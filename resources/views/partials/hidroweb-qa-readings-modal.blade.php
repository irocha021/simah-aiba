<!-- Modal para exibir leituras de qualidade da água HidroWeb -->
<div id="hidrowebQaReadingsModal" class="hidroweb-qa-modal">
    <div class="hidroweb-qa-modal-content">
        <!-- Cabeçalho -->
        <div class="hidroweb-qa-modal-header">
            <div class="hidroweb-qa-header-content">
                <img src="{{ asset('images/logo-top-sigmah.svg') }}" alt="Logo SIGMAH" class="logo-hidroweb-qa-modal" />
                <div class="hidroweb-qa-header-content-title">
                    <h2>Leituras HidroWeb - Qualidade da Água</h2>
                    <p>Informações detalhadas sobre parâmetros de qualidade da água em corpos hídricos do Brasil,
                        coletados e disponibilizados pela Agência Nacional de Águas (ANA) via Rede HidroWeb.</p>
                </div>
            </div>
            <span id="closeHidrowebQaModal" class="hidroweb-qa-modal-close">&times;</span>
        </div>

        <!-- Informações da Estação -->
        <div class="hidroweb-qa-modal-info">
            <span id="hidrowebQaModalStationCode"></span>
            <div style="margin: 10px">
                <span style="font-size:12px; color: #575F6E;">Total de Leituras:</span>
                <span style="font-size:12px; color: #575F6E;" id="hidrowebQaModalTotalReadings">-</span>
            </div>
        </div>

        <!-- Loading -->
        <div id="hidrowebQaLoadingSpinner" class="hidroweb-qa-loading">
            <div class="hidroweb-qa-spinner"></div>
            <p>Carregando leituras...</p>
        </div>

        <!-- Tabela -->
        <div id="hidrowebQaTableContainer" class="hidroweb-qa-table-container" style="display: none;">
            <table class="hidroweb-qa-table">
                <thead id="hidrowebQaTableHeader">
                    <!-- Cabeçalhos serão adicionados via JavaScript -->
                </thead>
                <tbody id="hidrowebQaTableBody">
                    <!-- Linhas serão adicionadas via JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Erro -->
        <div id="hidrowebQaErrorMessage" class="hidroweb-qa-error" style="display: none;">
            <strong>Erro:</strong> <span id="hidrowebQaErrorText"></span>
        </div>
    </div>
</div>

<style>
    .hidroweb-qa-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .hidroweb-qa-modal-content {
        background-color: #ffffff;
        margin: 2% auto;
        border: 1px solid #888;
        width: 85%;
        height: 90%;
        overflow-y: auto;
    }

    .hidroweb-qa-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        background-color: #E3EBFF;
        padding: 30px;
    }

    .logo-hidroweb-qa-modal {
        height: 4.5rem;
    }

    .hidroweb-qa-header-content {
        display: flex;
        align-items: center;
        gap: 2.25rem;
    }

    .hidroweb-qa-header-content-title {
        display: flex;
        align-items: flex-start;
        flex-direction: column;
        gap: 5px;
        width: 60%;
    }

    .hidroweb-qa-header-content-title h2 {
        color: #000000;
        font-weight: bold;
        font-size: 1.5rem;
        margin: 0;
    }

    .hidroweb-qa-header-content-title p {
        color: #575F6E;
        font-size: 1rem;
        margin: 0;
    }

    .hidroweb-qa-modal-close {
        cursor: pointer;
        font-size: 60px;
        font-weight: 300;
        color: #5C5E64;
    }

    .hidroweb-qa-modal-close:hover {
        color: #000000;
    }

    .hidroweb-qa-modal-info {
        margin: 20px 50px;
        padding: 10px;
    }

    .hidroweb-qa-loading {
        text-align: center;
        padding: 20px;
    }

    .hidroweb-qa-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3388ff;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    .hidroweb-qa-table-container {
        max-height: 23.5rem;
        width: 92%;
        overflow-x: auto;
        overflow-y: auto;
        margin: auto;
        position: relative;
    }

    .hidroweb-qa-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }

    /* CABEÇALHO */
    .hidroweb-qa-table thead th {
        position: sticky;
        top: 0;
        background-color: #ffffff;
        color: black;
        text-align: center;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        padding: 10px 20px;
        border: none;
        white-space: nowrap;
        box-shadow: inset 0 -2px 0 #3388ff;
    }

    .hidroweb-qa-table thead {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    /* CORPO DA TABELA */
    .hidroweb-qa-table td {
        padding: 10px 8px;
        background: white;
        border: none;
        white-space: nowrap;
        text-align: center;
        font-size: 0.8rem;
        border-bottom: 1px solid #3388ff;
    }

    /* Remove a borda inferior da última linha */
    .hidroweb-qa-table tbody tr:last-child td {
        border-bottom: none;
    }

    .hidroweb-qa-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .hidroweb-qa-table tbody tr:hover {
        background-color: #e6f2ff;
    }

    .hidroweb-qa-error {
        color: #d9534f;
        padding: 15px;
        background-color: #f2dede;
        border: 1px solid #ebccd1;
        border-radius: 4px;
        margin-top: 10px;
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
