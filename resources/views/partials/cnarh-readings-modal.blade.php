<!-- Modal para exibir dados CNARH -->
<div id="cnarhReadingsModal" class="cnarh-modal">
    <div class="cnarh-modal-content">
        <!-- Cabeçalho -->
        <div class="cnarh-modal-header">
            <h2>Dados CNARH</h2>
            <span id="closeCnarhModal" class="cnarh-modal-close">&times;</span>
        </div>

        <!-- Informações -->
        <div class="cnarh-modal-info">
            <strong>Código CNARH:</strong> <span id="cnarhModalCode">-</span>
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
.cnarh-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
.cnarh-modal-content { background-color: #fefefe; margin: 2% auto; padding: 20px; padding-bottom: 30px; border: 1px solid #888; width: 80%; max-width: 900px; max-height: 90vh; overflow-y: auto; border-radius: 8px; }
.cnarh-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #A47864; padding-bottom: 10px; }
.cnarh-modal-header h2 { margin: 0; color: #A47864; }
.cnarh-modal-close { cursor: pointer; font-size: 28px; font-weight: bold; color: #aaa; }
.cnarh-modal-close:hover { color: #A47864; }
.cnarh-modal-info { margin-bottom: 15px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; }
.cnarh-loading { text-align: center; padding: 20px; }
.cnarh-spinner { border: 4px solid #f3f3f3; border-top: 4px solid #A47864; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
.cnarh-data-container { max-height: 500px; overflow-y: auto; margin-bottom: 20px; }
.cnarh-data-row { padding: 8px; border-bottom: 1px solid #eee; display: flex; }
.cnarh-data-row:nth-child(even) { background-color: #f9f9f9; }
.cnarh-data-row:hover { background-color: #f3ebe6; }
.cnarh-data-label { font-weight: bold; width: 200px; flex-shrink: 0; }
.cnarh-data-value { flex-grow: 1; }
.cnarh-error { color: #d9534f; padding: 15px; background-color: #f2dede; border: 1px solid #ebccd1; border-radius: 4px; margin-top: 10px; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>