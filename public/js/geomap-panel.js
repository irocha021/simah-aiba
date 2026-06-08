/**
 * GEOMAP Panel - Gerenciamento de camadas do mapa
 *
 * Funções:
 * - Toggle do painel (expandir/colapsar)
 * - Popular lista de camadas
 * - Drag and drop para reordenar
 * - Controle de z-index das camadas
 * - Toggle de visibilidade das camadas
 * - Painel de legenda
 */

// Variáveis globais (serão setadas pelo dashboard)
var mapLayersData = window.mapLayersData || [];
var geoMapLayers = window.geoMapLayers || {};
var map = window.map || null;

// ========== TOGGLE DO PAINEL ==========
function toggleGeomapPanel() {
    var content = document.getElementById('geomapPanelContent');
    var icon = document.getElementById('geomapToggleIcon');
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.innerHTML = `
           <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px" fill="#FFFFFF"><path d="M480-528 324-372q-11 11-28 11t-28-11q-11-11-11-28t11-28l184-184q12-12 28-12t28 12l184 184q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-528Z"/></svg>
        `;
    } else {
        content.style.display = 'none';
        icon.innerHTML = `
           <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px" fill="#FFFFFF"><path d="M465-363.5q-7-2.5-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5q-8 0-15-2.5Z"/></svg>
        `;
    }
}

// ========== TRAZER CAMADAS GEOMAP PARA FRENTE APÓS TROCA DE BASEMAP ==========
function bringGeoMapLayersToFront() {
    Object.keys(geoMapLayers).forEach(function (slug) {
        var layerObj = geoMapLayers[slug];
        if (layerObj && layerObj.layer && map.hasLayer(layerObj.layer)) {
            layerObj.layer.bringToFront();
        }
    });
}

// ========== POPULAR O PAINEL ==========
function populateGeomapPanel() {
    var container = document.getElementById('geomapPanelContent');
    if (!container) return;

    container.innerHTML = '';

    mapLayersData.forEach(function (layerData) {
        var item = document.createElement('div');
        item.className = 'geomap-layer-item';
        item.setAttribute('draggable', 'true');
        item.setAttribute('data-slug', layerData.slug);

        // Ícone de arrastar
        var dragHandle = document.createElement('span');
        dragHandle.className = 'drag-handle';
        dragHandle.innerHTML = '≡';

        // Checkbox
        var checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = 'layer-' + layerData.slug;
        checkbox.onchange = function () {
            toggleLayer(layerData.slug, this.checked);
        };

        // Nome da camada
        var name = document.createElement('span');
        name.className = 'layer-name';
        name.textContent = layerData.name;

        item.appendChild(dragHandle);
        item.appendChild(checkbox);
        item.appendChild(name);

        // Ícone de info (só se tiver legenda)
        if (layerData.has_legend && layerData.legends && layerData.legends.length > 0) {
            var infoIcon = document.createElement('span');
            infoIcon.className = 'layer-info';
            infoIcon.innerHTML = 'ℹ';
            infoIcon.title = 'Ver legenda';
            infoIcon.onclick = function (e) {
                e.stopPropagation();
                showLegendPanel(layerData);
            };
            item.appendChild(infoIcon);
        }

        container.appendChild(item);
    });
}

// ========== DRAG AND DROP ==========
var draggedItem = null;

function initDragAndDrop() {
    var container = document.getElementById('geomapPanelContent');
    if (!container) return;

    container.addEventListener('dragstart', function (e) {
        if (e.target.classList.contains('geomap-layer-item')) {
            draggedItem = e.target;
            e.target.classList.add('dragging');
        }
    });

    container.addEventListener('dragend', function (e) {
        if (e.target.classList.contains('geomap-layer-item')) {
            e.target.classList.remove('dragging');
            draggedItem = null;
            updateLayersZIndex();
        }
    });

    container.addEventListener('dragover', function (e) {
        e.preventDefault();
        var afterElement = getDragAfterElement(container, e.clientY);
        if (draggedItem) {
            if (afterElement == null) {
                container.appendChild(draggedItem);
            } else {
                container.insertBefore(draggedItem, afterElement);
            }
        }
    });
}

