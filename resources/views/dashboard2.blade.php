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
        body { margin: 0; padding: 0; }
        #map { width: 100%; height: 100vh; }
    </style>
</head>
<body>
    <div id="map"></div>

    {{-- Side Menu --}}
    @include('components.side-menu')
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    
    <script>
        // Criar o mapa
        var map = L.map('map').setView([-12.5, -41.5], 8);
        map.zoomControl.setPosition('bottomright');
        
        // Adicionar o tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Criar MarkerClusterGroups para cada tipo
        var clusterGroups = {
            'cnarh': L.markerClusterGroup({ maxClusterRadius: 50, spiderfyOnMaxZoom: true, showCoverageOnHover: false, zoomToBoundsOnClick: true }),
            'hidroweb_qualidade_agua': L.markerClusterGroup({ maxClusterRadius: 50, spiderfyOnMaxZoom: true, showCoverageOnHover: false, zoomToBoundsOnClick: true }),
            'hidroweb_telemetria': L.markerClusterGroup({ maxClusterRadius: 50, spiderfyOnMaxZoom: true, showCoverageOnHover: false, zoomToBoundsOnClick: true }),
            'hidroweb_telemetria_com_previsao': L.markerClusterGroup({ maxClusterRadius: 50, spiderfyOnMaxZoom: true, showCoverageOnHover: false, zoomToBoundsOnClick: true }),
            'lrgs_client': L.markerClusterGroup({ maxClusterRadius: 50, spiderfyOnMaxZoom: true, showCoverageOnHover: false, zoomToBoundsOnClick: true }),
            'pocos_rimas': L.markerClusterGroup({ maxClusterRadius: 50, spiderfyOnMaxZoom: true, showCoverageOnHover: false, zoomToBoundsOnClick: true }),
            'pocos_siagas': L.markerClusterGroup({ maxClusterRadius: 50, spiderfyOnMaxZoom: true, showCoverageOnHover: false, zoomToBoundsOnClick: true })
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
                    fillColor: "#9933ff",
                    color: "#fff",
                    weight: 1,
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
       
        // Buscar os dados da API
        fetch('/api/stations')
            .then(response => response.json())
            .then(data => {
                console.log('Total de estações:', data.data.stations.length);
                
                var stations = data.data.stations;
                var bounds = [];
                
                stations.forEach(function(station) {
                    if (station.latitude && station.longitude) {
                        var style = getMarkerStyle(station.source);
                        var marker = L.circleMarker([station.latitude, station.longitude], style);
                        
                        // Adicionar ao cluster group específico ao invés de um único cluster
                        if (clusterGroups[station.source]) {
                            clusterGroups[station.source].addLayer(marker);
                        }

                        // Criar popup com botão para ver leituras/dados
                        var popupContent =
                            '<b>' + station.name + '</b><br>' +
                            'Código: ' + station.code + '<br>' +
                            'Lat: ' + station.latitude + '<br>' +
                            'Lng: ' + station.longitude + '<br>' +
                            'Fonte: ' + station.source;

                        // Se for poço RIMAS, adicionar botão para ver leituras
                        if (station.source === 'pocos_rimas') {
                            popupContent += '<br><br>' +
                                '<button onclick="openRimasReadingsModal(\'' + station.code + '\', \'' + station.name + '\', \'' + station.latitude + '\', \'' + station.longitude + '\')" ' +
                                'style="background-color: #3388ff; color: white; border: none; padding: 8px 16px; ' +
                                'cursor: pointer; border-radius: 4px; font-weight: bold; width: 100%;">' +
                                '📊 Ver Leituras (Últimas 50)</button>';
                        }

                        // Se for poço SIAGAS, adicionar botão para ver dados
                        if (station.source === 'pocos_siagas') {
                            popupContent += '<br><br>' +
                                '<button onclick="openSiagasReadingsModal(\'' + station.code + '\', \'' + station.name + '\', \'' + station.latitude + '\', \'' + station.longitude + '\')" ' +
                                'style="background-color: #3388ff; color: white; border: none; padding: 8px 16px; ' +
                                'cursor: pointer; border-radius: 4px; font-weight: bold; width: 100%;">' +
                                '📊 Ver Dados do Poço</button>';
                        }

                        // Se for HidroWeb Qualidade da Água, adicionar botão para ver leituras
                        if (station.source === 'hidroweb_qualidade_agua') {
                            popupContent += '<br><br>' +
                                '<button onclick="openHidrowebQaReadingsModal(\'' + station.code + '\', \'' + station.name + '\', \'' + station.latitude + '\', \'' + station.longitude + '\')" ' +
                                'style="background-color: #3388ff; color: white; border: none; padding: 8px 16px; ' +
                                'cursor: pointer; border-radius: 4px; font-weight: bold; width: 100%;">' +
                                '📊 Ver Leituras (Últimas 50)</button>';
                        }

                        if (station.source === 'lrgs_client') {
                            popupContent += '<br><br>' +
                                '<button onclick="openLrgsReadingsModal(\'' + station.code + '\', \'' + station.name + '\', \'' + station.latitude + '\', \'' + station.longitude + '\')" ' +
                                'style="background-color: #3388ff; color: white; border: none; padding: 8px 16px; ' +
                                'cursor: pointer; border-radius: 4px; font-weight: bold; width: 100%;">' +
                                '📊 Ver Leituras (Últimas 50)</button>';
                        }

                        // Se for HidroWeb Telemetria, adicionar botão para ver leituras
                        // Se for HidroWeb Telemetria OU Telemetria com Previsão
                        if (station.source === 'hidroweb_telemetria' || station.source === 'hidroweb_telemetria_com_previsao') {
                            var buttonColor = station.source === 'hidroweb_telemetria_com_previsao' ? '#9933ff' : '#00cc66';
                            popupContent += '<br><br>' +
                                '<button onclick="openHidrowebTelemetryDataModal(\'' + station.code + '\', \'' + station.name + '\', \'' + station.latitude + '\', \'' + station.longitude + '\')" ' +
                                'style="background-color: ' + buttonColor + '; color: white; border: none; padding: 8px 16px; ' +
                                'cursor: pointer; border-radius: 4px; font-weight: bold; width: 100%;">' +
                                '📊 Ver Dados</button>';
                        }

                        // Se for CNARH, adicionar botão para ver dados
                        if (station.source === 'cnarh') {
                            popupContent += '<br><br>' +
                                '<button onclick="openCnarhReadingsModal(\'' + station.code + '\', \'' + station.name + '\', \'' + station.latitude + '\', \'' + station.longitude + '\')" ' +
                                'style="background-color: #3388ff; color: white; border: none; padding: 8px 16px; ' +
                                'cursor: pointer; border-radius: 4px; font-weight: bold; width: 100%;">' +
                                '📊 Ver Dados CNARH</button>';
                        }

                        marker.bindPopup(popupContent);
                        
                        bounds.push([station.latitude, station.longitude]);
                    }
                });

                // Adicionar todos os cluster groups ao mapa por padrão
                Object.values(clusterGroups).forEach(function(cluster) {
                    cluster.addTo(map);
                });

                // Criar controle de camadas com legendas coloridas
                var overlayMaps = {
                    '<span style="color: #A47864;">●</span> CNARH': clusterGroups['cnarh'],
                    '<span style="color: #3388ff;">●</span> HidroWeb - Qualidade da Água': clusterGroups['hidroweb_qualidade_agua'],
                    '<span style="color: #00cc66;">●</span> HidroWeb - Telemetria': clusterGroups['hidroweb_telemetria'],
                    '<span style="color: #9933ff;">●</span> HidroWeb - Telemetria c/ Previsão': clusterGroups['hidroweb_telemetria_com_previsao'],
                    '<span style="color: #ff7800;">●</span> LRGS Client (DCP)': clusterGroups['lrgs_client'],
                    '<span style="color: #ff0000;">●</span> Poços RIMAS': clusterGroups['pocos_rimas'],
                    '<span style="color: #e16ccfff;">●</span> Poços SIAGAS': clusterGroups['pocos_siagas']
                };

                /* // Adicionar controle ao mapa
                L.control.layers(null, overlayMaps, {
                    collapsed: true,
                    position: 'topright'
                }).addTo(map); */
                
                // Ajustar zoom para mostrar todos os pontos
                if (bounds.length > 0) {
                    map.fitBounds(bounds, { 
                        padding: [30, 30],  
                        maxZoom: 12  
                    });
                }
                
                console.log('Total de marcadores adicionados:', bounds.length);
            })
            .catch(error => {
                console.error('Erro ao buscar estações:', error);
            });
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