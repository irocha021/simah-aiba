(function () {
    const DATA_BASE = '/fire/';
    const FILES = {
        realtime: 'focos_oeste_ba.json',
        panorama: 'panorama_fogo_oeste_ba.json',
        municipalities: 'limites_municipios_oeste.json',
        conservation: 'unidades_conservacao_oeste.geojson',
        app: 'imovel_rural_app_oeste.geojson',
        reserva: 'imovel_rural_reserva_legal_oeste.geojson',
        wind: 'vento_oeste_ba.json'
    };

    const VIEW_CONFIG = {
        realtime: {
            title: 'Monitoramento tempo real',
            status: 'Focos ativos detectados',
            dataset: 'realtime',
            defaultTimeWindow: 'today'
        },
        panorama: {
            title: 'Panorama do fogo',
            status: 'Historico de focos no periodo',
            dataset: 'panorama',
            defaultTimeWindow: 'all'
        },
        wind: {
            title: 'Ventos e fogo',
            status: 'Focos com dispersao estimada por vento',
            dataset: 'realtime',
            defaultTimeWindow: 'today'
        },
        risk: {
            title: 'Areas de risco',
            status: 'Camadas de risco e exposicao',
            dataset: 'panorama',
            defaultTimeWindow: 'all'
        }
    };

    const WIND_POINTS = [
        { name: 'Barreiras', lat: -12.15, lon: -44.99 },
        { name: 'Luis Eduardo Magalhaes', lat: -12.09, lon: -45.78 },
        { name: 'Sao Desiderio', lat: -12.36, lon: -44.97 },
        { name: 'Correntina', lat: -13.35, lon: -44.64 },
        { name: 'Cocos', lat: -14.18, lon: -44.54 },
        { name: 'Santa Rita de Cassia', lat: -11.01, lon: -44.52 }
    ];

    const state = {
        active: false,
        view: 'realtime',
        display: 'general',
        timeWindow: 'today',
        satellite: 'all',
        municipality: 'all',
        riskType: 'focus',
        loaded: false,
        focusedOnce: false,
        data: {
            realtime: null,
            panorama: null,
            municipalities: null,
            conservation: null,
            app: null,
            reserva: null,
            wind: null
        }
    };

    const refs = {};
    const layers = {
        focus: null,
        front: null,
        wind: null,
        risk: null,
        municipalities: null,
        conservation: null
    };

    function qs(selector) {
        return document.querySelector(selector);
    }

    function qsa(selector) {
        return Array.from(document.querySelectorAll(selector));
    }

    function getMap() {
        return window.map || (typeof map !== 'undefined' ? map : null);
    }

    function normalize(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toUpperCase()
            .trim();
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function parseDate(value) {
        if (!value) return null;
        const normalized = String(value).replace(' ', 'T');
        const date = new Date(normalized.length <= 19 ? normalized + '-03:00' : normalized);
        return Number.isNaN(date.getTime()) ? null : date;
    }

    function formatDate(value) {
        const date = value instanceof Date ? value : parseDate(value);
        if (!date) return '--';
        return date.toLocaleString('pt-BR', {
            timeZone: 'America/Sao_Paulo',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function getFeatureDate(feature) {
        return parseDate(feature?.properties?.data_hora || feature?.properties?.datetime || feature?.properties?.date);
    }

    function getDatasetKey() {
        return VIEW_CONFIG[state.view].dataset;
    }

    function getCurrentFeatures() {
        const dataset = state.data[getDatasetKey()];
        return Array.isArray(dataset?.features) ? dataset.features : [];
    }

    function getReferenceDate(features) {
        let maxDate = null;
        features.forEach(function (feature) {
            const date = getFeatureDate(feature);
            if (date && (!maxDate || date > maxDate)) maxDate = date;
        });
        return maxDate || new Date();
    }

    function isInsideTimeWindow(feature, referenceDate) {
        const date = getFeatureDate(feature);
        if (!date) return state.timeWindow === 'all';
        if (state.timeWindow === 'all') return true;

        if (state.timeWindow === 'today') {
            return date.toLocaleDateString('pt-BR', { timeZone: 'America/Sao_Paulo' }) ===
                referenceDate.toLocaleDateString('pt-BR', { timeZone: 'America/Sao_Paulo' });
        }

        const hours = Number(state.timeWindow.replace('h', ''));
        if (!Number.isFinite(hours)) return true;
        return date >= new Date(referenceDate.getTime() - hours * 60 * 60 * 1000);
    }

    function filterFeatures() {
        const features = getCurrentFeatures();
        const referenceDate = getReferenceDate(features);

        return features.filter(function (feature) {
            const props = feature.properties || {};
            if (!feature.geometry || feature.geometry.type !== 'Point') return false;
            if (!isInsideTimeWindow(feature, referenceDate)) return false;
            if (state.municipality !== 'all' && normalize(props.municipio_normalizado || props.municipio) !== state.municipality) {
                return false;
            }
            if (state.display === 'satellite' && state.satellite !== 'all' && normalize(props.satelite) !== state.satellite) {
                return false;
            }
            return true;
        });
    }

    async function loadJson(file, bustCache) {
        const suffix = bustCache ? '?v=' + Date.now() : '';
        const response = await fetch(DATA_BASE + file + suffix);
        if (!response.ok) throw new Error('Falha ao carregar ' + file);
        return response.json();
    }

    async function ensureData(key) {
        if (state.data[key]) return state.data[key];
        state.data[key] = await loadJson(FILES[key], key === 'realtime');
        return state.data[key];
    }

    async function loadCoreData() {
        await Promise.all([
            ensureData('realtime'),
            ensureData('panorama'),
            ensureData('municipalities'),
            ensureData('conservation')
        ]);
        populateFilters();
        updateMenuCounters();
        state.loaded = true;
    }

    function initLayers() {
        if (layers.focus) return;
        const L = window.L;
        layers.focus = L.markerClusterGroup ? L.markerClusterGroup({
            disableClusteringAtZoom: 12,
            maxClusterRadius: 48,
            spiderfyOnMaxZoom: true
        }) : L.layerGroup();
        layers.front = L.layerGroup();
        layers.wind = L.layerGroup();
        layers.risk = L.layerGroup();
    }

    function clearFireLayers() {
        const mapRef = getMap();
        if (!mapRef) return;
        ['focus', 'front', 'wind', 'risk'].forEach(function (key) {
            const layer = layers[key];
            if (layer && mapRef.hasLayer(layer)) mapRef.removeLayer(layer);
            if (layer && layer.clearLayers) layer.clearLayers();
        });
        ['municipalities', 'conservation'].forEach(function (key) {
            const layer = layers[key];
            if (layer && mapRef.hasLayer(layer)) mapRef.removeLayer(layer);
        });
    }

    function addBaseFireLayers() {
        const mapRef = getMap();
        if (!mapRef || !window.L) return;

        if (!layers.municipalities && state.data.municipalities) {
            layers.municipalities = L.geoJSON(state.data.municipalities, {
                style: {
                    color: '#111',
                    weight: 1.2,
                    opacity: 0.75,
                    fillOpacity: 0
                }
            });
        }

        if (!layers.conservation && state.data.conservation) {
            layers.conservation = L.geoJSON(state.data.conservation, {
                style: {
                    color: '#1f6ea8',
                    weight: 1.6,
                    dashArray: '5 5',
                    opacity: 0.8,
                    fillColor: '#2f80d0',
                    fillOpacity: 0.08
                },
                onEachFeature: function (feature, layer) {
                    const props = feature.properties || {};
                    const name = props.nome || props.NOME || props.name || 'Unidade de conservacao';
                    layer.bindTooltip(escapeHtml(name), { sticky: true });
                }
            });
        }

        if (layers.municipalities && !mapRef.hasLayer(layers.municipalities)) layers.municipalities.addTo(mapRef);
        if (layers.conservation && !mapRef.hasLayer(layers.conservation)) layers.conservation.addTo(mapRef);
    }

    function satelliteColor(satellite) {
        const palette = ['#ff4545', '#ff8b2b', '#da315f', '#9b51e0', '#2f80d0', '#0b7f55'];
        const text = normalize(satellite);
        let hash = 0;
        for (let i = 0; i < text.length; i++) hash = (hash + text.charCodeAt(i) * (i + 1)) % palette.length;
        return palette[hash];
    }

    function createFocusMarker(feature) {
        const coords = feature.geometry.coordinates;
        const props = feature.properties || {};
        const frp = Number(props.frp || 0);
        const color = state.display === 'satellite' ? satelliteColor(props.satelite) : (frp >= 100 ? '#d91515' : '#ff4545');
        const size = frp >= 100 ? 30 : 24;

        const icon = L.divIcon({
            className: 'fire-focus-marker' + (frp >= 100 ? ' fire-focus-high' : ''),
            html: '<span style="width:' + size + 'px;height:' + size + 'px;background:' + color + ';">!</span>',
            iconSize: [size + 8, size + 8],
            iconAnchor: [(size + 8) / 2, (size + 8) / 2]
        });

        return L.marker([coords[1], coords[0]], { icon: icon }).bindPopup(renderPopup(feature));
    }

    function renderPopup(feature) {
        const props = feature.properties || {};
        return [
            '<div class="fire-popup">',
            '<h3>Foco de calor</h3>',
            '<p><strong>Municipio:</strong> ' + escapeHtml(props.municipio || '--') + '</p>',
            '<p><strong>Deteccao:</strong> ' + escapeHtml(formatDate(props.data_hora)) + '</p>',
            '<p><strong>Satelite:</strong> ' + escapeHtml(props.satelite || '--') + '</p>',
            '<p><strong>FRP:</strong> ' + escapeHtml(props.frp ?? '--') + '</p>',
            '<p><strong>Bioma:</strong> ' + escapeHtml(props.bioma || '--') + '</p>',
            '</div>'
        ].join('');
    }

    function renderFocusLayers(features) {
        const mapRef = getMap();
        if (!mapRef) return;

        layers.focus.clearLayers();
        features.forEach(function (feature) {
            layers.focus.addLayer(createFocusMarker(feature));
        });
        layers.focus.addTo(mapRef);

        if (state.view === 'panorama' || state.view === 'wind') {
            renderFireFronts(features);
        }
    }

    function renderFireFronts(features) {
        const mapRef = getMap();
        const groups = new Map();
        layers.front.clearLayers();

        features.forEach(function (feature) {
            const coords = feature.geometry.coordinates;
            const key = Math.round(coords[1] * 4) / 4 + ',' + Math.round(coords[0] * 4) / 4;
            if (!groups.has(key)) groups.set(key, []);
            groups.get(key).push(feature);
        });

        groups.forEach(function (group) {
            if (group.length < 4) return;
            const center = group.reduce(function (acc, feature) {
                acc.lat += feature.geometry.coordinates[1];
                acc.lon += feature.geometry.coordinates[0];
                return acc;
            }, { lat: 0, lon: 0 });
            center.lat = center.lat / group.length;
            center.lon = center.lon / group.length;
            L.circle([center.lat, center.lon], {
                radius: Math.min(18000, 3500 + group.length * 180),
                color: '#ff4545',
                weight: 2,
                dashArray: '8 8',
                fillColor: '#ff4545',
                fillOpacity: 0.08
            }).bindTooltip('Frente de fogo agrupada: ' + group.length + ' focos').addTo(layers.front);
        });

        if (!mapRef.hasLayer(layers.front)) layers.front.addTo(mapRef);
    }

    async function renderRiskLayers(features) {
        const mapRef = getMap();
        layers.risk.clearLayers();

        if (state.riskType === 'focus') {
            features.slice(0, 600).forEach(function (feature) {
                const coords = feature.geometry.coordinates;
                const frp = Number(feature.properties?.frp || 0);
                L.circle([coords[1], coords[0]], {
                    radius: frp >= 100 ? 9000 : 4500,
                    color: '#f3423f',
                    weight: 1,
                    fillColor: '#f3423f',
                    fillOpacity: frp >= 100 ? 0.18 : 0.1
                }).addTo(layers.risk);
            });
        } else {
            const key = state.riskType === 'uc' ? 'conservation' : state.riskType;
            await ensureData(key);
            L.geoJSON(state.data[key], {
                style: {
                    color: state.riskType === 'app' ? '#0b7f55' : state.riskType === 'reserva' ? '#8a5c35' : '#2f80d0',
                    weight: 1,
                    opacity: 0.8,
                    fillOpacity: 0.12
                },
                onEachFeature: function (feature, layer) {
                    const props = feature.properties || {};
                    layer.bindTooltip(escapeHtml(props.NM_MUN || props.nome || props.name || 'Area de risco'), { sticky: true });
                }
            }).addTo(layers.risk);
        }

        if (!mapRef.hasLayer(layers.risk)) layers.risk.addTo(mapRef);
    }

    async function ensureWindData() {
        if (state.data.wind) return state.data.wind;

        try {
            state.data.wind = await loadJson(FILES.wind, true);
            return state.data.wind;
        } catch (localError) {
            const results = await Promise.all(WIND_POINTS.map(async function (point) {
                const url = 'https://api.open-meteo.com/v1/forecast?latitude=' + point.lat +
                    '&longitude=' + point.lon + '&current=wind_speed_10m,wind_direction_10m&timezone=America%2FSao_Paulo';
                const response = await fetch(url);
                if (!response.ok) throw new Error('Open-Meteo indisponivel');
                const json = await response.json();
                return {
                    name: point.name,
                    lat: point.lat,
                    lon: point.lon,
                    speed: Number(json.current?.wind_speed_10m || 0),
                    direction: Number(json.current?.wind_direction_10m || 0)
                };
            }));
            state.data.wind = { points: results };
            return state.data.wind;
        }
    }

    function destinationByWind(lat, lon, direction, distanceKm) {
        const radians = direction * Math.PI / 180;
        const dLat = Math.cos(radians) * distanceKm / 111;
        const dLon = Math.sin(radians) * distanceKm / (111 * Math.cos(lat * Math.PI / 180));
        return [lat + dLat, lon + dLon];
    }

    async function renderWindLayers(features) {
        const mapRef = getMap();
        layers.wind.clearLayers();
        refs.windCard?.classList.add('active');

        try {
            const windData = await ensureWindData();
            const points = Array.isArray(windData.points) ? windData.points : Array.isArray(windData) ? windData : [];
            let totalSpeed = 0;
            let totalDirection = 0;

            points.forEach(function (point) {
                totalSpeed += Number(point.speed || point.velocidade || 0);
                totalDirection += Number(point.direction || point.direcao || 0);
                const speed = Number(point.speed || point.velocidade || 0);
                const direction = Number(point.direction || point.direcao || 0);
                const dest = destinationByWind(point.lat, point.lon, direction, Math.max(12, speed * 2));
                L.polyline([[point.lat, point.lon], dest], {
                    color: '#2f80d0',
                    weight: 3,
                    opacity: 0.8,
                    className: 'fire-wind-particle'
                }).bindTooltip(point.name + ' - ' + Math.round(speed) + ' km/h').addTo(layers.wind);
            });

            features.slice(0, 80).forEach(function (feature) {
                const coords = feature.geometry.coordinates;
                const point = points[0];
                if (!point) return;
                const dest = destinationByWind(coords[1], coords[0], Number(point.direction || 0), 10);
                L.polygon([[coords[1], coords[0]], destinationByWind(coords[1], coords[0], Number(point.direction || 0) - 18, 6), dest, destinationByWind(coords[1], coords[0], Number(point.direction || 0) + 18, 6)], {
                    color: '#ff7a45',
                    weight: 1,
                    fillColor: '#ff7a45',
                    fillOpacity: 0.14
                }).addTo(layers.wind);
            });

            const count = Math.max(points.length, 1);
            refs.windAvg.textContent = Math.round(totalSpeed / count) + ' km/h';
            refs.windDirection.textContent = Math.round(totalDirection / count) + ' graus';
            refs.windStatus.textContent = 'Estimativa exibida sobre os focos filtrados.';
            if (!mapRef.hasLayer(layers.wind)) layers.wind.addTo(mapRef);
        } catch (error) {
            refs.windStatus.textContent = 'Nao foi possivel carregar os dados de vento agora.';
        }
    }

    function updatePanelHeader() {
        const config = VIEW_CONFIG[state.view];
        refs.title.textContent = config.title;
        refs.status.textContent = config.status;
        const showRisk = state.view === 'risk';
        qsa('[data-fire-filter="risk"]').forEach(function (element) {
            element.style.display = showRisk ? '' : 'none';
        });
        const showSatellite = state.display === 'satellite';
        qsa('[data-fire-filter="satellite"]').forEach(function (element) {
            element.style.display = showSatellite ? '' : 'none';
        });
        refs.windCard?.classList.toggle('active', state.view === 'wind');
    }

    function updateSummary(features) {
        const municipalities = new Set();
        const satellites = new Set();
        let lastDate = null;

        features.forEach(function (feature) {
            const props = feature.properties || {};
            if (props.municipio_normalizado || props.municipio) municipalities.add(normalize(props.municipio_normalizado || props.municipio));
            if (props.satelite) satellites.add(normalize(props.satelite));
            const date = getFeatureDate(feature);
            if (date && (!lastDate || date > lastDate)) lastDate = date;
        });

        refs.total.textContent = features.length;
        refs.municipalitiesAffected.textContent = municipalities.size;
        refs.satellites.textContent = satellites.size;
        refs.lastRecord.textContent = lastDate ? lastDate.toLocaleTimeString('pt-BR', {
            timeZone: 'America/Sao_Paulo',
            hour: '2-digit',
            minute: '2-digit'
        }) : '--';

        const totalMunicipalities = state.data.municipalities?.features?.length || 0;
        refs.municipalitiesTotal.textContent = totalMunicipalities;
    }

    function updateList(features) {
        const sorted = features.slice().sort(function (a, b) {
            return (getFeatureDate(b)?.getTime() || 0) - (getFeatureDate(a)?.getTime() || 0);
        });

        if (!sorted.length) {
            refs.list.innerHTML = '<div class="fire-empty">Nenhum foco no filtro atual.</div>';
            return;
        }

        refs.list.innerHTML = sorted.slice(0, 40).map(function (feature) {
            const props = feature.properties || {};
            return [
                '<article class="fire-list-item">',
                '<strong>' + escapeHtml(formatDate(props.data_hora)) + '</strong>',
                '<span><b>' + escapeHtml(props.municipio || '--') + '</b> | ' + escapeHtml(props.satelite || '--') + '</span>',
                '<span>FRP: ' + escapeHtml(props.frp ?? '--') + ' | Bioma: ' + escapeHtml(props.bioma || '--') + '</span>',
                '</article>'
            ].join('');
        }).join('');
    }

    function populateFilters() {
        const features = [
            ...(state.data.realtime?.features || []),
            ...(state.data.panorama?.features || [])
        ];

        const satellites = new Map();
        const municipalities = new Map();
        features.forEach(function (feature) {
            const props = feature.properties || {};
            if (props.satelite) satellites.set(normalize(props.satelite), props.satelite);
            if (props.municipio_normalizado || props.municipio) {
                municipalities.set(normalize(props.municipio_normalizado || props.municipio), props.municipio || props.municipio_normalizado);
            }
        });

        refs.satellite.innerHTML = '<option value="all">Todos os satelites</option>' +
            Array.from(satellites.entries()).sort((a, b) => a[1].localeCompare(b[1])).map(([key, name]) => {
                return '<option value="' + escapeHtml(key) + '">' + escapeHtml(name) + '</option>';
            }).join('');

        refs.municipality.innerHTML = '<option value="all">Todos os municipios</option>' +
            Array.from(municipalities.entries()).sort((a, b) => a[1].localeCompare(b[1])).map(([key, name]) => {
                return '<option value="' + escapeHtml(key) + '">' + escapeHtml(name) + '</option>';
            }).join('');
    }

    function updateMenuCounters() {
        const realtimeCount = state.data.realtime?.features?.length || 0;
        const panoramaCount = state.data.panorama?.features?.length || 0;
        const counters = {
            realtime: realtimeCount,
            panorama: panoramaCount,
            wind: realtimeCount,
            risk: panoramaCount
        };
        Object.entries(counters).forEach(function ([view, count]) {
            const element = qs('[data-fire-count="' + view + '"]');
            if (element) element.textContent = count > 999 ? '999+' : count;
        });
    }

    async function render() {
        if (!state.active) return;
        initLayers();
        updatePanelHeader();
        clearFireLayers();
        addBaseFireLayers();

        await ensureData(getDatasetKey());
        const features = filterFeatures();
        renderFocusLayers(features);
        updateSummary(features);
        updateList(features);

        if (state.view === 'wind') await renderWindLayers(features);
        if (state.view === 'risk') await renderRiskLayers(features);

        const mapRef = getMap();
        if (mapRef && !state.focusedOnce && features.length) {
            try {
                mapRef.fitBounds(layers.focus.getBounds(), { padding: [40, 40], maxZoom: 8 });
                state.focusedOnce = true;
            } catch (error) {
                mapRef.setView([-12.7, -44.6], 7);
            }
        }
    }

    async function activate(view) {
        state.active = true;
        state.view = view || state.view || 'realtime';
        document.body.classList.add('fire-mode-active');
        refs.panel.classList.add('active');

        if (!state.loaded) {
            refs.status.textContent = 'Carregando dados de focos...';
            await loadCoreData();
        }

        await render();
    }

    function deactivate() {
        state.active = false;
        document.body.classList.remove('fire-mode-active');
        refs.panel.classList.remove('active');
        clearFireLayers();
    }

    function setView(view) {
        if (view === 'map') {
            const mapRef = getMap();
            if (mapRef) mapRef.setView([-12.7, -44.6], 7);
            return;
        }
        if (state.view !== view) {
            state.timeWindow = VIEW_CONFIG[view].defaultTimeWindow;
            if (refs.timeWindow) refs.timeWindow.value = state.timeWindow;
        }
        state.view = view;
        qsa('.fire-nav-option').forEach(function (option) {
            option.classList.toggle('active', option.dataset.fireView === view);
        });
        activate(view);
    }

    function readRefs() {
        refs.panel = qs('#firePanel');
        refs.close = qs('#firePanelClose');
        refs.title = qs('#firePanel h2');
        refs.status = qs('#fireStatusText');
        refs.timeWindow = qs('#fireTimeWindow');
        refs.satellite = qs('#fireSatelliteFilter');
        refs.municipality = qs('#fireMunicipalityFilter');
        refs.riskType = qs('#fireRiskType');
        refs.total = qs('#fireTotal');
        refs.municipalitiesAffected = qs('#fireMunicipalitiesAffected');
        refs.municipalitiesTotal = qs('#fireMunicipalitiesTotal');
        refs.satellites = qs('#fireSatellites');
        refs.lastRecord = qs('#fireLastRecord');
        refs.list = qs('#fireList');
        refs.windCard = qs('#fireWindCard');
        refs.windAvg = qs('#fireWindAvg');
        refs.windDirection = qs('#fireWindDirection');
        refs.windStatus = qs('#fireWindStatus');
    }

    function bindEvents() {
        refs.close?.addEventListener('click', deactivate);

        qs('#fire-control .select-header')?.addEventListener('click', function () {
            window.setTimeout(function () {
                if (qs('#fire-control')?.classList.contains('active')) activate(state.view);
            }, 0);
        });

        qs('#layer-control .select-header')?.addEventListener('click', function () {
            window.setTimeout(function () {
                if (qs('#layer-control')?.classList.contains('active')) deactivate();
            }, 0);
        });

        qsa('.fire-nav-option').forEach(function (option) {
            option.addEventListener('click', function () {
                setView(option.dataset.fireView);
            });
        });

        refs.timeWindow.addEventListener('change', function () {
            state.timeWindow = refs.timeWindow.value;
            render();
        });

        refs.satellite.addEventListener('change', function () {
            state.satellite = refs.satellite.value;
            render();
        });

        refs.municipality.addEventListener('change', function () {
            state.municipality = refs.municipality.value;
            render();
        });

        refs.riskType.addEventListener('change', function () {
            state.riskType = refs.riskType.value;
            render();
        });

        qsa('[data-fire-display]').forEach(function (button) {
            button.addEventListener('click', function () {
                state.display = button.dataset.fireDisplay;
                qsa('[data-fire-display]').forEach(function (item) {
                    item.classList.toggle('active', item === button);
                });
                render();
            });
        });
    }

    function init() {
        if (!window.L || !getMap()) {
            window.setTimeout(init, 150);
            return;
        }
        readRefs();
        if (!refs.panel) return;
        bindEvents();
        updatePanelHeader();
    }

    window.fireMonitoring = {
        activate: activate,
        deactivate: deactivate,
        setView: setView,
        refresh: function () {
            state.data.realtime = null;
            state.loaded = false;
            return activate(state.view);
        }
    };

    document.addEventListener('DOMContentLoaded', init);
})();
