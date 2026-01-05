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
        
        // Adicionar o tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var layerGroups = {
            'cnarh': L.layerGroup(),
            'hidroweb_qualidade_agua': L.layerGroup(),
            'hidroweb_telemetria': L.layerGroup(),
            'lrgs_client': L.layerGroup(),
            'pocos_rimas': L.layerGroup(),
            'pocos_siagas': L.layerGroup()
        }
        
        // Função para retornar o estilo do marcador baseado no source
        function getMarkerStyle(source) {
            var styles = {
                'cnarh': {
                    radius: 5,
                    fillColor: "#A47864",
                    color: "#fff",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.7
                },
                'hidroweb_qualidade_agua': {
                    radius: 5,
                    fillColor: "#3388ff",
                    color: "#fff",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.7
                },
                'hidroweb_telemetria': {
                    radius: 5,
                    fillColor: "#00cc66",
                    color: "#fff",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.7
                },
                'lrgs_client': {
                    radius: 5,
                    fillColor: "#ff7800",
                    color: "#fff",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.7
                },
                'pocos_rimas': {
                    radius: 5,
                    fillColor: "#ff0000",
                    color: "#fff",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.7
                },
                'pocos_siagas': {
                    radius: 5,
                    fillColor: "#e16ccfff",
                    color: "#fff",
                    weight: 1,
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
                        
                        // Adicionar ao layer group específico ao invés do mapa direto
                        if (layerGroups[station.source]) {
                            layerGroups[station.source].addLayer(marker);
                        }

                        // Criar popup com botão para ver leituras (só para RIMAS)
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
                                'style="background-color: #ff0000; color: white; border: none; padding: 8px 16px; ' +
                                'cursor: pointer; border-radius: 4px; font-weight: bold; width: 100%;">' +
                                '📊 Ver Leituras (Últimas 50)' +
                                '</button>';
                        }

                        // Se for poço SIAGAS, adicionar botão para ver dados
                        if (station.source === 'pocos_siagas') {
                            popupContent += '<br><br>' +
                                '<button onclick="openSiagasReadingsModal(\'' + station.code + '\', \'' + station.name + '\', \'' + station.latitude + '\', \'' + station.longitude + '\')" ' +
                                'style="background-color: #e16ccfff; color: white; border: none; padding: 8px 16px; ' +
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
                                'style="background-color: #ff7800; color: white; border: none; padding: 8px 16px; ' +
                                'cursor: pointer; border-radius: 4px; font-weight: bold; width: 100%;">' +
                                '📊 Ver Leituras (Últimas 50)</button>';
                        }

                        // Se for HidroWeb Telemetria, adicionar botão para ver leituras
                        if (station.source === 'hidroweb_telemetria') {
                            popupContent += '<br><br>' +
                                '<button onclick="openHidrowebTelemetryReadingsModal(\'' + station.code + '\', \'' + station.name + '\', \'' + station.latitude + '\', \'' + station.longitude + '\')" ' +
                                'style="background-color: #00cc66; color: white; border: none; padding: 8px 16px; ' +
                                'cursor: pointer; border-radius: 4px; font-weight: bold; width: 100%;">' +
                                '📊 Ver Leituras (Últimas 50)</button>';
                        }

                        // Se for CNARH, adicionar botão para ver dados
                        if (station.source === 'cnarh') {
                            popupContent += '<br><br>' +
                                '<button onclick="openCnarhReadingsModal(\'' + station.code + '\', \'' + station.name + '\', \'' + station.latitude + '\', \'' + station.longitude + '\')" ' +
                                'style="background-color: #A47864; color: white; border: none; padding: 8px 16px; ' +
                                'cursor: pointer; border-radius: 4px; font-weight: bold; width: 100%;">' +
                                '📊 Ver Dados CNARH</button>';
                        }


                        marker.bindPopup(popupContent);
                        
                        bounds.push([station.latitude, station.longitude]);
                    }
                });

                // Adicionar todos os layer groups ao mapa por padrão
                Object.values(layerGroups).forEach(function(layer) {
                    layer.addTo(map);
                });

                // Criar controle de camadas com legendas coloridas
                var overlayMaps = {
                    '<span style="color: #A47864;">●</span> CNARH': layerGroups['cnarh'],
                    '<span style="color: #3388ff;">●</span> HidroWeb - Qualidade da Água': layerGroups['hidroweb_qualidade_agua'],
                    '<span style="color: #00cc66;">●</span> HidroWeb - Telemetria': layerGroups['hidroweb_telemetria'],
                    '<span style="color: #ff7800;">●</span> LRGS Client (DCP)': layerGroups['lrgs_client'],
                    '<span style="color: #ff0000;">●</span> Poços RIMAS': layerGroups['pocos_rimas'],
                    '<span style="color: #e16ccfff;">●</span> Poços SIAGAS': layerGroups['pocos_siagas']
                };

                /* // Adicionar controle ao mapa
                L.control.layers(null, overlayMaps, {
                    collapsed: false,
                    position: 'topright'
                }).addTo(map); */
                
                // Ajustar zoom para mostrar todos os pontos MAS com mais zoom
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
    

    <!-- Modal HTML -->
    @include('partials.rimas-readings-modal')
    @include('partials.siagas-readings-modal')
    @include('partials.hidroweb-qa-readings-modal')
    @include('partials.lrgs-readings-modal')
    @include('partials.hidroweb-telemetry-readings-modal')
    @include('partials.cnarh-readings-modal')
</body>
</html>