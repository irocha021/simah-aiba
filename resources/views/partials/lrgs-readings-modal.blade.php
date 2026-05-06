@auth
    <meta name="user-logged-in" content="true">
@else
    <meta name="user-logged-in" content="false">
@endauth

<!-- Modal para exibir leituras LRGS Client (DCP) -->
<div id="lrgsReadingsModal" class="lrgs-modal">
    <div class="lrgs-modal-content">
        <!-- Cabeçalho -->
        <div class="lrgs-modal-header">
            <div class="lrgs-header-content">
                <img src="{{ asset('images/logo-top-sigmah.svg') }}" alt="Logo SIGMAH" class="logo-lrgs-modal" />
                <div class="lrgs-header-content-title">
                    <h2>Leituras Estação AIBA</h2>
                    <p>Dados de telemetria e parâmetros de recepção dos DCPs
                        Inclui nível d’água, chuva, temperatura, sinal, bateria e localização.</p>
                </div>
            </div>
            <span id="closeLrgsModal" class="lrgs-modal-close">&times;</span>
        </div>

        <!-- Informações da Estação -->
        <div class="lrgs-modal-info">
            <span id="lrgsModalStationCode"></span>
            <div style="margin: 10px">
                <span style="font-size:12px; color: #575F6E;">Total de Leituras:</span>
                <span style="font-size:12px; color: #575F6E;" id="lrgsModalTotalReadings"></span>
            </div>
            <div style="margin: 10px">
                <span style="font-size:12px; color: #575F6E;" id="lrgsStationLocation"></span>
            </div>
        </div>

        <!-- Loading -->
        <div id="lrgsLoadingSpinner" class="lrgs-loading">
            <div class="lrgs-spinner"></div>
            <p>Carregando leituras...</p>
        </div>

        <!-- Tabela -->
        <div id="lrgsTableContainer" class="lrgs-table-container" style="display: none;">
            <table class="lrgs-table">
                <thead id="lrgsTableHeader">
                    <!-- Cabeçalhos serão adicionados via JavaScript -->
                </thead>
                <tbody id="lrgsTableBody">
                    <!-- Linhas serão adicionadas via JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Erro -->
        <div id="lrgsErrorMessage" class="lrgs-error" style="display: none;">
            <strong>Erro:</strong> <span id="lrgsErrorText"></span>
        </div>
    </div>
</div>

<!-- Sub-modal Dados Completos (apenas admin) -->
<div id="lrgsFullDataModal" class="lrgs-full-modal">
    <div class="lrgs-full-modal-content">
        <div class="lrgs-full-modal-header">
            <h3>Dados Completos da Leitura</h3>
            <span id="closeLrgsFullModal" class="lrgs-modal-close" style="font-size: 40px;">&times;</span>
        </div>
        <div id="lrgsFullDataBody" class="lrgs-full-data-body">
            <!-- preenchido via JavaScript -->
        </div>
    </div>
</div>

<style>
    .lrgs-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .lrgs-modal-content {
        background-color: #ffffff;
        margin: 2% auto;
        padding-bottom: 30px;
        border: 1px solid #888;
        width: 85%;
        height: 90%;
        overflow-y: auto;
    }

    .lrgs-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        background-color: #E3EBFF;
        padding: 30px;
    }

    .logo-lrgs-modal {
        height: 4.5rem;
    }

    .lrgs-header-content {
        display: flex;
        align-items: center;
        gap: 2.25rem;
    }

    .lrgs-header-content-title {
        display: flex;
        align-items: flex-start;
        flex-direction: column;
        gap: 5px;
        width: 60%;
    }

    .lrgs-header-content-title h2 {
        color: #000000;
        font-weight: bold;
        font-size: 1.5rem;
        margin: 0;
    }

    .lrgs-header-content-title p {
        color: #575F6E;
        font-size: 1rem;
        margin: 0;
    }

    .lrgs-modal-close {
        cursor: pointer;
        font-size: 60px;
        font-weight: 300;
        color: #5C5E64;
    }

    .lrgs-modal-close:hover {
        color: #000000;
    }

    .lrgs-modal-info {
        margin: 20px 50px;
        padding: 10px;
    }

    .lrgs-loading {
        text-align: center;
        padding: 20px;
    }

    .lrgs-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3388ff;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    .lrgs-table-container {
        max-height: 35%;
        width: 92%;
        overflow-x: auto;
        overflow-y: auto;
        margin: auto;
        position: relative;
    }

    .lrgs-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }

    /* CABEÇALHO */
    .lrgs-table thead th {
        position: sticky;
        top: 0;
        background-color: #ffffff;
        color: black;
        text-align: center;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 10px 20px;
        border: none;
        white-space: nowrap;
        box-shadow: inset 0 -2px 0 #3388ff;
        height: 2rem;
    }

    .lrgs-table td {
        padding: 10px 8px;
        background: white;
        border: none;
        white-space: nowrap;
        text-align: center;
        font-size: 0.8rem;
        border-bottom: 1px solid #3388ff;
    }

    /* Remove a borda inferior da última linha */
    .lrgs-table tbody tr:last-child td {
        border-bottom: none;
    }

    .lrgs-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .lrgs-table tbody tr:hover {
        background-color: #fff4e6;
    }

    .lrgs-error {
        color: #d9534f;
        padding: 15px;
        background-color: #f2dede;
        border: 1px solid #ebccd1;
        border-radius: 4px;
        margin: 50px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

        /* Botão Ver completo (apenas admin) */
    .lrgs-full-data-btn {
        padding: 4px 10px;
        background: #242731;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.75rem;
    }

    .lrgs-full-data-btn:hover {
        background: #3a3f4e;
    }

    /* Sub-modal dados completos */
    .lrgs-full-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
    }

    .lrgs-full-modal-content {
        background: #fff;
        margin: 5% auto;
        width: 580px;
        max-height: 80vh;
        overflow-y: auto;
        border-radius: 8px;
        padding: 28px;
        position: relative;
    }

    .lrgs-full-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #3388ff;
    }

    .lrgs-full-modal-header h3 {
        margin: 0;
        font-size: 1.1rem;
        color: #242731;
    }

    .lrgs-full-data-body dl {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 20px;
        margin: 0;
    }

    .lrgs-full-data-body dt {
        font-weight: bold;
        font-size: 0.75rem;
        color: #575F6E;
        text-transform: uppercase;
        padding: 4px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .lrgs-full-data-body dd {
        font-size: 0.85rem;
        margin: 0;
        color: #242731;
        padding: 4px 0;
        border-bottom: 1px solid #f0f0f0;
        word-break: break-all;
    }

</style>
