<!-- Modal para exibir leituras LRGS Client (DCP) -->
<div id="lrgsReadingsModal" class="lrgs-modal">
    <div class="lrgs-modal-content">
        <!-- Cabeçalho -->
        <div class="lrgs-modal-header">
            <h2>🛰️ Leituras LRGS Client (DCP)</h2>
            <span id="closeLrgsModal" class="lrgs-modal-close">&times;</span>
        </div>

        <!-- Informações da Estação -->
        <div class="lrgs-modal-info">
            <strong>Código da Estação:</strong> <span id="lrgsModalStationCode">-</span><br><br>
            <span style="font-size:12px;">Total de Leituras:</span> <span style="font-size:12px;" id="lrgsModalTotalReadings">-</span>
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

<style>
.lrgs-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
.lrgs-modal-content { background-color: #fefefe; margin: 2% auto; padding: 20px; padding-bottom: 30px; border: 1px solid #888; width: 95%; max-width: 1400px; max-height: 90vh; overflow-y: auto; border-radius: 8px; }
.lrgs-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #ff7800; padding-bottom: 10px; }
.lrgs-modal-header h2 { margin: 0; color: #ff7800; }
.lrgs-modal-close { cursor: pointer; font-size: 28px; font-weight: bold; color: #aaa; }
.lrgs-modal-close:hover { color: #ff7800; }
.lrgs-modal-info { margin-bottom: 15px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; }
.lrgs-loading { text-align: center; padding: 20px; }
.lrgs-spinner { border: 4px solid #f3f3f3; border-top: 4px solid #ff7800; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
.lrgs-table-container { max-height: 600px; overflow-x: auto; overflow-y: auto; margin-bottom: 20px; position: relative; }
.lrgs-table { width: 100%; border-collapse: collapse; font-size: 12px; table-layout: auto; }
.lrgs-table thead { position: sticky; top: 0; background-color: #ff7800; color: white; z-index: 10; }
.lrgs-table thead th { position: sticky; top: 0; background-color: #ff7800; }
.lrgs-table th { padding: 10px 8px; border: 1px solid #ddd; text-align: left; white-space: nowrap; font-size: 11px; }
.lrgs-table td { padding: 10px 8px; border: 1px solid #ddd; text-align: left; white-space: nowrap; }
.lrgs-table tbody tr:nth-child(even) { background-color: #f9f9f9; }
.lrgs-table tbody tr:hover { background-color: #fff4e6; }
.lrgs-error { color: #d9534f; padding: 15px; background-color: #f2dede; border: 1px solid #ebccd1; border-radius: 4px; margin-top: 10px; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>