#!/bin/bash
set -euo pipefail

# Entrypoint compartilhado pelos três contêineres (web, fila e agendador), que
# usam a mesma imagem.
#
# Um volume nomeado vazio herda dono e permissão do diretório correspondente da
# imagem, mas isso NÃO vale para bind mounts nem para volumes já existentes de
# uma versão anterior. Sem o ajuste abaixo, um volume criado antes de um deploy
# pode manter dono root e a aplicação (que roda como www-data) falha ao escrever
# — o mesmo sintoma que o modelo antigo, de bind mount, resolvia com chown a
# cada boot.

DIRETORIOS_DE_ESCRITA=(
    storage
    bootstrap/cache
    public/tiles
    public/geojson
    lrgs
)

if [ "$(id -u)" = "0" ]; then
    for dir in "${DIRETORIOS_DE_ESCRITA[@]}"; do
        [ -d "/var/www/html/$dir" ] || continue
        # -h evita seguir o symlink public/storage criado por storage:link
        if [ "$(stat -c '%U' "/var/www/html/$dir")" != "www-data" ]; then
            chown -Rh www-data:www-data "/var/www/html/$dir"
        fi
    done
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
fi

# As migrações NÃO rodam aqui de propósito: os três contêineres compartilham
# este entrypoint e disputariam o banco ao subir juntos. Quem migra é o
# deploy.sh, uma vez por deploy.

exec "$@"
