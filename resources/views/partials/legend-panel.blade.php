<style>
/* Painel de Legenda (estilo GEOBahia) */
.legend-panel {
    display: none;
    position: absolute;
    top: 10px;
    right: 300px;
    z-index: 1000;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    min-width: 200px;
    max-width: 280px;
}

.legend-panel.active {
    display: block;
}

.legend-panel-header {
    background: #f5f5f5;
    padding: 10px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
    border-radius: 8px 8px 0 0;
}

.legend-panel-header h4 {
    margin: 0;
    font-size: 14px;
    color: #333;
}

.legend-panel-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #666;
    padding: 0;
    line-height: 1;
}

.legend-panel-close:hover {
    color: #333;
}

.legend-panel-content {
    padding: 10px 15px;
    max-height: 300px;
    overflow-y: auto;
}

.legend-item {
    display: flex;
    align-items: center;
    padding: 5px 0;
}

.legend-color {
    width: 18px;
    height: 18px;
    margin-right: 10px;
    border: 1px solid #ccc;
}

.legend-label {
    font-size: 13px;
    color: #333;
}
</style>

<!-- Painel de Legenda (estilo GEOBahia) -->
<div class="legend-panel" id="legendPanel">
    <div class="legend-panel-header">
        <h4 id="legendPanelTitle">Legenda</h4>
        <button class="legend-panel-close" onclick="closeLegendPanel()">&times;</button>
    </div>
    <div class="legend-panel-content" id="legendPanelContent">
        <!-- Items da legenda serão inseridos via JavaScript -->
    </div>
</div>