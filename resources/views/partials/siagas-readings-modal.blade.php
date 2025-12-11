<!-- Modal para exibir dados do poço SIAGAS -->
<div id="siagasReadingsModal" class="siagas-modal">
    <div class="siagas-modal-content">
        <!-- Cabeçalho -->
        <div class="siagas-modal-header">
            <h2>Dados do Poço SIAGAS</h2>
            <span id="closeSiagasModal" class="siagas-modal-close">&times;</span>
        </div>

        <!-- Informações do Poço -->
        <div class="siagas-modal-info">
            <strong>ID do Ponto:</strong> <span id="siagasModalIdPonto">-</span>
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
.siagas-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
.siagas-modal-content { background-color: #fefefe; margin: 2% auto; padding: 20px; padding-bottom: 30px; border: 1px solid #888; width: 80%; max-width: 900px; max-height: 90vh; overflow-y: auto; border-radius: 8px; }
.siagas-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #e16ccfff; padding-bottom: 10px; }
.siagas-modal-header h2 { margin: 0; color: #e16ccfff; }
.siagas-modal-close { cursor: pointer; font-size: 28px; font-weight: bold; color: #aaa; }
.siagas-modal-close:hover { color: #e16ccfff; }
.siagas-modal-info { margin-bottom: 15px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; }
.siagas-loading { text-align: center; padding: 20px; }
.siagas-spinner { border: 4px solid #f3f3f3; border-top: 4px solid #e16ccfff; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
.siagas-data-container { max-height: 500px; overflow-y: auto; margin-bottom: 20px; }
.siagas-data-content { font-size: 14px; }
.siagas-data-row { padding: 8px; border-bottom: 1px solid #eee; display: flex; }
.siagas-data-row:nth-child(even) { background-color: #f9f9f9; }
.siagas-data-row:hover { background-color: #f3e6ff; }
.siagas-data-label { font-weight: bold; width: 200px; flex-shrink: 0; }
.siagas-data-value { flex-grow: 1; }
.siagas-error { color: #d9534f; padding: 15px; background-color: #f2dede; border: 1px solid #ebccd1; border-radius: 4px; margin-top: 10px; }
</style>