@auth
    <meta name="user-logged-in" content="true">
@else
    <meta name="user-logged-in" content="false">
@endauth

<!-- Modal para exibir leituras do poço RIMAS -->
<div id="rimasReadingsModal" class="rimas-modal">
    <div class="rimas-modal-content">
        <!-- Cabeçalho -->
        <div class="rimas-modal-header">
            <div class="rimas-header-content">
                <img src="{{ asset('images/logo-top-sigmah.svg') }}" alt="Logo SIGMAH" class="logo-rimas-modal" />
                <div class="rimas-header-content-title">
                    <h2>Leituras do Poço RIMAS</h2>
                    <p>Monitoramento de águas subterrâneas da Rede RIMAS, disponibilizado pelo Serviço Geológico do
                        Brasil (SGB/CPRM).</p>
                </div>
            </div>
            <span id="closeRimasModal" class="rimas-modal-close">&times;</span>
        </div>

        <!-- Informações do Poço -->
        <div class="rimas-modal-info">
            <span id="rimasModalIdPonto"></span>
            <div style="margin: 10px">
                <span style="font-size:12px; color: #575F6E;">Total de Leituras:</span>
                <span style="font-size:12px; color: #575F6E;" id="rimasModalTotalReadings">-</span>
            </div>
        </div>

        <!-- Loading -->
        <div id="rimasLoadingSpinner" class="rimas-loading">
            <div class="rimas-spinner"></div>
            <p>Carregando leituras...</p>
        </div>

        <!-- Tabela -->
        <div id="rimasReadingsTableContainer" class="rimas-table-container" style="display: none;">
            <table class="rimas-table">
                <thead>
                    <tr>
                        <th>NÚMERO DO PONTO</th>
                        <th>DATA</th>
                        <th>HORA</th>
                        <th>NÍVEL DA ÁGUA (m)</th>
                        <th>OBSERVAÇÃO</th>
                    </tr>
                </thead>
                <tbody id="rimasReadingsTableBody"></tbody>
            </table>
        </div>

        <!-- Erro -->
        <div id="rimasErrorMessage" class="rimas-error" style="display: none;">
            <strong>Erro:</strong> <span id="rimasErrorText"></span>
        </div>
    </div>
</div>

<style>
    .rimas-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .rimas-modal-content {
        background-color: #ffffff;
        margin: 2% auto;
        border: 1px solid #888;
        width: 85%;
        height: 90%;
        overflow-y: auto;
    }

    .rimas-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        background-color: #E3EBFF;
        padding: 30px;
    }

    .logo-rimas-modal {
        height: 4.5rem;
    }

    .rimas-header-content {
        display: flex;
        align-items: center;
        gap: 2.25rem;
    }

    .rimas-header-content-title {
        display: flex;
        align-items: flex-start;
        flex-direction: column;
        gap: 5px;
        width: 60%;
    }

    .rimas-header-content-title h2 {
        color: #000000;
        font-weight: bold;
        font-size: 1.5rem;
        margin: 0;
    }

    .rimas-header-content-title p {
        color: #575F6E;
        font-size: 1rem;
        margin: 0;
    }

    .rimas-modal-close {
        cursor: pointer;
        font-size: 60px;
        font-weight: 300;
        color: #5C5E64;
    }

    .rimas-modal-close:hover {
        color: #000000;
    }

    .rimas-modal-info {
        margin: 20px 50px;
        padding: 10px;
    }

    .rimas-loading {
        text-align: center;
        padding: 20px;
    }

    .rimas-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #ff7800;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    .rimas-table-container {
        max-height: 25rem;
        width: 92%;
        overflow-x: auto;
        overflow-y: auto;
        margin: auto;
        position: relative;
    }

    .rimas-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }

    .rimas-table thead th {
        position: sticky;
        top: 0;
        background-color: #ffffff;
        color: black;
        text-align: center;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 10px 8px;
        border: none;
        white-space: nowrap;
        box-shadow: inset 0 -2px 0 #3388ff;
    }

    .rimas-table th {
        padding: 10px 8px;
        border: 2px solid #3388ff;
        border-top: none;
        border-right: none;
        border-left: none;
        white-space: nowrap;
    }

    .rimas-table td {
        padding: 10px 8px;
        background: white;
        border: none;
        white-space: nowrap;
        text-align: center;
        font-size: 0.8rem;
        border-bottom: 1px solid #3388ff;
    }

    .lrgs-table tbody tr:last-child td {
        border-bottom: none;
    }

    .rimas-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .rimas-table tbody tr:hover {
        background-color: #fff4e6;
    }

    .rimas-error {
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
</style>
