<!-- Modal para exibir leituras de qualidade da água HidroWeb -->
<div id="hidrowebQaReadingsModal" class="hidroweb-qa-modal">
    <div class="hidroweb-qa-modal-content">
        <!-- Cabeçalho -->
        <div class="hidroweb-qa-modal-header">
            <h2>Leituras HidroWeb - Qualidade da Água</h2>
            <span id="closeHidrowebQaModal" class="hidroweb-qa-modal-close">&times;</span>
        </div>

        <!-- Informações da Estação -->
        <div class="hidroweb-qa-modal-info">
            <strong>Código da Estação:</strong> <span id="hidrowebQaModalStationCode">-</span><br><br>
            <span style="font-size:12px;">Total de Leituras:</span> <span style="font-size:12px;" id="hidrowebQaModalTotalReadings">-</span>
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
.hidroweb-qa-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
.hidroweb-qa-modal-content { background-color: #fefefe; margin: 2% auto; padding: 20px; padding-bottom: 30px; border: 1px solid #888; width: 95%; max-width: 1400px; max-height: 90vh; overflow-y: auto; border-radius: 8px; }
.hidroweb-qa-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #3388ff; padding-bottom: 10px; }
.hidroweb-qa-modal-header h2 { margin: 0; color: #3388ff; }
.hidroweb-qa-modal-close { cursor: pointer; font-size: 28px; font-weight: bold; color: #aaa; }
.hidroweb-qa-modal-close:hover { color: #3388ff; }
.hidroweb-qa-modal-info { margin-bottom: 15px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; }
.hidroweb-qa-loading { text-align: center; padding: 20px; }
.hidroweb-qa-spinner { border: 4px solid #f3f3f3; border-top: 4px solid #3388ff; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
.hidroweb-qa-table-container { max-height: 600px; overflow-x: auto; overflow-y: auto; margin-bottom: 20px; position: relative; }
.hidroweb-qa-table { width: 100%; border-collapse: collapse; font-size: 12px; table-layout: auto; }
.hidroweb-qa-table thead { position: sticky; top: 0; background-color: #3388ff; color: white; z-index: 10; }
.hidroweb-qa-table thead th { position: sticky; top: 0; background-color: #3388ff; }
.hidroweb-qa-table th { padding: 10px 8px; border: 1px solid #ddd; text-align: left; white-space: nowrap; font-size: 11px; }
.hidroweb-qa-table td { padding: 10px 8px; border: 1px solid #ddd; text-align: left; white-space: nowrap; }
.hidroweb-qa-table tbody tr:nth-child(even) { background-color: #f9f9f9; }
.hidroweb-qa-table tbody tr:hover { background-color: #e6f2ff; }
.hidroweb-qa-error { color: #d9534f; padding: 15px; background-color: #f2dede; border: 1px solid #ebccd1; border-radius: 4px; margin-top: 10px; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>