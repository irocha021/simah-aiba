<aside class="fire-panel" id="firePanel" aria-label="Monitoramento de focos de calor">
    <div class="fire-panel-header">
        <div>
            <span class="fire-panel-kicker">Programa de Fogo</span>
            <h2>Monitoramento tempo real</h2>
        </div>
        <button class="fire-panel-close" id="firePanelClose" type="button" aria-label="Fechar painel de fogo">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="fire-status">
        <span class="fire-status-dot"></span>
        <span id="fireStatusText">Focos ativos detectados</span>
    </div>

    <div class="fire-clock">
        <i class="fas fa-clock"></i>
        <span>Horarios exibidos em UTC-3</span>
    </div>

    <section class="fire-tool-section">
        <h3><i class="fas fa-desktop"></i> Modo de exibicao</h3>
        <div class="fire-segmented" role="group" aria-label="Modo de exibicao dos focos">
            <button class="active" type="button" data-fire-display="general">
                <i class="fas fa-check"></i> Geral
            </button>
            <button type="button" data-fire-display="satellite">
                <i class="fas fa-globe"></i> Por Satelite
            </button>
        </div>

        <label for="fireTimeWindow">Janela temporal</label>
        <select id="fireTimeWindow">
            <option value="today">Todas as horas (Hoje)</option>
            <option value="24h">Ultimas 24 horas</option>
            <option value="48h">Ultimas 48 horas</option>
            <option value="72h">Ultimas 72 horas</option>
            <option value="all">Todo o periodo</option>
        </select>
        <p>Janela temporal referente ao horario UTC-3</p>

        <label for="fireSatelliteFilter" data-fire-filter="satellite">Satelite</label>
        <select id="fireSatelliteFilter" data-fire-filter="satellite">
            <option value="all">Todos os satelites</option>
        </select>

        <label for="fireMunicipalityFilter">Municipio</label>
        <select id="fireMunicipalityFilter">
            <option value="all">Todos os municipios</option>
        </select>

        <label for="fireRiskType" data-fire-filter="risk">Camada de risco</label>
        <select id="fireRiskType" data-fire-filter="risk">
            <option value="focus">Zonas de atencao por focos</option>
            <option value="uc">Unidades de conservacao</option>
            <option value="app">Areas de preservacao permanente</option>
            <option value="reserva">Reservas legais</option>
        </select>
    </section>

    <section class="fire-summary">
        <div class="fire-summary-main">
            <span>Focos no periodo</span>
            <strong id="fireTotal">0</strong>
        </div>
        <div>
            <strong id="fireMunicipalitiesAffected">0</strong>
            <span>Municipios com ocorrencia</span>
        </div>
        <div>
            <strong id="fireMunicipalitiesTotal">0</strong>
            <span>Municipios monitorados</span>
        </div>
        <div>
            <strong id="fireSatellites">0</strong>
            <span>Satelites no filtro</span>
        </div>
        <div>
            <strong id="fireLastRecord">--</strong>
            <span>Registro mais recente</span>
        </div>
    </section>

    <section class="fire-wind-card" id="fireWindCard">
        <h3><i class="fas fa-wind"></i> Ventos e fogo</h3>
        <div>
            <strong id="fireWindAvg">--</strong>
            <span>Vento medio</span>
        </div>
        <div>
            <strong id="fireWindDirection">--</strong>
            <span>Direcao predominante</span>
        </div>
        <p id="fireWindStatus">Dados carregados ao selecionar Ventos e Fogo.</p>
    </section>

    <section class="fire-list-section">
        <div class="fire-list-heading">
            <h3>Lista de focos</h3>
            <span>UTC-3</span>
        </div>
        <div class="fire-list" id="fireList">
            <div class="fire-empty">Nenhum foco no filtro atual.</div>
        </div>
    </section>

    <section class="fire-sos">
        <h3><i class="fas fa-asterisk"></i> Canal SOS</h3>
        <div>
            <span>Corpo de Bombeiros</span>
            <strong>193</strong>
        </div>
        <div>
            <span>Defesa Civil</span>
            <strong>199</strong>
        </div>
    </section>
</aside>
