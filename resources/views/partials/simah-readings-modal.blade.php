<!-- Modal para exibir leituras do poço SIMAH -->
<div id="simahReadingsModal" class="simah-modal">
    <div class="simah-modal-content">
        <div class="simah-modal-header">
            <div class="simah-header-content">
                <img src="{{ asset('images/logo-top-sigmah.svg') }}" alt="Logo SIGMAH" class="logo-simah-modal" />
                <div class="simah-header-content-title">
                    <h2>Leituras do Poço SIMAH</h2>
                    <p>Monitoramento de poços do Sistema de Monitoramento Ambiental dos Recursos Hídricos.</p>
                </div>
            </div>
            <span id="closeSimahModal" class="simah-modal-close">&times;</span>
        </div>

        <div class="simah-modal-info">
            <span id="simahModalStationName"></span>
            <div style="margin: 10px">
                <span style="font-size:12px; color: #575F6E;">Código:</span>
                <span style="font-size:12px; color: #575F6E;" id="simahModalStationCode">-</span>
                &nbsp;&nbsp;
                <span style="font-size:12px; color: #575F6E;">Total de Leituras:</span>
                <span style="font-size:12px; color: #575F6E;" id="simahModalTotalReadings">-</span>
            </div>
        </div>

        <div id="simahLoadingSpinner" class="simah-loading">
            <div class="simah-spinner"></div>
            <p>Carregando leituras...</p>
        </div>

        <div id="simahReadingsTableContainer" class="simah-table-container" style="display: none;">
            <table class="simah-table">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Data/Hora Local</th>
                        <th>Data/Hora UTC</th>
                        <th>Pd (bar)</th>
                        <th>P1 (bar)</th>
                        <th>P2 (bar)</th>
                        <th>Tob1 (°C)</th>
                        <th>Tob2 (°C)</th>
                    </tr>
                </thead>
                <tbody id="simahReadingsTableBody"></tbody>
            </table>
        </div>

        <div id="simahErrorMessage" class="simah-error" style="display: none;">
            <strong>Erro:</strong> <span id="simahErrorText"></span>
        </div>
    </div>
</div>

<style>
    .simah-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .simah-modal-content {
        background-color: #ffffff;
        margin: 2% auto;
        border: 1px solid #888;
        width: 85%;
        height: 90%;
        overflow-y: auto;
    }

    .simah-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        background-color: #E3EBFF;
        padding: 30px;
    }

    .logo-simah-modal {
        height: 4.5rem;
    }

    .simah-header-content {
        display: flex;
        align-items: center;
        gap: 2.25rem;
    }

    .simah-header-content-title {
        display: flex;
        align-items: flex-start;
        flex-direction: column;
        gap: 5px;
        width: 60%;
    }

    .simah-header-content-title h2 {
        color: #000000;
        font-weight: bold;
        font-size: 1.5rem;
        margin: 0;
    }

    .simah-header-content-title p {
        color: #575F6E;
        font-size: 1rem;
        margin: 0;
    }

    .simah-modal-close {
        cursor: pointer;
        font-size: 60px;
        font-weight: 300;
        color: #5C5E64;
    }

    .simah-modal-close:hover {
        color: #000000;
    }

    .simah-modal-info {
        margin: 20px 50px;
        padding: 10px;
    }

    .simah-loading {
        text-align: center;
        padding: 20px;
    }

    .simah-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #165B9C;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: simah-spin 1s linear infinite;
        margin: 0 auto;
    }

    .simah-table-container {
        max-height: 25rem;
        width: 92%;
        overflow-x: auto;
        overflow-y: auto;
        margin: auto;
        position: relative;
    }

    .simah-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }

    .simah-table thead th {
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
        box-shadow: inset 0 -2px 0 #165B9C;
    }

    .simah-table th {
        padding: 10px 8px;
        border: 2px solid #165B9C;
        border-top: none;
        border-right: none;
        border-left: none;
        white-space: nowrap;
    }

    .simah-table td {
        padding: 10px 8px;
        background: white;
        border: none;
        white-space: nowrap;
        text-align: center;
        font-size: 0.8rem;
        border-bottom: 1px solid #165B9C;
    }

    .simah-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .simah-table tbody tr:hover {
        background-color: #e8f0fa;
    }

    .simah-error {
        color: #d9534f;
        padding: 15px;
        background-color: #f2dede;
        border: 1px solid #ebccd1;
        border-radius: 4px;
        margin: 50px;
    }

    @keyframes simah-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
