// layer-control.js

function initLayerControl() {
  // Configurações das camadas com ícones SVG
  const layerConfig = {
    'cnarh': {
      name: 'CNARH - Outorgas',
      color: '#A47864',
      icon: '/images/icons/CNARH_Outorgas.svg',
      endpoint: '/api/stations/cnarh'
    },
    'hidroweb_qualidade_agua': {
      name: 'HidroWeb - Qualidade da Água',
      color: '#3388ff',
      icon: '/images/icons/HidroWeb_Qualidade.svg',
      endpoint: '/api/stations/hidroweb-qualidade-agua'
    },
    'hidroweb_telemetria': {
      name: 'HidroWeb - Telemetria',
      color: '#00cc66',
      icon: '/images/icons/HidroWeb_telemetria.svg',
      endpoint: '/api/stations/hidroweb-telemetria'
    },
    'hidroweb_telemetria_com_previsao': {
      name: 'Previsão de vazão',
      color: '#9933ff',
      icon: '/images/icons/PREVISAO_VAZAO.svg',
      endpoint: '/api/stations/hidroweb-telemetria-previsao'
    },
    'lrgs_client': {
      name: 'Estações AIBA',
      color: '#ff7800',
      icon: '/images/icons/estacoes_AIBA.svg',
      endpoint: '/api/stations/lrgs-client'
    },
    'pocos_rimas': {
      name: 'Poços RIMAS',
      color: '#ff0000',
      icon: '/images/icons/Pocos_SIAGAS_e_RIMAS.svg',
      endpoint: '/api/stations/pocos-rimas'
    },
    'pocos_siagas': {
      name: 'Poços SIAGAS',
      color: '#e16ccf',
      icon: '/images/icons/Pocos_SIAGAS_e_RIMAS.svg',
      endpoint: '/api/stations/pocos-siagas'
    },
    'pocos_simah': {
      name: 'Poços AIBA',
      color: '#165B9C',
      icon: '/images/icons/Pocos_AIBA.svg',
      endpoint: '/api/stations/pocos-simah'
    },
  };

  // ===== Áreas de drenagem (HidroWeb) =====
  //const HIDROWEB_KEYS = ['hidroweb_telemetria', 'hidroweb_qualidade_agua'];
  const HIDROWEB_KEYS = [];
  let drainageLayers = null;     // array de L.tileLayer
  let drainageLoaded = false;    // já fez fetch na API?
  let drainageRefCount = 0;      // quantos toggles HidroWeb estão ativos

  async function ensureDrainageLayers() {
    if (drainageLoaded) return;
    try {
      const resp = await fetch('/api/hw-station-drainages/ready');
      const json = await resp.json();
      const items = json.data || [];

      // Ordena por área (maior área primeiro). As estações de jusante têm áreas
      // de drenagem que englobam as de montante; renderizar as maiores embaixo
      // garante que as menores fiquem visíveis por cima.
      items.forEach(d => {
        const b = d.bounds_latlng_json;
        d._area = b ? (b.east - b.west) * (b.north - b.south) : 0;
      });
      items.sort((a, b) => b._area - a._area);

      drainageLayers = items.map((d, idx) => {
        const opts = {
          opacity: 1.0,
          minZoom: d.min_zoom,
          maxZoom: 18,
          maxNativeZoom: d.max_zoom,
          tms: true,
          zIndex: 50 + idx, // maiores embaixo, menores em cima
        };
        if (d.bounds_latlng_json) {
          opts.bounds = L.latLngBounds(
            [d.bounds_latlng_json.south, d.bounds_latlng_json.west],
            [d.bounds_latlng_json.north, d.bounds_latlng_json.east]
          );
        }
        return L.tileLayer(d.url_pattern, opts);
      });
      drainageLoaded = true;
      console.log(`Áreas de drenagem carregadas: ${drainageLayers.length}`);
    } catch (err) {
      console.warn('Falha ao carregar áreas de drenagem', err);
      drainageLayers = [];
      drainageLoaded = true;
    }
  }

  function addDrainagesToMap() {
    if (drainageLayers) drainageLayers.forEach(l => l.addTo(map));
  }

  function removeDrainagesFromMap() {
    if (drainageLayers) drainageLayers.forEach(l => map.removeLayer(l));
  }

  // Estado de cada camada
  let layerState = {};
  Object.keys(layerConfig).forEach(key => {
    layerState[key] = {
      loaded: false,   // Se os dados já foram carregados da API
      loading: false,  // Se está carregando agora
      visible: false,  // Se está visível no mapa
      count: 0         // Quantidade de estações
    };
  });

  let clusterGroups = {};

  setTimeout(() => {
    if (typeof map === 'undefined') {
      setTimeout(initLayerControl, 1000);
      return;
    }

    clusterGroups = window.clusterGroups;
    if (!clusterGroups) return;

    createLayerButtons();
  }, 500);

  function createLayerButtons() {
    const container = document.getElementById('layer-control-buttons');
    if (!container) return;

    container.innerHTML = '';

    Object.entries(layerConfig).forEach(([key, config], index) => {
      const button = document.createElement('button');
      button.className = 'select-option';
      button.dataset.layer = key;
      button.style.animationDelay = `${0.1 + (index * 0.05)}s`;

      // Verifica se o ícone é SVG ou imagem
      const isImageIcon = config.icon && (config.icon.endsWith('.svg'));

      let iconHtml = '';
      if (isImageIcon) {
        iconHtml = `<img src="${config.icon}" class="layer-icon" alt="${config.name}" onerror="this.style.display='none'" />`;
      } else {
        iconHtml = `<i class="${config.icon}" style="margin-right: 8px;"></i>`;
      }

      button.innerHTML = `
        
        ${iconHtml}
        <span class="option-name">${config.name}</span>
        <span class="option-loading" style="display: none;">
          <i class="fas fa-spinner fa-spin"></i>
        </span>
        <span class="option-count" style="display: none;">0</span>
      `;

      button.addEventListener('click', function (e) {
        e.stopPropagation();
        toggleLayerLazy(key, this);
      });

      container.appendChild(button);
    });
  }

  async function toggleLayerLazy(layerKey, button) {
    if (!clusterGroups[layerKey]) return;

    const state = layerState[layerKey];

    // Se está carregando, ignorar clique
    if (state.loading) return;

    const isActive = button.classList.contains('active');

    if (isActive) {
      // Desativar - apenas esconder
      map.removeLayer(clusterGroups[layerKey]);
      button.classList.remove('active');
      state.visible = false;

      // Hook drenagem: se desativou um toggle HidroWeb, decrementa refcount
      if (HIDROWEB_KEYS.includes(layerKey)) {
        drainageRefCount = Math.max(0, drainageRefCount - 1);
        if (drainageRefCount === 0 && drainageLoaded) {
          removeDrainagesFromMap();
        }
      }
    } else {
      // Ativar
      if (!state.loaded) {
        // Primeira vez - carregar dados da API
        await loadLayerData(layerKey, button);
      }

      // Adicionar ao mapa se carregou com sucesso
      if (state.loaded) {
        map.addLayer(clusterGroups[layerKey]);
        button.classList.add('active');
        state.visible = true;

        // Hook drenagem: se ativou um toggle HidroWeb, garante drenagens visíveis
        if (HIDROWEB_KEYS.includes(layerKey)) {
          await ensureDrainageLayers();
          if (drainageRefCount === 0) {
            addDrainagesToMap();
          }
          drainageRefCount++;
        }
      }
    }
  }


  async function loadLayerData(layerKey, button) {
    const state = layerState[layerKey];
    const config = layerConfig[layerKey];
    const loadingEl = button.querySelector('.option-loading');
    const countEl = button.querySelector('.option-count');

    try {
      // Mostrar loading
      state.loading = true;
      loadingEl.style.display = 'inline';
      button.style.opacity = '0.7';
      button.style.pointerEvents = 'none';

      // Fetch da API
      const response = await fetch(config.endpoint);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      const stations = data.data.stations;

      // Criar marcadores usando a função global do dashboard
      stations.forEach(function (station) {
        if (station.latitude && station.longitude) {
          const marker = window.createMarker(station);
          clusterGroups[layerKey].addLayer(marker);
        }
      });

      // Atualizar estado
      state.loaded = true;
      state.count = stations.length;

      // Mostrar contador
      countEl.textContent = stations.length;
      countEl.style.display = 'inline';

      console.log(`Camada ${layerKey} carregada: ${stations.length} estações`);

    } catch (error) {
      console.error(`Erro ao carregar camada ${layerKey}:`, error);

      // Feedback visual de erro
      button.style.background = '#ffebee';
      button.style.borderColor = '#f44336';

      setTimeout(() => {
        button.style.background = '';
        button.style.borderColor = '';
      }, 2000);

    } finally {
      // Esconder loading
      state.loading = false;
      loadingEl.style.display = 'none';
      button.style.opacity = '1';
      button.style.pointerEvents = 'auto';
    }
  }

  // Exportar funções específicas do controle de camadas
  window.layerControl = {
    updateCounts: function () {
      Object.keys(layerConfig).forEach(layerKey => {
        const state = layerState[layerKey];
        if (state.loaded && clusterGroups[layerKey]) {
          const count = clusterGroups[layerKey].getLayers().length;
          state.count = count;

          const button = document.querySelector(`.select-option[data-layer="${layerKey}"]`);
          if (button) {
            const countEl = button.querySelector('.option-count');
            if (countEl) {
              countEl.textContent = count;
            }
          }
        }
      });
    },
    refresh: createLayerButtons,
    isLoaded: function (layerKey) {
      return layerState[layerKey]?.loaded || false;
    },
    isVisible: function (layerKey) {
      return layerState[layerKey]?.visible || false;
    },
    getCount: function (layerKey) {
      return layerState[layerKey]?.count || 0;
    }
  };
}

// Inicializar
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initLayerControl);
} else {
  initLayerControl();
}