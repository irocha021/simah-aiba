#!/bin/bash
#
# Exporta os dados do SIMAH do ambiente atual (studio1) para migração.
#
# Roda NO SERVIDOR DE ORIGEM, como root:
#   ./exportar.sh              # ensaio: exporta sem parar nada
#   ./exportar.sh --cutover    # cutover: para os contêineres antes de exportar
#
# O ensaio serve para medir tempo e validar o pacote sem downtime; ele pode
# gerar um pacote levemente inconsistente se alguém escrever durante a cópia.
# O cutover para os contêineres primeiro e os DEIXA parados — é o export
# definitivo, feito na janela combinada.
#
set -euo pipefail

ORIGEM="/root/system/mhb"
DESTINO_BASE="/root/migracao-simah"
MARGEM_DISCO_GB=6

log()  { printf '\n\033[1;36m==> %s\033[0m\n' "$*"; }
aviso(){ printf '\033[1;33m    %s\033[0m\n' "$*"; }
erro() { printf '\n\033[1;31mERRO: %s\033[0m\n' "$*" >&2; exit 1; }

[ "$(id -u)" = "0" ] || erro "rode como root (os dados estão sob /root)."
[ -d "$ORIGEM" ] || erro "origem não encontrada: $ORIGEM"

CUTOVER=false
[ "${1:-}" = "--cutover" ] && CUTOVER=true

# --- Espaço ----------------------------------------------------------------
livre_gb=$(df -BG --output=avail /root | tail -1 | tr -dc '0-9')
[ "$livre_gb" -lt "$MARGEM_DISCO_GB" ] && erro "espaço insuficiente: ${livre_gb}GB livres, mínimo ${MARGEM_DISCO_GB}GB."
log "Espaço em disco: ${livre_gb}GB livres"

# --- Compressor ------------------------------------------------------------
# pigz comprime em paralelo e gera .gz padrão, então o destino descomprime com
# qualquer gzip. Importa: são 465 mil arquivos pequenos, e o gzip serial leva
# vários minutos só nos tiles.
if command -v pigz > /dev/null; then
    COMPRESSOR="pigz -6"
    log "Compressor: pigz (paralelo, $(nproc) núcleos)"
else
    COMPRESSOR="gzip -6"
    aviso "pigz não encontrado — usando gzip serial (mais lento)"
fi

CARIMBO=$(date +%Y%m%d-%H%M%S)
DESTINO="${DESTINO_BASE}/mhb-migracao-${CARIMBO}"
mkdir -p "$DESTINO"

# --- Janela de consistência ------------------------------------------------
if [ "$CUTOVER" = true ]; then
    log "CUTOVER — parando os contêineres (ficarão parados ao final)"
    docker stop mhb_cron mhb_queue mhb 2>/dev/null || true
else
    aviso "ENSAIO — contêineres seguem no ar."
    aviso "O pacote pode ficar inconsistente se houver escrita durante a cópia."
    aviso "Para o export definitivo use: $0 --cutover"
fi

# --- Credenciais do banco --------------------------------------------------
# Lidas do .env do próprio ambiente; nada é ecoado no terminal.
env_get() { grep -E "^${1}=" "${ORIGEM}/.env" 2>/dev/null | head -1 | cut -d= -f2- | tr -d '"' | tr -d "'"; }

DB_HOST_ENV=$(env_get DB_HOST); DB_PORT_ENV=$(env_get DB_PORT)
DB_NAME=$(env_get DB_DATABASE);  DB_USER=$(env_get DB_USERNAME)
DB_PASS=$(env_get DB_PASSWORD)
[ -n "$DB_NAME" ] && [ -n "$DB_USER" ] || erro "não consegui ler as credenciais em ${ORIGEM}/.env"

# host.docker.internal só resolve dentro de contêiner; do host é 127.0.0.1.
DB_HOST_REAL="127.0.0.1"
[ "$DB_HOST_ENV" != "host.docker.internal" ] && DB_HOST_REAL="$DB_HOST_ENV"

# --- Banco -----------------------------------------------------------------
log "Exportando o banco (${DB_NAME})"
# --single-transaction: dump consistente em InnoDB sem travar as tabelas — o
# MariaDB de origem atende outros 4 sistemas e não pode ser bloqueado.
docker run --rm --network host \
    -e MYSQL_PWD="$DB_PASS" \
    mariadb:10.11 \
    mysqldump \
        --host="$DB_HOST_REAL" --port="${DB_PORT_ENV:-3306}" --user="$DB_USER" \
        --single-transaction --routines --triggers --events \
        --default-character-set=utf8mb4 \
        --no-tablespaces \
        "$DB_NAME" \
    | $COMPRESSOR > "${DESTINO}/banco.sql.gz"

