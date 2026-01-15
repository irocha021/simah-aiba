<!-- Modal para exibir dados CNARH -->
<div id="cnarhReadingsModal" class="cnarh-modal">
    <div class="cnarh-modal-content">
        <!-- Cabeçalho -->
        <div class="cnarh-modal-header">
            <div class="cnarh-header-content">
                <img src="{{ asset('images/logo-top-sigmah.svg') }}" alt="Logo SIGMAH" class="logo-cnarh-modal" />
                <div class="cnarh-header-content-title">
                    <h2>Dados CNARH</h2>
                    <p>Informações cadastrais sobre a captação e o uso de recursos hídricos no Brasil, coletadas e
                        disponibilizadas pela Agência Nacional de Águas (ANA).</p>
                </div>
            </div>
            <span id="closeCnarhModal" class="cnarh-modal-close">&times;</span>
        </div>

        <!-- Informações -->
        <div class="cnarh-modal-info">
            <span id="cnarhModalCode"></span>
        </div>

        <!-- Loading -->
        <div id="cnarhLoadingSpinner" class="cnarh-loading">
            <div class="cnarh-spinner"></div>
            <p>Carregando dados...</p>
        </div>

        <!-- Dados -->
        <div id="cnarhDataContainer" class="cnarh-data-container" style="display: none;">
            <div id="cnarhDataContent"></div>
        </div>

        <!-- Erro -->
        <div id="cnarhErrorMessage" class="cnarh-error" style="display: none;">
            <strong>Erro:</strong> <span id="cnarhErrorText"></span>
        </div>
    </div>
</div>

<style>
    .cnarh-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .cnarh-modal-content {
        background-color: #ffffff;
        margin: 2% auto;
        border: 1px solid #888;
        width: 85%;
        height: 90%;
        overflow-y: auto;
    }

    .cnarh-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        background-color: #E3EBFF;
        padding: 30px;
    }

    .logo-cnarh-modal {
        height: 4.5rem;
    }

    .cnarh-header-content {
        display: flex;
        align-items: center;
        gap: 2.25rem;
    }

    .cnarh-header-content-title {
        display: flex;
        align-items: flex-start;
        flex-direction: column;
        gap: 5px;
        width: 60%;
    }

    .cnarh-header-content-title h2 {
        color: #000000;
        font-weight: bold;
        font-size: 1.5rem;
        margin: 0;
    }

    .cnarh-header-content-title p {
        color: #575F6E;
        font-size: 1rem;
        margin: 0;
    }

    .cnarh-modal-close {
        cursor: pointer;
        font-size: 60px;
        font-weight: 300;
        color: #5C5E64;
    }

    .cnarh-modal-close:hover {
        color: #000000;
    }

    .cnarh-modal-info {
        margin: 20px 50px;
        padding: 10px;
    }

    .cnarh-loading {
        text-align: center;
        padding: 20px;
    }

    .cnarh-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3388ff;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    .cnarh-data-container {
        max-height: 26rem;
        overflow-y: auto;
        margin: 20px 50px;
    }

    .cnarh-data-content {
        font-size: 14px;
    }

    .cnarh-data-row {
        padding: 12px 8px;
        border-bottom: 1px solid #3388ff;
        display: flex;
        align-items: center;
    }

    .cnarh-data-row:nth-child(even) {
        background-color: #f9f9f9;
    }

    .cnarh-data-row:hover {
        background-color: #e6f2ff;
        /* Azul claro no hover */
    }

    /* RÓTULOS (CHAVES) */
    .cnarh-data-label {
        font-weight: bold;
        width: 200px;
        flex-shrink: 0;
        color: black;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }

    /* VALORES */
    .cnarh-data-value {
        flex-grow: 1;
        color: #333333;
        font-size: 0.85rem;
    }

    .cnarh-error {
        color: #d9534f;
        padding: 15px;
        background-color: #f2dede;
        border: 1px solid #ebccd1;
        border-radius: 4px;
        margin-top: 10px;
    }
</style>
