// layer-control.js

function initLayerControl() {
    // Configurações das camadas
    const layerConfig = {
        'cnarh': {
            name: 'CNARH',
            color: '#A47864',
            icon: 'fas fa-tint'
        },
        'hidroweb_qualidade_agua': {
            name: 'HidroWeb - Qualidade da Água',
            color: '#3388ff',
            icon: 'fas fa-flask'
        },
        'hidroweb_telemetria': {
            name: 'HidroWeb - Telemetria',
            color: '#00cc66',
            icon: 'fas fa-satellite-dish'
        },
        'hidroweb_telemetria_com_previsao': {
            name: 'HidroWeb - Telemetria c/ Previsão',
            color: '#9933ff',
            icon: 'fas fa-chart-line'
        },
        'lrgs_client': {
            name: 'LRGS Client (DCP)',
            color: '#ff7800',
            icon: 'fas fa-satellite'
        },
        'pocos_rimas': {
            name: 'Poços RIMAS',
            color: '#ff0000',
            icon: 'fas fa-water'
        },
        'pocos_siagas': {
            name: 'Poços SIAGAS',
            color: '#e16ccf',
            icon: 'fas fa-oil-well'
        }
    };

    let layerCounts = {};
    let clusterGroups = {};

    setTimeout(() => {
        if (typeof map === 'undefined') {
            setTimeout(initLayerControl, 1000);
            return;
        }

        clusterGroups = window.clusterGroups;

        if (!clusterGroups) return;

        Object.keys(layerConfig).forEach(layerKey => {
            if (clusterGroups[layerKey]) {
                const markers = clusterGroups[layerKey].getLayers();
                layerCounts[layerKey] = markers.length;
            }
        });

        createLayerButtons();
        updateActiveStates();

    }, 1500);

    function createLayerButtons() {
        const container = document.getElementById('layer-control-buttons');
        if (!container) return;

        container.innerHTML = '';

        Object.entries(layerConfig).forEach(([key, config], index) => {
            const button = document.createElement('button');
            button.className = 'select-option active';
            button.dataset.layer = key;
            button.style.animationDelay = `${0.1 + (index * 0.05)}s`;

            button.innerHTML = `
                <span class="option-indicator" style="background: ${config.color}"></span>
                <span class="option-name">${config.name}</span>
            `;

            button.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleLayer(key, this);
            });

            container.appendChild(button);
        });
    }

    function toggleLayer(layerKey, button) {
        if (!clusterGroups[layerKey]) return;

        const isActive = button.classList.contains('active');

        if (isActive) {
            map.removeLayer(clusterGroups[layerKey]);
            button.classList.remove('active');
            button.querySelector('.option-status').style.background = '#dc3545';
        } else {
            map.addLayer(clusterGroups[layerKey]);
            button.classList.add('active');
            button.querySelector('.option-status').style.background = '#28a745';
        }
    }

    function updateActiveStates() {
        Object.keys(layerConfig).forEach(layerKey => {
            if (clusterGroups[layerKey]) {
                const button = document.querySelector(`.select-option[data-layer="${layerKey}"]`);
                if (button) {
                    const isActive = map.hasLayer(clusterGroups[layerKey]);
                    button.classList.toggle('active', isActive);

                    const status = button.querySelector('.option-status');
                    if (status) {
                        status.style.background = isActive ? '#28a745' : '#dc3545';
                    }
                }
            }
        });
    }

    // Exportar funções específicas do controle de camadas
    window.layerControl = {
        updateCounts: function () {
            Object.keys(layerConfig).forEach(layerKey => {
                if (clusterGroups[layerKey]) {
                    const markers = clusterGroups[layerKey].getLayers();
                    const count = markers.length;
                    layerCounts[layerKey] = count;

                    const button = document.querySelector(
                        `.select-option[data-layer="${layerKey}"] .option-count`);
                    if (button) {
                        button.textContent = count;
                    }
                }
            });
        },
        refresh: createLayerButtons,
        updateActiveStates: updateActiveStates
    };
}

// Inicializar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLayerControl);
} else {
    initLayerControl();
}