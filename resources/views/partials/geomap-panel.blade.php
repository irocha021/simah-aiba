<style>
/* Painel GEOMAP */
.geomap-panel {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1000;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    max-height: 80vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-width: 280px;
}

.geomap-panel-header {
    background: #2c3e50;
    color: white;
    padding: 10px 15px;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.geomap-panel-header:hover {
    background: #34495e;
}

.geomap-panel-content {
    max-height: 400px;
    overflow-y: auto;
    padding: 5px 0;
}

.geomap-layer-item {
    display: flex;
    align-items: center;
    padding: 8px 15px;
    cursor: grab;
    border-bottom: 1px solid #eee;
    background: white;
    transition: background 0.2s;
}

.geomap-layer-item:hover {
    background: #f5f5f5;
}

.geomap-layer-item.dragging {
    opacity: 0.5;
    background: #e3f2fd;
}

.geomap-layer-item input[type="checkbox"] {
    margin-right: 10px;
}

.geomap-layer-item .layer-name {
    flex: 1;
    font-size: 13px;
}

.geomap-layer-item .layer-info {
    color: #2196F3;
    cursor: pointer;
    margin-left: 8px;
    font-size: 16px;
}

.geomap-layer-item .layer-info:hover {
    color: #1976D2;
}

.geomap-layer-item .drag-handle {
    color: #999;
    margin-right: 10px;
    cursor: grab;
}
</style>

<!-- Painel GEOMAP -->
<div class="geomap-panel" id="geomapPanel">
    <div class="geomap-panel-header" onclick="toggleGeomapPanel()">
        <span>GEOMAP - Camadas</span>
        <span id="geomapToggleIcon">▼</span>
    </div>
    <div class="geomap-panel-content" id="geomapPanelContent">
        <!-- Items serão inseridos via JavaScript -->
    </div>
</div>
