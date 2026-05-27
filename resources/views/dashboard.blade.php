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

        .map-label {
            font-size: 9px;
            font-weight: 400;
            letter-spacing: 0.3px;
            white-space: nowrap;
            pointer-events: none;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            text-align: center;
            transform: translateX(-50%);
        }

        body.mode-satellite .map-label {
            color: rgba(255, 255, 255, 0.85);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.7);
        }

        body.mode-street .map-label {
            color: rgba(40, 40, 40, 0.9);
            text-shadow: 0 0 3px rgba(255, 255, 255, 0.9), 0 0 2px rgba(255, 255, 255, 0.9);
        }

        /* ===== LABEL DO NOME DA ESTAÇÃO (junto ao ponto) ===== */
        /* .map-point-label {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            font-size: 10px;
            font-weight: 500;
            white-space: nowrap;
            pointer-events: none;
        }

       
        .map-point-label::before {
            display: none !important;
        }

        body.mode-satellite .map-point-label {
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
        }

        body.mode-street .map-point-label {
            color: rgba(40, 40, 40, 0.9);
            text-shadow: 0 0 3px rgba(255, 255, 255, 0.9), 0 0 2px rgba(255, 255, 255, 0.9);
        }

       
        body.hide-point-labels .map-point-label {
            display: none !important;
        } */

        /* ===== LABEL DO NOME DA ESTAÇÃO (junto ao ponto) =====
           3 estilos pra testar. Deixe APENAS UM bloco "ESTILO X" sem comentário
           por vez. As 2 regras finais (hide-point-labels e ::before) valem
           para todos e devem ficar sempre ativas. */

        /* Base comum a todos os estilos */
        .map-point-label {
            white-space: nowrap;
            pointer-events: none;
        }

        /* ---------- ESTILO 1: PÍLULA ESCURA (ATIVO) ---------- */
        /* .map-point-label {
            background: rgba(26, 26, 26, 0.75) !important;
            border: none !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4) !important;
            padding: 3px 8px !important;
            border-radius: 10px !important;
            color: #fff;
            font-size: 10px;
            font-weight: 600;
        } */
        /* ---------- FIM ESTILO 1 ---------- */

        /* ---------- ESTILO 2: PÍLULA NA COR DA CAMADA ---------- */
        /* .map-point-label {
            background: rgba(26, 26, 26, 0.78) !important;
            border: 1.5px solid #ff7800 !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4) !important;
            padding: 3px 8px !important;
            border-radius: 10px !important;
            color: #fff;
            font-size: 10px;
            font-weight: 600;
        } */
        /* ---------- FIM ESTILO 2 ---------- */

        /* ---------- ESTILO 3: TEXTO COM MAIS PESO (sem caixa) ---------- */
        .map-point-label {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            font-size: 12px;
            font-weight: 700;
        }
        body.mode-satellite .map-point-label {
            color: #fff;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.95), 0 0 4px rgba(0, 0, 0, 0.8);
        }
        body.mode-street .map-point-label {
            color: #1a1a1a;
            text-shadow: 0 0 4px rgba(255,255,255,1), 0 0 3px rgba(255,255,255,1);
        }
        /* ---------- FIM ESTILO 3 ---------- */

        /* Remove a setinha padrão do tooltip do Leaflet (vale p/ todos) */
        .map-point-label::before {
            display: none !important;
        }

        /* Esconde os nomes no zoom baixo (vale p/ todos) */
        body.hide-point-labels .map-point-label {
            display: none !important;
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

        
        // Mostra os nomes das estações só a partir do zoom 8 (mesmo limiar
        // usado nos labels de shapefile em geomap-panel.js). Abaixo disso,
        // a classe no body esconde todos os labels via CSS — barato mesmo
        // com muitos pontos, e não interfere no agrupamento dos clusters.
        var POINT_LABEL_MIN_ZOOM = 8;
        function updatePointLabelsVisibility() {
            var hide = map.getZoom() < POINT_LABEL_MIN_ZOOM;
            document.body.classList.toggle('hide-point-labels', hide);
        }
        map.on('zoomend', updatePointLabelsVisibility);
        updatePointLabelsVisibility();


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
        document.body.classList.add('mode-satellite');

        document.getElementById('btn-satellite').addEventListener('click', function() {
            map.removeLayer(activeBaseTile);
            tileSatellite.addTo(map);
            activeBaseTile = tileSatellite;
            this.classList.add('active');
            document.getElementById('btn-street').classList.remove('active');
            document.body.classList.remove('mode-street');
            document.body.classList.add('mode-satellite');
            bringGeoMapLayersToFront();
        });
        document.getElementById('btn-street').addEventListener('click', function() {
            map.removeLayer(activeBaseTile);
            tileStreet.addTo(map);
            activeBaseTile = tileStreet;
            this.classList.add('active');
            document.getElementById('btn-satellite').classList.remove('active');
            document.body.classList.remove('mode-satellite');
            document.body.classList.add('mode-street');
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
            }),
            'pocos_simah': L.markerClusterGroup({
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true
            }),

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
                },
                'pocos_simah': {
                    radius: 5,
                    color: '#165B9C',
                    fillColor: '#fff',
                    weight: 6,
                    opacity: 1,
                    fillOpacity: 0.7
                },
            };

            return styles[source] || styles['hidroweb_qualidade_agua'];
        }

        // Função global para criar marcadores (usada pelo layer-control.js)
        function createCustomIcon(source) {
            const iconPaths = {
                'cnarh': '/images/icons/CNARH_Outorgas.svg',
                'hidroweb_qualidade_agua': '/images/icons/HidroWeb_Qualidade.svg',
                'hidroweb_telemetria': '/images/icons/HidroWeb_telemetria.svg',
                'hidroweb_telemetria_com_previsao': '/images/icons/PREVISAO_VAZAO.svg',
                'lrgs_client': '/images/icons/estacoes_AIBA.svg',
                'pocos_rimas': '/images/icons/Pocos_SIAGAS_e_RIMAS.svg',
                'pocos_siagas': '/images/icons/Pocos_SIAGAS_e_RIMAS.svg',
                'pocos_simah': '/images/icons/Pocos_AIBA.svg'
            };
            
            const iconPath = iconPaths[source];
            
            // Criar ícone Leaflet a partir do SVG
            return L.icon({
                iconUrl: iconPath,
                iconSize: [25, 25],  // Ajuste o tamanho conforme necessário
                iconAnchor: [12, 12], // Ponto de ancoragem (centro do ícone)
                popupAnchor: [0, -12], // Onde o popup vai aparecer
                className: 'custom-svg-marker'
            });
        }

        // Função global para criar marcadores (usada pelo layer-control.js)
        window.createMarker = function(station) {
            // Criar marcador com ícone SVG personalizado
            var marker = L.marker([station.latitude, station.longitude], {
                icon: createCustomIcon(station.source)
            });

            // Criar popup com botão para ver leituras/dados
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
                        <img class="layer-icon" src="/images/icons/Pocos_SIAGAS_e_RIMAS.svg" alt="Rimas" /> Ver Leituras (Últimas 50)
                    </button>
                `;
            }

            if (station.source === 'pocos_siagas') {
                popupContent += `
                    <button onclick="openSiagasReadingsModal('${station.code}', '${station.name}', '${station.latitude}', '${station.longitude}')" 
                            class="popup-button siagas">
                        <img class="layer-icon" src="/images/icons/Pocos_SIAGAS_e_RIMAS.svg" alt="SIAGAS" /> Ver Dados do Poço
                    </button>
                `;
            }

            if (station.source === 'hidroweb_qualidade_agua') {
                popupContent += `
                    <button onclick="openHidrowebQaReadingsModal('${station.code}', '${station.name}', '${station.latitude}', '${station.longitude}')" 
                            class="popup-button hidroweb-qa">
                        <img class="layer-icon" src="/images/icons/HidroWeb_Qualidade.svg" alt="Qualidade da Água" /> Ver Leituras (Últimas 50)
                    </button>
                `;
            }

            if (station.source === 'lrgs_client') {
                popupContent += `
                    <button onclick="openLrgsReadingsModal('${station.code}', '${station.name}', '${station.latitude}', '${station.longitude}')" 
                            class="popup-button lrgs">
                        <img class="layer-icon" src="/images/icons/estacoes_AIBA.svg" alt="Estações AIBA" /> Ver Leituras (Últimas 72)
                    </button>
                `;
            }
            if (station.source === 'hidroweb_telemetria') {
                popupContent += `
                    <button onclick="openHidrowebTelemetryDataModal('${station.code}', '${station.name}', '${station.latitude}', '${station.longitude}')" 
                            class="popup-button">
                        <img class="layer-icon" src="/images/icons/HidroWeb_telemetria.svg" alt="Telemetria" /> Ver Dados de Telemetria
                    </button>
                `;
            }

            if (station.source === 'hidroweb_telemetria_com_previsao') {
                popupContent += `
                    <button onclick="openHidrowebTelemetryDataModal('${station.code}', '${station.name}', '${station.latitude}', '${station.longitude}')" 
                            class="popup-button">
                        <img class="layer-icon" src="/images/icons/PREVISAO_VAZAO.svg" alt="Previsão de Vazão" /> Ver Previsão de Vazão
                    </button>
                `;
            }

            if (station.source === 'cnarh') {
                popupContent += `
                    <button onclick="openCnarhReadingsModal('${station.code}', '${station.name}', '${station.latitude}', '${station.longitude}')" 
                            class="popup-button cnarh">
                        <img class="layer-icon" src="/images/icons/CNARH_Outorgas.svg" alt="CNARH" /> Ver Dados CNARH
                    </button>
                `;
            }

            if (station.source === 'pocos_simah') {
                popupContent += `
                    <button onclick="openSimahReadingsModal('${station.code}', '${station.name}')" 
                            class="popup-button simah">
                        <img class="layer-icon" src="/images/icons/Pocos_AIBA.svg" alt="SIMAH" /> Ver Leituras (Últimas 50)
                    </button>
                `;
            }

            popupContent += `
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);

            // Nome da estação fixo ao lado do ponto. permanent:true faz o
            // MarkerCluster esconder o label enquanto o ponto está agrupado
            // e reexibi-lo quando ele se separa do cluster.
            if (station.name) {
                marker.bindTooltip(station.name, {
                    permanent: true,
                    direction: 'top',
                    offset: [0, -6],
                    className: 'map-point-label'
                });
            }

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
    @include('partials.simah-readings-modal')
</body>

</html>
