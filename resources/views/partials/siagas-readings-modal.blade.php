<!-- Modal para exibir dados do poço SIAGAS -->
<div id="siagasReadingsModal" class="siagas-modal">
    <div class="siagas-modal-content">
        <!-- Cabeçalho -->
        <div class="siagas-modal-header">
            <div class="siagas-header-content">
                <img src="{{ asset('images/logo-top-sigmah.svg') }}" alt="Logo SIGMAH" class="logo-siagas-modal" />
                <div class="siagas-header-content-title">
                    <h2>Dados SIAGAS</h2>
                    <p>Informações detalhadas sobre poços artesianos e não artesianos no Brasil, coletados e
                        disponibilizados pelo Serviço Geológico do Brasil (SGB/CPRM).</p>
                </div>
            </div>
            <span id="closeSiagasModal" class="siagas-modal-close">&times;</span>
        </div>

        <!-- Informações do Poço -->
        <div class="siagas-modal-info">
            <div id="siagasModalIdPonto"></div>
        </div>

        <!-- Loading -->
        <div id="siagasLoadingSpinner" class="siagas-loading">
            <div class="siagas-spinner"></div>
            <p>Carregando dados...</p>
        </div>

        <!-- Dados -->
        <div id="siagasDataContainer" class="siagas-data-container" style="display: none;">
            <div id="siagasDataContent"></div>
        </div>

        <!-- Erro -->
        <div id="siagasErrorMessage" class="siagas-error" style="display: none;">
            <strong>Erro:</strong> <span id="siagasErrorText"></span>
        </div>
    </div>
</div>

<style>
    .siagas-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .siagas-modal-content {
        background-color: #ffffff;
        margin: 2% auto;
        border: 1px solid #888;
        width: 85%;
        height: 90%;
        overflow-y: auto;
    }

    .siagas-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        background-color: #E3EBFF;
        padding: 30px;
    }

    .logo-siagas-modal {
        height: 4.5rem;
    }

    .siagas-header-content {
        display: flex;
        align-items: center;
        gap: 2.25rem;
    }

    .siagas-header-content-title {
        display: flex;
        align-items: flex-start;
        flex-direction: column;
        gap: 5px;
        width: 60%;
    }

    .siagas-header-content-title h2 {
        color: #000000;
        font-weight: bold;
        font-size: 1.5rem;
        margin: 0;
    }

    .siagas-header-content-title p {
        color: #575F6E;
        font-size: 1rem;
        margin: 0;
    }

    .siagas-modal-close {
        cursor: pointer;
        font-size: 60px;
        font-weight: 300;
        color: #5C5E64;
    }

    .siagas-modal-close:hover {
        color: #000000;
    }

    .siagas-modal-info {
        margin: 20px 50px;
        padding: 10px;
    }

    .siagas-loading {
        text-align: center;
        padding: 20px;
    }

    .siagas-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #e16ccfff;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    .siagas-data-container {
        max-height: 27.5rem;
        overflow-y: auto;
        margin: 20px 50px;
    }

    .siagas-data-content {
        font-size: 14px;
    }

    .siagas-data-row {
        padding: 8px;
        border-bottom: 1px solid #eee;
        display: flex;
    }

    .siagas-data-row:nth-child(even) {
        background-color: #f9f9f9;
    }

    .siagas-data-row:hover {
        background-color: #f2f7ff;
    }

    .siagas-data-label {
        font-weight: bold;
        width: 200px;
        flex-shrink: 0;
    }

    .siagas-data-value {
        flex-grow: 1;
    }

    .siagas-error {
        color: #d9534f;
        padding: 15px;
        background-color: #f2dede;
        border: 1px solid #ebccd1;
        border-radius: 4px;
        margin-top: 10px;
    }
</style>
