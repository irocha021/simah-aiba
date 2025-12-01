# Fluxograma: retrieveMessages DCP

## Fluxo Principal

```mermaid
flowchart TB
    A["Início: retrieveMessages()"]
    B["Reprocessar Jobs Pendentes/Falhos<br/>reprocessStuckJobs()"]
    C["Buscar Estações Ativas<br/>dcpStationService.getActiveStations()"]
    D{"Existem<br/>estações?"}
    E["Retornar JSON Error 404<br/>Nenhuma estação ativa"]
    F["Calcular Intervalo<br/>Última hora completa<br/>lrgsService.getLastFullHourInterval()"]
    G["Inicializar Estatísticas<br/>totalMessages = 0<br/>totalInserted = 0<br/>totalCorrupted = 0"]
    H{"Loop<br/>Estações"}
    I["Criar Sync Log<br/>dcpSyncLogService.createLog()<br/>status: pending, attempts: 0"]
    J["Iniciar Sync Log<br/>dcpSyncLogService.startLog()<br/>status: running, attempts++"]
    K["Gerar Arquivo de Busca<br/>lrgsService.generateSearchCriteria()<br/>MessageBrowser.sc"]
    L["Executar Cliente LRGS<br/>lrgsService.fetchMessages()<br/>getDcpMessages binary"]
    M["Ler Arquivo de Saída<br/>file_get_contents()"]
    N["Dividir Mensagens<br/>explode() por delimitador"]
    O["Filtrar Mensagens Irrelevantes<br/>Normal termination<br/>Missing message<br/>Wrong channel<br/>TESTE DE TRANSMISSAO"]
    P["Processar e Inserir Mensagens<br/>dcpReadingService.processAndInsertMessages()<br/>Parse + Validação + Bulk Insert"]
    Q["Completar Sync Log<br/>dcpSyncLogService.completeLog()<br/>status: completed"]
    R["Acumular Estatísticas<br/>totalMessages++<br/>totalInserted++<br/>totalCorrupted++"]
    S["Registrar Conclusão<br/>Log geral de estatísticas"]
    T["Retornar JSON Success<br/>interval, total_stations<br/>total_messages, stations array"]
    Z["Fim"]
    U["Capturar Erro da Estação<br/>Log error com stack trace"]
    V["Marcar Sync Log Failed<br/>dcpSyncLogService.failLog()<br/>status: failed, error_message"]
    W["Capturar Erro Principal<br/>Log error fatal"]
    X["Retornar JSON Error 500<br/>success: false"]

    A --> B
    B --> C
    C --> D
    D -->|Não| E
    D -->|Sim| F
    F --> G
    G --> H
    H -->|Para cada| I
    I --> J
    J --> K
    K --> L
    L --> M
    M --> N
    N --> O
    O --> P
    P --> Q
    Q --> R
    R --> H
    H -->|Fim loop| S
    S --> T
    T --> Z
    E --> Z

    I -.->|erro| U
    J -.->|erro| U
    K -.->|erro| U
    L -.->|erro| U
    P -.->|erro| U
    U --> V
    V --> H

    C -.->|erro fatal| W
    F -.->|erro fatal| W
    W --> X
    X --> Z

    style A fill:#4CAF50,stroke:#2E7D32,stroke-width:3px,color:#fff
    style Z fill:#4CAF50,stroke:#2E7D32,stroke-width:3px,color:#fff
    style E fill:#f44336,stroke:#c62828,stroke-width:2px,color:#fff
    style X fill:#f44336,stroke:#c62828,stroke-width:2px,color:#fff
    style V fill:#f44336,stroke:#c62828,stroke-width:2px,color:#fff
    style U fill:#FF9800,stroke:#E65100,stroke-width:2px,color:#fff
    style W fill:#FF9800,stroke:#E65100,stroke-width:2px,color:#fff
    style T fill:#2196F3,stroke:#1565C0,stroke-width:2px,color:#fff
    style D fill:#9C27B0,stroke:#6A1B9A,stroke-width:2px,color:#fff
    style H fill:#9C27B0,stroke:#6A1B9A,stroke-width:2px,color:#fff
    style P fill:#00BCD4,stroke:#006064,stroke-width:2px,color:#fff
    style L fill:#FF5722,stroke:#BF360C,stroke-width:2px,color:#fff
```

## Detalhamento do Processo

### 1. Reprocessar Jobs
- Busca logs com status: `pending`, `failed` ou `running` > 10min
- Limite de 3 tentativas (attempts < 3)
- Deleta leituras antigas antes de reprocessar

### 2. Loop de Estações
- Para cada estação ativa no banco
- Cria um sync log (status: pending)
- Inicia processamento (status: running)

### 3. Busca LRGS
- Gera arquivo MessageBrowser.sc com critérios
- Executa cliente LRGS (getDcpMessages)
- Salva resultado em arquivo temporário

### 4. Processamento
- Divide mensagens por delimitador
- Filtra mensagens irrelevantes:
  - Normal termination
  - Until time reached
  - Missing message
  - Wrong channel
  - TESTE DE TRANSMISSAO

### 5. Inserção
- Parse do header GOES DCS (37 bytes)
- Validação de corrupção
- Mapeamento para campos do banco
- Bulk insert (todas mensagens válidas de uma vez)

### 6. Finalização
- Atualiza sync log (status: completed)
- Acumula estatísticas
- Retorna JSON com resultados
