<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mapa</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- MarkerCluster CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <style>
        body {
            margin: 0;
            padding: 0;
        }

        #map {
            width: 100%;
            height: 100vh;
        }

        /* ===== ESTILOS PARA POPUPS DO MAPA ===== */

        /* Estilização básica dos popups do Leaflet */
        .leaflet-popup-content-wrapper {
            border-radius: 12px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
            border: 1px solid #ddd !important;
            padding: 0 !important;
            overflow: hidden !important;
        }

        .leaflet-popup-content {
            margin: 0 !important;
            line-height: 1.5 !important;
            font-size: 14px !important;
            width: 300px !important;
        }

        .leaflet-popup-tip {
            box-shadow: 0 3px 14px rgba(0, 0, 0, 0.1) !important;
        }

        /* Container principal do popup */
        .map-popup-content {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 0;
        }

        /* Header do popup */
        .popup-header {
            background: #f8f9fa;
            padding: 15px 15px 10px 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .popup-title {
            width: 85%;
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin: 5px 0;
            line-height: 1.3;
        }

        /* Informações da estação */
        .popup-info {
            padding: 15px;
            background: white;
        }

        .popup-info-row {
            margin-bottom: 8px;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .popup-info-row strong {
            color: #555;
        }

        /* Container dos botões */
        .popup-buttons {
            padding: 15px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        /* Botões estilizados */
        .popup-button {
            display: block;
            width: 100%;
            padding: 12px 16px;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            margin-bottom: 10px;
        }

        .popup-button:hover {
            background: #165b9c;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(22, 91, 156, 0.2);
        }

        .popup-button:last-child {
            margin-bottom: 0;
        }

        /* Cores específicas dos botões */
        .popup-button {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: transparent;
            color: #165b9c;
            border: 2px solid #165b9c;
            border-radius: 25px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            justify-content: center;
        }

        /* Estilo mais específico para o botão fechar */
        .leaflet-container a.leaflet-popup-close-button {
            width: 32px !important;
            height: 32px !important;
            font-size: 32px !important;
            line-height: 25px !important;
            background: #165b9c !important;
            color: white !important;
            border-radius: 50% !important;
            text-align: center !important;
            text-decoration: none !important;
            padding: 0 !important;
            margin: 0 !important;
            top: 15px !important;
            right: 15px !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3) !important;
        }

        .leaflet-container a.leaflet-popup-close-button:hover {
            background: #165b9c !important;
            transform: scale(1.1) !important;
        }
    </style>
</head>

<body>
    <div id="map"></div>

    {{-- Side Menu --}}
    @include('components.side-menu')

    <!-- Painel GEOMAP -->
    @include('partials.geomap-panel')

    <!-- Painel de Legenda -->
    @include('partials.legend-panel')

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <!-- GEOMAP Panel JS -->
    <script src="{{ asset('js/geomap-panel.js') }}"></script>

    <script>
        // Inicializar layers do GEOMAP com dados do banco
        var geoMapLayers = initGeoMapLayers(@json($mapLayers));

        // Criar o mapa
        var map = L.map('map').setView([-13.0, -41.5], 6);
        map.zoomControl.setPosition('bottomright');

        // Setar referência do mapa para o GEOMAP panel
        setMapReference(map);

        // Tile layers
        var tileSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
        });
        var tileStreet = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        });

        // Satélite como padrão
        tileSatellite.addTo(map);
        var activeBaseTile = tileSatellite;

        document.getElementById('btn-satellite').addEventListener('click', function() {
            map.removeLayer(activeBaseTile);
            tileSatellite.addTo(map);
            activeBaseTile = tileSatellite;
            this.classList.add('active');
            document.getElementById('btn-street').classList.remove('active');
            bringGeoMapLayersToFront();
        });
        document.getElementById('btn-street').addEventListener('click', function() {
            map.removeLayer(activeBaseTile);
            tileStreet.addTo(map);
            activeBaseTile = tileStreet;
            this.classList.add('active');
            document.getElementById('btn-satellite').classList.remove('active');
            bringGeoMapLayersToFront();
        });


        // Criar MarkerClusterGroups para cada tipo
        var clusterGroups = {
            'cnarh': L.markerClusterGroup({
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true
            }),
            'hidroweb_qualidade_agua': L.markerClusterGroup({
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true
            }),
            'hidroweb_telemetria': L.markerClusterGroup({
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true
            }),
            'hidroweb_telemetria_com_previsao': L.markerClusterGroup({
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true
            }),
            'lrgs_client': L.markerClusterGroup({
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true
            }),
            'pocos_rimas': L.markerClusterGroup({
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true
            }),
            'pocos_siagas': L.markerClusterGroup({
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true
            })
        };

        window.clusterGroups = clusterGroups; // Expoe globalmente
        if (window.layerControl && window.layerControl.updateCounts) {
            window.layerControl.updateCounts();
        }

        // Função para retornar o estilo do marcador baseado no source
        function getMarkerStyle(source) {
            var styles = {
                'cnarh': {
                    radius: 5,
                    color: "#A47864",
                    fillColor: "#fff",
                    weight: 6,
                    opacity: 1,
                    fillOpacity: 0.7
                },
                'hidroweb_qualidade_agua': {
                    radius: 5,
                    color: "#3388ff",
                    fillColor: "#fff",
                    weight: 6,
                    opacity: 1,
                    fillOpacity: 0.7
                },
                'hidroweb_telemetria': {
                    radius: 5,
                    color: "#00cc66",
                    fillColor: "#fff",
                    weight: 6,
                    opacity: 1,
                    fillOpacity: 0.7
                },
                'hidroweb_telemetria_com_previsao': {
                    radius: 5,
                    fillColor: "#fff",
                    color: "#9933ff",
                    weight: 6,
                    opacity: 1,
                    fillOpacity: 0.7
                },
                'lrgs_client': {
                    radius: 5,
                    color: "#ff7800",
                    fillColor: "#fff",
                    weight: 6,
                    opacity: 1,
                    fillOpacity: 0.7
                },
                'pocos_rimas': {
                    radius: 5,
                    color: "#ff0000",
                    fillColor: "#fff",
                    weight: 6,
                    opacity: 1,
                    fillOpacity: 0.7
                },
                'pocos_siagas': {
                    radius: 5,
                    color: "#e16ccfff",
                    fillColor: "#fff",
                    weight: 6,
                    opacity: 1,
                    fillOpacity: 0.7
                }
            };

            return styles[source] || styles['hidroweb_qualidade_agua'];
        }

        // Função global para criar marcadores (usada pelo layer-control.js)
        window.createMarker = function(station) {
            var style = getMarkerStyle(station.source);
            var marker = L.circleMarker([station.latitude, station.longitude], style);

            // Criar popup com botão para ver leituras/dados - ESTILIZADO
            var popupContent = `
                <div class="map-popup-content">
                    <div class="popup-header">
                        <div class="popup-title">${station.name}</div>
                        <div style="color: #666; font-size: 0.85rem;">Fonte: ${station.source}</div>
                    </div>
                    
                    <div class="popup-info">
                        <div class="popup-info-row"><strong>Código:</strong> ${station.code}</div>
                        <div class="popup-info-row"><strong>Latitude:</strong> ${station.latitude}</div>
                        <div class="popup-info-row"><strong>Longitude:</strong> ${station.longitude}</div>
                    </div>
                    
                    <div class="popup-buttons">
            `;

            // Botões específicos por tipo
            if (station.source === 'pocos_rimas') {
                popupContent += `
                    <button onclick="openRimasReadingsModal('${station.code}', '${station.name}', '${station.latitude}', '${station.longitude}')" 
                            class="popup-button rimas">
                        Ver Leituras (Últimas 50)
                    </button>
                `;
            }

            if (station.source === 'pocos_siagas') {
                popupContent += `
                    <button onclick="openSiagasReadingsModal('${station.code}', '${station.name}', '${station.latitude}', '${station.longitude}')" 
                            class="popup-button siagas">
                        Ver Dados do Poço
                    </button>
                `;
            }

            if (station.source === 'hidroweb_qualidade_agua') {
                popupContent += `
                    <button onclick="openHidrowebQaReadingsModal('${station.code}', '${station.name}', '${station.latitude}', '${station.longitude}')" 
                            class="popup-button hidroweb-qa">
                        Ver Leituras (Últimas 50)
                    </button>
                `;
            }

            if (station.source === 'lrgs_client') {
                popupContent += `
                    <button onclick="openLrgsReadingsModal('${station.code}', '${station.name}', '${station.latitude}', '${station.longitude}')" 
                            class="popup-button lrgs">
                        Ver Leituras (Últimas 72)
                    </button>
                `;
            }

            if (station.source === 'hidroweb_telemetria' || station.source === 'hidroweb_telemetria_com_previsao') {
                var buttonClass = station.source === 'hidroweb_telemetria_com_previsao' ?
                    'hidroweb-telemetria-previsao' : 'hidroweb-telemetria';
                popupContent += `
                    <button onclick="openHidrowebTelemetryDataModal('${station.code}', '${station.name}', '${station.latitude}', '${station.longitude}')" 
                            class="popup-button ${buttonClass}">
                        Ver Dados
                    </button>
                `;
            }

            if (station.source === 'cnarh') {
                popupContent += `
                    <button onclick="openCnarhReadingsModal('${station.code}', '${station.name}', '${station.latitude}', '${station.longitude}')" 
                            class="popup-button cnarh">
                        Ver Dados CNARH
                    </button>
                `;
            }

            popupContent += `
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);
            return marker;
        };

        // Camadas serão carregadas sob demanda pelo layer-control.js
        console.log('Mapa inicializado. Camadas serão carregadas sob demanda.');

    </script>

    <!-- Modal JS -->
    <script src="{{ asset('js/station-modal.js') }}"></script>

    <!-- Modals HTML -->
    @include('partials.rimas-readings-modal')
    @include('partials.siagas-readings-modal')
    @include('partials.hidroweb-qa-readings-modal')
    @include('partials.lrgs-readings-modal')
    @include('partials.hidroweb-telemetry-data-modal')
    @include('partials.cnarh-readings-modal')
</body>

</html>
