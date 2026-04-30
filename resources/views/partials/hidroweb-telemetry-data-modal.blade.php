<!-- Modal para exibir dados HidroWeb Telemetria (Leituras + Previsões) -->
<div id="hidrowebTelemetryDataModal" class="hidroweb-data-modal">
    <div class="hidroweb-data-modal-content">
        <!-- Cabeçalho -->
        <div class="hidroweb-data-modal-header">
            <div class="hidroweb-data-header-content">
                <img src="{{ asset('images/logo-top-sigmah.svg') }}" alt="Logo SIGMAH" class="logo-hidroweb-data-modal" />
                <div class="hidroweb-data-header-content-title">
                    <h2>Dados HidroWeb - Telemetria</h2>
                    <p>Dados adotados de cota, vazão e precipitação transmitidos automaticamente pela Rede HidroWeb da
                        Agência Nacional de Águas (ANA).</p>
                </div>
            </div>
            <span id="closeHidrowebDataModal" class="hidroweb-data-modal-close">&times;</span>
        </div>

        <!-- Informações da Estação -->
        <div class="hidroweb-data-modal-info">
            <h2 style="margin: 0; color: #000000; font-weight: bold; font-size: 1.5rem;">
                Telemetria de DCPs Hidrológicos
            </h2>
            <p style="margin: 5px 0 0 0; color: #575F6E; font-size: 1rem;">
                <span id="hidrowebDataModalStationName"></span> - <span id="hidrowebDataModalStationCode"></span>
            </p>
        </div>

        <!-- Tabs -->
        <div class="hidroweb-data-tabs-wrapper">
            <div class="hidroweb-data-tabs">
                <button class="hidroweb-data-tab-btn active" data-tab="leituras">Leituras</button>
                <button class="hidroweb-data-tab-btn" data-tab="previsoes">Previsões</button>
            </div>
        </div>

        <!-- Tab: Leituras -->
        <div id="tab-leituras" class="hidroweb-data-tab-content active">
            <!-- Loading -->
            <div id="leiturasLoadingSpinner" class="hidroweb-data-loading">
                <div class="hidroweb-data-spinner"></div>
                <p>Carregando leituras...</p>
            </div>

            <!-- Tabela Leituras -->
            <div id="leiturasTableContainer" class="hidroweb-data-table-container" style="display: none;">
                <p style="font-size:12px; margin-bottom: 10px;">Total de Leituras: <strong id="leiturasTotal">-</strong>
                </p>
                <table class="hidroweb-data-table">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Chuva Adotada (mm)</th>
                            <th>Cota Adotada (m)</th>
                            <th>Vazão Adotada (m³/s)</th>
                        </tr>
                    </thead>
                    <tbody id="leiturasTableBody"></tbody>
                </table>
            </div>

            <!-- Erro Leituras -->
            <div id="leiturasErrorMessage" class="hidroweb-data-error" style="display: none;">
                <strong>Erro:</strong> <span id="leiturasErrorText"></span>
            </div>
        </div>

        <!-- Tab: Previsões -->
        <div id="tab-previsoes" class="hidroweb-data-tab-content">
            <!-- Loading -->
            <div id="previsoesLoadingSpinner" class="hidroweb-data-loading">
                <div class="hidroweb-data-spinner"></div>
                <p>Carregando previsões...</p>
            </div>

            <!-- Tabela Previsões -->
            <div id="previsoesTableContainer" class="hidroweb-data-table-container" style="display: none;">
                <p style="font-size:12px; margin-bottom: 10px;">Total de Previsões: <strong id="previsoesTotal">
                    </strong></p>
                <table class="hidroweb-data-table">
                    <thead>
                        <tr>
                            <th>Ano</th>
                            <th>Mês</th>
                            <th>Vazão Prevista (m³/s)</th>
                            <th>Alfa Pond</th>
                            <th>Q90</th>
                            <th>Vsup</th>
                        </tr>
                    </thead>
                    <tbody id="previsoesTableBody"></tbody>
                </table>
            </div>

            <!-- Erro Previsões -->
            <div id="previsoesErrorMessage" class="hidroweb-data-error" style="display: none;">
                <strong>Erro:</strong> <span id="previsoesErrorText"></span>
            </div>

            <!-- Sem previsões -->
            <div id="previsoesEmpty" class="hidroweb-data-empty" style="display: none;">
                <p>⚠️ Esta estação não possui previsões de vazão.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .hidroweb-data-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .hidroweb-data-modal-content {
        background-color: #ffffff;
        margin: 2% auto;
        border: 1px solid #888;
        width: 85%;
        height: 90%;
        overflow-y: auto;
    }

    .hidroweb-data-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        background-color: #E3EBFF;
        padding: 30px;
    }

    .logo-hidroweb-data-modal {
        height: 4.5rem;
    }

    .hidroweb-data-header-content {
        display: flex;
        align-items: center;
        gap: 2.25rem;
    }

    .hidroweb-data-header-content-title {
        display: flex;
        align-items: flex-start;
        flex-direction: column;
        gap: 5px;
        width: 60%;
    }

    .hidroweb-data-header-content-title h2 {
        color: #000000;
        font-weight: bold;
        font-size: 1.5rem;
        margin: 0;
    }

    .hidroweb-data-header-content-title p {
        color: #575F6E;
        font-size: 1rem;
        margin: 0;
    }

    .hidroweb-data-modal-close {
        cursor: pointer;
        font-size: 60px;
        font-weight: 300;
        color: #5C5E64;
    }

    .hidroweb-data-modal-close:hover {
        color: #000000;
    }

    .hidroweb-data-modal-info {
        margin: 20px 50px;
        padding: 10px;
    }

    /* Tabs Wrapper */
    .hidroweb-data-tabs-wrapper {
        position: relative;
        margin: 0 50px 20px 50px;
        padding-left: 0;
    }

    /* Linha cinza abaixo de toda a área */
    .hidroweb-data-tabs-wrapper::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background-color: #D4D4D4;
    }

    /* Tabs */
    .hidroweb-data-tabs {
        display: flex;
        gap: 5px;
        position: relative;
        z-index: 2;
    }

    .hidroweb-data-tab-btn {
        padding: 12px 24px;
        background-color: transparent;
        border: none;
        cursor: pointer;
        font-size: 1.1rem;
        font-weight: 500;
        position: relative;
        color: #79808F;
        margin-bottom: 0px;
    }

    .hidroweb-data-tab-btn.active {
        background-color: transparent;
        color: #242731;
    }

    .hidroweb-data-tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: 0px;
        left: 0;
        right: 0;
        height: 3px;
        background-color: #4277FF;
        z-index: 3;
    }

    .hidroweb-data-tab-content {
        display: none;
        margin: 0 50px;
    }

    .hidroweb-data-tab-content.active {
        display: block;
    }

    .hidroweb-data-loading {
        text-align: center;
        padding: 40px 20px;
    }

    .hidroweb-data-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3388ff;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px auto;
    }

    .hidroweb-data-table-container {
        max-height: 23.5rem;
        overflow-y: auto;
        margin: auto;
        position: relative;
    }

    .hidroweb-data-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }

    /* CABEÇALHO */
    .hidroweb-data-table thead th {
        position: sticky;
        top: 0;
        background-color: #ffffff;
        color: black;
        text-align: center;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        padding: 10px 8px;
        border: none;
        white-space: nowrap;
        box-shadow: inset 0 -2px 0 #3388ff;
        z-index: 1;
    }

    .hidroweb-data-table thead {
        position: sticky;
        top: 0;
        z-index: 1;
    }

    /* CORPO DA TABELA */
    .hidroweb-data-table td {
        padding: 10px 8px;
        background: white;
        border: none;
        white-space: nowrap;
        text-align: center;
        font-size: 0.8rem;
        border-bottom: 1px solid #3388ff;
    }

    /* Remove a borda inferior da última linha */
    .hidroweb-data-table tbody tr:last-child td {
        border-bottom: none;
    }

    .hidroweb-data-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .hidroweb-data-table tbody tr:hover {
        background-color: #fff4e6;
    }

    .hidroweb-data-error {
        color: #d9534f;
        padding: 15px;
        background-color: #f2dede;
        border: 1px solid #ebccd1;
        border-radius: 4px;
        margin-top: 10px;
    }

    .hidroweb-data-empty {
        padding: 20px;
        text-align: center;
        background-color: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 4px;
        color: #856404;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>
