# SIMAH — Sistema de Monitoramento Hidrológico

Consolida dados hidrológicos da Bahia vindos de cinco fontes independentes, mantém a série histórica em base própria e publica o resultado em três frentes: um mapa com camadas geoespaciais geradas pelo próprio sistema, um painel administrativo e uma API pública versionada.

Laravel 12 · PHP 8.3 · MariaDB 10.11 · GDAL · Python 3 · JRE

## Fontes de dados

| Fonte | O que entra | Como chega |
|---|---|---|
| **LRGS / DCP** | mensagens das plataformas de coleta de dados por satélite | cliente Java (OpenDCS) conecta no LDDS da NOAA/USGS — de hora em hora |
| **ANA / HidroWeb** | inventário de estações, séries telemétricas e séries de qualidade | API da ANA — diário |
| **RIMAS e SIAGAS** | poços de monitoramento e respectivas leituras | upload de `.dbf` (dBase) pelo painel |
| **CNARH** | cadastro de usuários de recursos hídricos | upload de planilha |
| **Poços SIMAH** | estações e leituras próprias | cadastro e importação pelo painel |

Sobre a série da ANA, o sistema ainda calcula **previsão de vazão** mensalmente, via script Python (`resources/scripts/previsao_otimizada.py`, numpy + pandas) sobre as estações com curva de descarga cadastrada.

## Arquitetura

Três contêineres derivados da **mesma imagem**, mais o banco:

| Serviço | Papel |
|---|---|
| `app` | Apache + PHP, atende HTTP e expõe o healthcheck em `/up` |
| `queue` | `queue:work` — é onde as importações pesadas realmente rodam |
| `cron` | agendamento; dispara os endpoints de coleta |
| `db` | MariaDB 10.11, sem porta publicada (só a rede interna do compose) |

A imagem é **imutável e identificada pelo SHA do commit**: o código entra nela em build time, não por bind mount. Tudo que é escrito em runtime — `storage/app`, `storage/logs`, `public/tiles`, `public/geojson` — é volume nomeado. Nova pasta de escrita implica novo volume no compose, não ajuste de permissão no boot.

## Coleta automática

| Quando | Endpoint | O que faz |
|---|---|---|
| a cada hora (`:10`) | `/jobs/lrgs/readings/dcp-messages` | baixa e processa as mensagens DCP do período corrente |
| 01:30 | `/jobs/hidroweb/inventory-station` | sincroniza o inventário de estações da ANA |
| 02:00 | `/jobs/hidroweb/readings/hidro-serie-qa` | importa as séries de qualidade |
| 02:30 | `/jobs/hidroweb/info-ana-adopted-telemetric-series-reading` | importa as séries telemétricas adotadas |
| dia 1, 02:00 | `/jobs/hidroweb/flow-forecast` | recalcula a previsão de vazão |

O cron chama `http://app` — o nome do serviço na rede interna do compose, não o domínio público. Assim o agendamento não depende de DNS, TLS nem do proxy reverso: se o certificado vencer ou o proxy cair, a coleta continua. O reprocessamento manual de um período específico fica em `/jobs/lrgs/readings/dcp-messages/manual`.

Essas rotas são propositalmente **sem autenticação** — elas presumem que só a rede interna as alcança. Ao expor a aplicação, mantenha `/jobs/*` fora do que o proxy publica.

## API pública

Prefixo `/api/v1`, autenticação por chave no header `X-Api-Key` (ou `?api_key=`), limite de **300 requisições/minuto por chave**. A chave é solicitada pelo formulário em `/api-key` e enviada por e-mail.

| Recurso | Endpoints |
|---|---|
| LRGS | `/lrgs/stations`, `/lrgs/{station_code}/readings` |
| HidroWeb | `/hidroweb/stations`, `/hidroweb/{code}/telemetry`, `/quality`, `/forecast` |
| RIMAS | `/rimas/points`, `/rimas/{id_ponto}/readings` |
| SIAGAS | `/siagas/wells`, `/siagas/{ponto}` |
| CNARH | `/cnarh`, `/cnarh/{cd_cnarh40}` |
| Poços SIMAH | `/poco-simah/stations`, `/poco-simah/{station_code}/readings` |

A referência navegável é gerada pelo Scribe (`php artisan scribe:generate`) e servida em `/docs`.

## Camadas do mapa

O pipeline converte shapefile em tiles XYZ, em três etapas **sequenciais**: `ogr2ogr` (shapefile → GeoJSON) → `gdal_rasterize` (→ raster) → `gdal2tiles` (→ tiles, 4 processos).

