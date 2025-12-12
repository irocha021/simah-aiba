<!-- Modal para exibir leituras HidroWeb Telemetria -->
<div id="hidrowebTelemetryReadingsModal" class="hidroweb-telemetry-modal">
    <div class="hidroweb-telemetry-modal-content">
        <!-- Cabeçalho -->
        <div class="hidroweb-telemetry-modal-header">
            <h2>📡 Leituras HidroWeb - Telemetria</h2>
            <span id="closeHidrowebTelemetryModal" class="hidroweb-telemetry-modal-close">&times;</span>
        </div>

        <!-- Informações da Estação -->
        <div class="hidroweb-telemetry-modal-info">
            <strong>Código da Estação:</strong> <span id="hidrowebTelemetryModalStationCode">-</span><br><br>
            <span style="font-size:12px;">Total de Leituras:</span> <span style="font-size:12px;" id="hidrowebTelemetryModalTotalReadings">-</span>
        </div>

        <!-- Loading -->
        <div id="hidrowebTelemetryLoadingSpinner" class="hidroweb-telemetry-loading">
            <div class="hidroweb-telemetry-spinner"></div>
            <p>Carregando leituras...</p>
        </div>

        <!-- Tabela -->
        <div id="hidrowebTelemetryTableContainer" class="hidroweb-telemetry-table-container" style="display: none;">
            <table class="hidroweb-telemetry-table">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Chuva Adotada</th>
                        <th>Cota Adotada</th>
                        <th>Vazão Adotada</th>
                    </tr>
                </thead>
                <tbody id="hidrowebTelemetryTableBody"></tbody>
            </table>
        </div>

        <!-- Erro -->
        <div id="hidrowebTelemetryErrorMessage" class="hidroweb-telemetry-error" style="display: none;">
            <strong>Erro:</strong> <span id="hidrowebTelemetryErrorText"></span>
        </div>
    </div>
</div>

<style>
.hidroweb-telemetry-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
.hidroweb-telemetry-modal-content { background-color: #fefefe; margin: 2% auto; padding: 20px; padding-bottom: 30px; border: 1px solid #888; width: 80%; max-width: 900px; max-height: 90vh; overflow-y: auto; border-radius: 8px; }
.hidroweb-telemetry-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #00cc66; padding-bottom: 10px; }
.hidroweb-telemetry-modal-header h2 { margin: 0; color: #00cc66; }
.hidroweb-telemetry-modal-close { cursor: pointer; font-size: 28px; font-weight: bold; color: #aaa; }
.hidroweb-telemetry-modal-close:hover { color: #00cc66; }
.hidroweb-telemetry-modal-info { margin-bottom: 15px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; }
.hidroweb-telemetry-loading { text-align: center; padding: 20px; }
.hidroweb-telemetry-spinner { border: 4px solid #f3f3f3; border-top: 4px solid #00cc66; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
.hidroweb-telemetry-table-container { max-height: 500px; overflow-y: auto; margin-bottom: 20px; }
.hidroweb-telemetry-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.hidroweb-telemetry-table thead { position: sticky; top: 0; background-color: #00cc66; color: white; z-index: 1; }
.hidroweb-telemetry-table th, .hidroweb-telemetry-table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
.hidroweb-telemetry-table tbody tr:nth-child(even) { background-color: #f9f9f9; }
.hidroweb-telemetry-table tbody tr:hover { background-color: #e6ffe6; }
.hidroweb-telemetry-error { color: #d9534f; padding: 15px; background-color: #f2dede; border: 1px solid #ebccd1; border-radius: 4px; margin-top: 10px; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>