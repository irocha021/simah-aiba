<!-- Modal para exibir leituras do poço RIMAS -->
<div id="rimasReadingsModal" class="rimas-modal">
    <div class="rimas-modal-content">
        <!-- Cabeçalho -->
        <div class="rimas-modal-header">
            <h2>Leituras do Poço RIMAS</h2>
            <span id="closeRimasModal" class="rimas-modal-close">&times;</span>
        </div>

        <!-- Informações do Poço -->
        <div class="rimas-modal-info">
            <strong>ID do Ponto:</strong> <span id="rimasModalIdPonto">-</span><br><br>
            <span style="font-size:12px;">Total de Leituras:</span> <span style="font-size:12px;" id="rimasModalTotalReadings">-</span>
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
                        <th>Nº Medição</th>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Nível da Água</th>
                        <th>Observação</th>
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
.rimas-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
.rimas-modal-content { background-color: #fefefe; margin: 2% auto; padding: 20px; padding-bottom: 30px; border: 1px solid #888; width: 80%; max-width: 900px; max-height: 90vh; overflow-y: auto; border-radius: 8px; }
.rimas-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #ff0000; padding-bottom: 10px; }
.rimas-modal-header h2 { margin: 0; color: #ff0000; }
.rimas-modal-close { cursor: pointer; font-size: 28px; font-weight: bold; color: #aaa; }
.rimas-modal-close:hover { color: #ff0000; }
.rimas-modal-info { margin-bottom: 15px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; }
.rimas-loading { text-align: center; padding: 20px; }
.rimas-spinner { border: 4px solid #f3f3f3; border-top: 4px solid #ff0000; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
.rimas-table-container { max-height: 400px; overflow-y: auto; margin-bottom: 20px; }
.rimas-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.rimas-table thead { position: sticky; top: 0; background-color: #ff0000; color: white; z-index: 1; }
.rimas-table th, .rimas-table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
.rimas-table th:nth-child(4), .rimas-table td:nth-child(4) { text-align: right; font-weight: bold; }
.rimas-table tbody tr:nth-child(even) { background-color: #f9f9f9; }
.rimas-table tbody tr:hover { background-color: #ffe6e6; }
.rimas-error { color: #d9534f; padding: 15px; background-color: #f2dede; border: 1px solid #ebccd1; border-radius: 4px; margin-top: 10px; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>