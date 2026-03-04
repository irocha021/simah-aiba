<style>
    /* Painel GEOMAP */
    .geomap-panel {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        max-height: 80vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-width: 280px;
    }

    .geomap-panel-header {
        background: #f8f9fa;
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
        color: #333;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    #geomapToggleIcon {
        line-height: 25px;
        background: #165b9c;
        color: white;
        border-radius: 50%;
        text-align: center;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }

    #geomapToggleIcon:hover {
        background: #165b9c;
        transform: scale(1.1);
    }

    .geomap-panel-content {
        max-height: 400px;
        overflow-y: auto;
        padding: 8px;
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
        <span id="geomapToggleIcon">
            <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px" fill="#FFFFFF">
                <path
                    d="M480-528 324-372q-11 11-28 11t-28-11q-11-11-11-28t11-28l184-184q12-12 28-12t28 12l184 184q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-528Z" />
            </svg>
        </span>
    </div>
    <div class="geomap-panel-content" id="geomapPanelContent">
        <!-- Items serão inseridos via JavaScript -->
    </div>
</div>