function getDragAfterElement(container, y) {
    var draggableElements = [...container.querySelectorAll('.geomap-layer-item:not(.dragging)')];

    return draggableElements.reduce(function (closest, child) {
        var box = child.getBoundingClientRect();
        var offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// ========== Z-INDEX DAS CAMADAS ==========
function updateLayersZIndex() {
    var items = document.querySelectorAll('.geomap-layer-item');
    var totalItems = items.length;

    items.forEach(function (item, index) {
        var slug = item.getAttribute('data-slug');
        var layerObj = geoMapLayers[slug];

        if (layerObj && layerObj.layer) {
            // Primeiro da lista = maior z-index (fica por cima)
            var zIndex = (totalItems - index) * 100;

            if (layerObj.layer.setZIndex) {
                layerObj.layer.setZIndex(zIndex);
            }
            // Para layerGroups (GeoJSON), precisa setar em cada layer interno
            if (layerObj.layer.eachLayer) {
                layerObj.layer.eachLayer(function (subLayer) {
                    if (subLayer.setZIndex) {
                        subLayer.setZIndex(zIndex);
                    }
                });
            }
        }
    });

    console.log('Z-index atualizado pela ordem do painel');
}

// ========== OPACIDADE GLOBAL DAS CAMADAS ==========
function setAllLayersOpacity(opacity) {
    Object.keys(geoMapLayers).forEach(function (slug) {
        var layerObj = geoMapLayers[slug];
        if (!layerObj || !layerObj.layer) return;
        var layer = layerObj.layer;

        if (layer.setOpacity) {
            layer.setOpacity(opacity);
        }
        if (layer.eachLayer) {
            layer.eachLayer(function (sub) {
                if (sub.setStyle) {
                    sub.setStyle({ opacity: opacity, fillOpacity: opacity });
                }
            });
        }
    });
    
    // ===== Aplica também nas camadas de drenagem =====
    if (window.drainageByCode) {
        Object.keys(window.drainageByCode).forEach(function (code) {
            var drainageLayer = window.drainageByCode[code];
            if (drainageLayer) {
                // Se for um LayerGroup ou FeatureGroup
                if (drainageLayer.eachLayer) {
                    drainageLayer.eachLayer(function (subLayer) {
                        if (subLayer.setStyle) {
                            // Para polígonos/linhas (GeoJSON)
                            subLayer.setStyle({ 
                                opacity: opacity, 
                                fillOpacity: opacity
                            });
                        }
                        if (subLayer.setOpacity) {
                            // Para tile layers
                            subLayer.setOpacity(opacity);
                        }
                    });
                }
                // Se for uma layer direta (não um grupo)
                else if (drainageLayer.setStyle) {
                    drainageLayer.setStyle({ 
                        opacity: opacity, 
                        fillOpacity: opacity 
                    });
                }
                else if (drainageLayer.setOpacity) {
                    drainageLayer.setOpacity(opacity);
                }
            }
        });
    }
}


// ========== TOGGLE DE CAMADAS ==========
function toggleLayer(slug, visible) {
    var layerObj = geoMapLayers[slug];
    if (layerObj && layerObj.layer && map) {
        if (visible) {
            layerObj.layer.addTo(map);
        } else {
            map.removeLayer(layerObj.layer);
        }
    }
}

// ========== PAINEL DE LEGENDA ==========
function showLegendPanel(layerData) {
    var panel = document.getElementById('legendPanel');
    var title = document.getElementById('legendPanelTitle');
    var content = document.getElementById('legendPanelContent');

    if (!panel || !title || !content) return;

    // Setar título
    title.textContent = 'Legenda';

    // Popular conteúdo com as cores da legenda
    content.innerHTML = '';

    if (layerData.legends && layerData.legends.length > 0) {
        layerData.legends.forEach(function (legend) {
            var item = document.createElement('div');
            item.className = 'legend-item';

            var colorBox = document.createElement('div');
            colorBox.className = 'legend-color';
            colorBox.style.backgroundColor = legend.color_hex;

            var label = document.createElement('span');
            label.className = 'legend-label';
            label.textContent = legend.label;

            item.appendChild(colorBox);
            item.appendChild(label);
            content.appendChild(item);
        });
    } else {
        content.innerHTML = '<p>Nenhuma legenda disponível.</p>';
    }

    // Mostrar painel
    panel.classList.add('active');
}

function closeLegendPanel() {
    var panel = document.getElementById('legendPanel');
    if (panel) {
        panel.classList.remove('active');
    }
}

// ========== CRIAR LAYER A PARTIR DOS DADOS ==========
function createLayerFromData(layerData) {
    if (layerData.type === 'tile') {
        var tileLayer = L.tileLayer(layerData.url_pattern, {
            attribution: layerData.attribution || '',
            maxZoom: 18,
            maxNativeZoom: layerData.max_zoom,
            minZoom: layerData.min_zoom,
            opacity: layerData.opacity,
            tms: layerData.tms,
        });

        // Camadas com label_field têm um GeoJSON paralelo de centroides+nomes
        // servido em /tiles/{slug}/labels.geojson. Carregamos e criamos um grupo
        // de tooltips permanentes que aparecem só a partir do zoom mínimo de label.
        if (layerData.label_field) {
            var labelsGroup = L.layerGroup();
            var labelsUrl = '/tiles/' + layerData.slug + '/labels.geojson';
            // Zoom mínimo configurável por camada (label_min_zoom no banco).
            // Default 8 mantém o comportamento original p/ camadas antigas.
            var labelMinZoom = layerData.label_min_zoom || 8;

            // Labels só aparecem se: (a) tileLayer estiver ATIVO no mapa E
            // (b) zoom atual >= labelMinZoom. Recalcula em zoom/add/remove.
            function updateLabelsVisibility() {
                var shouldShow = map.hasLayer(tileLayer) && map.getZoom() >= labelMinZoom;
                if (shouldShow && !map.hasLayer(labelsGroup)) map.addLayer(labelsGroup);
                if (!shouldShow && map.hasLayer(labelsGroup)) map.removeLayer(labelsGroup);
            }

            fetch(labelsUrl)
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    data.features.forEach(function (feature) {
                        var coords = feature.geometry.coordinates;
                        var name = feature.properties.name;
                        if (!name) return;
                        labelsGroup.addLayer(L.marker([coords[1], coords[0]], {
                            icon: L.divIcon({ className: 'map-label', html: name, iconSize: null }),
                            interactive: false
                        }));
                    });

                    // Re-avalia em mudança de zoom OU quando a camada tile é
                    // adicionada/removida do mapa (toggle da checkbox).
                    map.on('zoomend', updateLabelsVisibility);
                    tileLayer.on('add', updateLabelsVisibility);
                    tileLayer.on('remove', updateLabelsVisibility);

                    updateLabelsVisibility(); // aplica no load
                })
                .catch(function (err) {
                    console.warn('Falha ao carregar labels para', layerData.slug, err);
                });

        }

        return tileLayer;

    } else if (layerData.type === 'geojson') {

        var layer = L.layerGroup();
        fetch(layerData.url_pattern)
            .then(response => response.json())
            .then(data => {
                L.geoJSON(data, {
                    pointToLayer: function (feature, latlng) {
                        return L.circleMarker(latlng, {
                            radius: layerData.marker_radius || 6,
                            fillColor: layerData.marker_color || "#ff6600",
                            color: "#fff",
                            weight: 2,
                            opacity: 1,
                            fillOpacity: layerData.opacity || 0.8
                        });
                    },
                    onEachFeature: function (feature, layer) {
                        if (feature.properties) {
                            var popupContent = '<b>' + layerData.name + '</b><br>';
                            for (var key in feature.properties) {
                                popupContent += key + ': ' + feature.properties[key] + '<br>';
                            }
                            layer.bindPopup(popupContent);
                        }
                    }
                }).addTo(layer);
            });
        return layer;
    }
}

// ========== INICIALIZAÇÃO ==========
function initGeoMapLayers(layersData) {
    mapLayersData = layersData;
    window.mapLayersData = layersData;

    // Criar objeto para armazenar os layers
    geoMapLayers = {};
    layersData.forEach(function (layerData) {
        geoMapLayers[layerData.slug] = {
            layer: createLayerFromData(layerData),
            data: layerData
        };
    });
    window.geoMapLayers = geoMapLayers;

    console.log('GeoMap Layers criados:', geoMapLayers);

    // Popular o painel
    populateGeomapPanel();

    // Inicializar drag-and-drop
    initDragAndDrop();

    return geoMapLayers;
}

// Função para setar referência do mapa
function setMapReference(mapInstance) {
    map = mapInstance;
    window.map = mapInstance;
}
