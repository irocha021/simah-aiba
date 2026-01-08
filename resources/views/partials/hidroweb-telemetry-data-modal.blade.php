<!-- Modal para exibir dados HidroWeb Telemetria (Leituras + Previsões) -->
<div id="hidrowebTelemetryDataModal" class="hidroweb-data-modal">
    <div class="hidroweb-data-modal-content">
        <!-- Cabeçalho -->
        <div class="hidroweb-data-modal-header">
            <h2>📡 Dados HidroWeb - Telemetria</h2>
            <span id="closeHidrowebDataModal" class="hidroweb-data-modal-close">&times;</span>
        </div>

        <!-- Informações da Estação -->
        <div class="hidroweb-data-modal-info">
            <strong>Código da Estação:</strong> <span id="hidrowebDataModalStationCode">-</span><br>
            <strong>Nome:</strong> <span id="hidrowebDataModalStationName">-</span>
        </div>

        <!-- Tabs -->
        <div class="hidroweb-data-tabs">
            <button class="hidroweb-data-tab-btn active" data-tab="leituras">📊 Leituras</button>
            <button class="hidroweb-data-tab-btn" data-tab="previsoes">📈 Previsões</button>
        </div>

        <!-- Tab: Leituras -->
        <div id="tab-leituras" class="hidroweb-data-tab-content active">
            <!-- Loading -->
            <div id="leiturasLoadingSpinner" class="hidroweb-data-loading">
                <div class="hidroweb-data-spinner"></div>
                <p>Carregando leituras...</p>
            </div>

            <!-- Tabela Leituras -->
            <div id="leiturasTableContainer" class="hidroweb-data-table-container" style="display: none;">
                <p style="font-size:12px; margin-bottom: 10px;">Total de Leituras: <strong id="leiturasTotal">-</strong></p>
                <table class="hidroweb-data-table">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Chuva Adotada</th>
                            <th>Cota Adotada</th>
                            <th>Vazão Adotada</th>
                        </tr>
                    </thead>
                    <tbody id="leiturasTableBody"></tbody>
                </table>
            </div>

            <!-- Erro Leituras -->
            <div id="leiturasErrorMessage" class="hidroweb-data-error" style="display: none;">
                <strong>Erro:</strong> <span id="leiturasErrorText"></span>
            </div>
        </div>

        <!-- Tab: Previsões -->
        <div id="tab-previsoes" class="hidroweb-data-tab-content">
            <!-- Loading -->
            <div id="previsoesLoadingSpinner" class="hidroweb-data-loading">
                <div class="hidroweb-data-spinner"></div>
                <p>Carregando previsões...</p>
            </div>

            <!-- Tabela Previsões -->
            <div id="previsoesTableContainer" class="hidroweb-data-table-container" style="display: none;">
                <p style="font-size:12px; margin-bottom: 10px;">Total de Previsões: <strong id="previsoesTotal">-</strong></p>
                <table class="hidroweb-data-table">
                    <thead>
                        <tr>
                            <th>Ano</th>
                            <th>Mês</th>
                            <th>Vazão Prevista (m³/s)</th>
                            <th>Alfa Pond</th>
                            <th>Q90</th>
                            <th>Vsup</th>
                        </tr>
                    </thead>
                    <tbody id="previsoesTableBody"></tbody>
                </table>
            </div>

            <!-- Erro Previsões -->
            <div id="previsoesErrorMessage" class="hidroweb-data-error" style="display: none;">
                <strong>Erro:</strong> <span id="previsoesErrorText"></span>
            </div>

            <!-- Sem previsões -->
            <div id="previsoesEmpty" class="hidroweb-data-empty" style="display: none;">
                <p>⚠️ Esta estação não possui previsões de vazão.</p>
            </div>
        </div>
    </div>
</div>

<style>
.hidroweb-data-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
.hidroweb-data-modal-content { background-color: #fefefe; margin: 2% auto; padding: 20px; padding-bottom: 30px; border: 1px solid #888; width: 80%; max-width: 900px; max-height: 90vh; overflow-y: auto; border-radius: 8px; }
.hidroweb-data-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #00cc66; padding-bottom: 10px; }
.hidroweb-data-modal-header h2 { margin: 0; color: #00cc66; }
.hidroweb-data-modal-close { cursor: pointer; font-size: 28px; font-weight: bold; color: #aaa; }
.hidroweb-data-modal-close:hover { color: #00cc66; }
.hidroweb-data-modal-info { margin-bottom: 15px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; }

/* Tabs */
.hidroweb-data-tabs { display: flex; gap: 5px; margin-bottom: 20px; border-bottom: 2px solid #ddd; }
.hidroweb-data-tab-btn { flex: 1; padding: 12px; background-color: #f9f9f9; border: none; border-bottom: 3px solid transparent; cursor: pointer; font-size: 14px; font-weight: bold; transition: all 0.3s; }
.hidroweb-data-tab-btn:hover { background-color: #e6e6e6; }
.hidroweb-data-tab-btn.active { background-color: #fff; border-bottom-color: #00cc66; color: #00cc66; }
.hidroweb-data-tab-content { display: none; }
.hidroweb-data-tab-content.active { display: block; }

.hidroweb-data-loading { text-align: center; padding: 20px; }
.hidroweb-data-spinner { border: 4px solid #f3f3f3; border-top: 4px solid #00cc66; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
.hidroweb-data-table-container { max-height: 500px; overflow-y: auto; margin-bottom: 20px; }
.hidroweb-data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.hidroweb-data-table thead { position: sticky; top: 0; background-color: #00cc66; color: white; z-index: 1; }
.hidroweb-data-table th, .hidroweb-data-table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
.hidroweb-data-table tbody tr:nth-child(even) { background-color: #f9f9f9; }
.hidroweb-data-table tbody tr:hover { background-color: #e6ffe6; }
.hidroweb-data-error { color: #d9534f; padding: 15px; background-color: #f2dede; border: 1px solid #ebccd1; border-radius: 4px; margin-top: 10px; }
.hidroweb-data-empty { padding: 20px; text-align: center; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; color: #856404; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