tabelas=$(zcat "${DESTINO}/banco.sql.gz" | grep -c "^CREATE TABLE" || echo 0)
log "Banco exportado: $(du -h "${DESTINO}/banco.sql.gz" | cut -f1), ${tabelas} tabelas"

# --- Arquivos --------------------------------------------------------------
# tar único por conjunto: são centenas de milhares de arquivos de ~4 KB, e
# copiá-los individualmente (rsync/scp) levaria horas contra minutos assim.
empacotar() {
    local nome="$1" base="$2" alvo="$3"
    [ -d "${base}/${alvo}" ] || { aviso "pulando ${nome}: ${base}/${alvo} não existe"; return; }
    log "Empacotando ${nome}"
    tar -I "$COMPRESSOR" -cf "${DESTINO}/${nome}.tar.gz" -C "$base" "$alvo"
    printf '    %s -> %s\n' "$nome" "$(du -h "${DESTINO}/${nome}.tar.gz" | cut -f1)"
}

empacotar tiles       "${ORIGEM}/public"  "tiles"
empacotar geojson     "${ORIGEM}/public"  "geojson"

# storage/app sem o temp: é área de trabalho descartável (era o vazamento de
# 7,9 GB) e sem os scripts, que agora são código versionado em resources/.
log "Empacotando storage_app (sem temp/ e scripts/)"
tar -I "$COMPRESSOR" -cf "${DESTINO}/storage_app.tar.gz" \
    -C "${ORIGEM}/storage" \
    --exclude='app/temp/*' --exclude='app/scripts' --exclude='app/dbf_temp/*' \
    app

# --- Manifesto e checksums -------------------------------------------------
log "Gerando manifesto"
tiles_qtd=$(find "${ORIGEM}/public/tiles" -type f 2>/dev/null | wc -l)
geojson_qtd=$(find "${ORIGEM}/public/geojson" -type f 2>/dev/null | wc -l)
# Os excludes precisam ser IDÊNTICOS aos do tar acima, senão a validação do
# importar.sh acusa divergência em dados que foram deixados de fora de
# propósito — scripts/ virou código versionado (resources/scripts) e não é
# mais dado de aplicação.
app_qtd=$(find "${ORIGEM}/storage/app" -type f \
    -not -path '*/temp/*' \
    -not -path '*/scripts/*' \
    -not -path '*/dbf_temp/*' 2>/dev/null | wc -l)

cat > "${DESTINO}/MANIFESTO.txt" <<EOF
# Migração SIMAH — studio1 -> AIBA
# Gerado em: $(date -Is)
# Modo: $([ "$CUTOVER" = true ] && echo "CUTOVER (contêineres parados)" || echo "ENSAIO (contêineres no ar)")
# Origem: $(hostname)

BANCO_TABELAS=${tabelas}
TILES_ARQUIVOS=${tiles_qtd}
GEOJSON_ARQUIVOS=${geojson_qtd}
STORAGE_APP_ARQUIVOS=${app_qtd}
EOF

cat "${DESTINO}/MANIFESTO.txt"

log "Calculando checksums"
( cd "$DESTINO" && sha256sum ./*.tar.gz ./*.sql.gz > SHA256SUMS )

# --- Resultado -------------------------------------------------------------
total=$(du -sh "$DESTINO" | cut -f1)
log "Export concluído — ${total} em ${DESTINO}"
ls -lh "$DESTINO"

cat <<EOF

Próximos passos:

  1. Transferir o diretório para a VM da AIBA. Da sua máquina:

     scp -r root@ssh.studio1.tech:${DESTINO} .
     scp -r ./mhb-migracao-${CARIMBO} <usuario>@<vm-aiba>:~/

  2. Na VM da AIBA, com o ambiente já no ar:

     ./scripts/migracao/importar.sh ~/mhb-migracao-${CARIMBO}

EOF

if [ "$CUTOVER" = true ]; then
    aviso "Os contêineres do studio1 continuam PARADOS (cutover)."
    aviso "Para reverter enquanto valida a AIBA:"
    aviso "  cd ${ORIGEM} && docker compose -f docker-compose.production.yml -p mhb up -d"
fi