```
GET /geobahia/import-async/{slug}                              # enfileira — use este
GET /geobahia/import/{layer}/{minZoom?}/{maxZoom?}/{opacity?}   # síncrono
GET /geobahia/import-all
```

Prefira a versão assíncrona: ela roda no worker e a falha vai para `failed_jobs`. A maior camada do acervo gera ~2,9 GB de arquivos intermediários e leva cerca de 10 minutos nos zooms 5–12; acompanhe com `docker logs mhb_queue`.

As 15 camadas e suas regras de colorização (campo classificador, paleta GDAL, bordas, tipo ponto/polígono) ficam em `config/shapefile-tiles.php`. As áreas de drenagem por estação têm rota própria em `/hw-station-drainages/import-all`.

Importações interrompidas deixam diretórios órfãos em `storage/app/temp`; o `temp:prune` roda no início de cada importação e remove os que passam de 24 h.

## Deploy

A VM **busca** a imagem no registro — nada conecta de fora para dentro, o que dispensa expor SSH.

```bash
# 1. push na main → o workflow builda e publica em ghcr.io/<owner>/<repo>
# 2. na VM:
./deploy.sh sha-a1b2c3d    # tag específica (preferível: fixa o commit)
./deploy.sh                # tag do .env, ou latest
./deploy.sh --rollback     # volta para a tag anterior registrada
```

O script verifica espaço em disco (mínimo 5 GB), registra a tag corrente para rollback, sobe os serviços, espera o healthcheck do `app` e só então aplica migrações e regenera caches. As migrações rodam **uma vez por deploy**, aqui — não no entrypoint, já que os três contêineres compartilham a imagem e disputariam o banco ao subir juntos.

A VM precisa de acesso ao registro: `docker login ghcr.io` com um PAT de escopo `read:packages`, a menos que o pacote seja público.

## Configuração

> **Atenção:** o `.env.example` é o esqueleto do Laravel — traz `DB_CONNECTION=sqlite` e as demais `DB_*` comentadas. Ele **não basta** para subir o ambiente containerizado.

Além do que já vem no exemplo, defina:

| Variável | Observação |
|---|---|
| `DB_CONNECTION=mysql`, `DB_HOST=db`, `DB_DATABASE`, `DB_USERNAME` | `db` é o nome do serviço no compose |
| `DB_PASSWORD`, `DB_ROOT_PASSWORD` | **obrigatórias** — o compose aborta se faltarem |
| `IMAGE_TAG`, `IMAGE_REGISTRY` | tag e registro da imagem; sem elas valem os defaults do `deploy.sh` |
| `HTTP_BIND`, `HTTP_PORT` | padrão `127.0.0.1:8119` — publique só pelo proxy reverso |
| `HIDROWEB_IDENTIFIER`, `HIDROWEB_PASSWORD`, `HIDROWEB_BASE_URL` | credenciais da API da ANA |
| `LRGS_HOST`, `LRGS_USERNAME`, `LRGS_PASSWORD`, `LRGS_*_PATH` | servidor LDDS e credenciais |
| `MAIL_*` | envio de chaves de API e recuperação de senha |

O acesso ao LDDS usa a **porta 16003** de saída. Se estiver bloqueada, a coleta DCP falha **em silêncio** — vale testar antes com `nc -zv <host> 16003` e monitorar a data da última leitura.

## Ambiente local

```bash
cp .env.example .env        # ajuste DB_CONNECTION=mysql e as DB_* antes de subir
docker compose -f docker-compose.local.yml up -d --build
docker exec mhb php artisan key:generate
docker exec mhb php artisan migrate
```

Aplicação em `http://localhost:8082`, Vite em `5173`, MariaDB exposto em `33006`.

## Comandos operacionais

```bash
php artisan tiles:generate      # gera tiles a partir de um shapefile
php artisan geojson:generate    # converte shapefile (.zip) em GeoJSON
php artisan lrgs:fetch          # consulta mensagens DCP direto no LDDS
php artisan temp:prune          # remove intermediários órfãos de importações
php artisan queue:failed        # jobs que falharam
php artisan test                # suíte de testes
```

## Estrutura

```
app/Services/          coleta e importação, um serviço por fonte
app/Jobs/              importação de camadas, drenagens e sincronização DCP
config/                shapefile-tiles.php (camadas), lrgs.php, dcp.php
lrgs/                  cliente Java do OpenDCS e seus JARs
resources/scripts/     previsão de vazão (Python)
routes/                web.php · public-api.php (API v1) · api.php
scripts/migracao/      exportação e importação do acervo entre ambientes
docker/                configuração de PHP, Apache, cron e entrypoint
```
