function openStationModal(config) {
    const modal = document.getElementById(config.modalId);
    const modalIdPonto = document.getElementById(config.idPontoId);
    const loadingSpinner = document.getElementById(config.loadingId);
    const tableContainer = document.getElementById(config.tableContainerId);
    const errorMessage = document.getElementById(config.errorMessageId);
    const tableBody = document.getElementById(config.tableBodyId);

    modal.style.display = 'block';
    modalIdPonto.innerHTML = config.idPonto + ' - ' + config.stationName +
        '<br><br><strong>Latitude:</strong> ' + config.latitude +
        '  <strong>Longitude:</strong> ' + config.longitude;
    loadingSpinner.style.display = 'block';
    tableContainer.style.display = 'none';
    errorMessage.style.display = 'none';
    tableBody.innerHTML = '';

    fetch(config.apiUrl)
        .then(response => {
            if (!response.ok) throw new Error('Erro ao buscar leituras');
            return response.json();
        })
        .then(data => {
            loadingSpinner.style.display = 'none';

            if (data.data && data.data.readings && data.data.readings.length > 0) {
                document.getElementById(config.totalReadingsId).textContent = data.data.readings.length;

                data.data.readings.forEach(reading => {
                    const row = document.createElement('tr');
                    row.innerHTML = config.renderRow(reading);
                    tableBody.appendChild(row);
                });

                tableContainer.style.display = 'block';
            } else {
                errorMessage.style.display = 'block';
                document.getElementById(config.errorTextId).textContent = 'Nenhuma leitura encontrada.';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            loadingSpinner.style.display = 'none';
            errorMessage.style.display = 'block';
            document.getElementById(config.errorTextId).textContent = error.message;
        });
}

function closeStationModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function initStationModal(config) {
    const closeBtn = document.getElementById(config.closeButtonId);
    if (closeBtn) {
        closeBtn.addEventListener('click', () => closeStationModal(config.modalId));
    }

    window.addEventListener('click', function (event) {
        const modal = document.getElementById(config.modalId);
        if (event.target === modal) {
            closeStationModal(config.modalId);
        }
    });
}

// Configuração específica para RIMAS
const rimasModalConfig = {
    modalId: 'rimasReadingsModal',
    idPontoId: 'rimasModalIdPonto',
    loadingId: 'rimasLoadingSpinner',
    tableContainerId: 'rimasReadingsTableContainer',
    errorMessageId: 'rimasErrorMessage',
    tableBodyId: 'rimasReadingsTableBody',
    totalReadingsId: 'rimasModalTotalReadings',
    errorTextId: 'rimasErrorText',
    closeButtonId: 'closeRimasModal',
    renderRow: function (reading) {
        return `
            <td>${reading.numero_de || '-'}</td>
            <td>${reading.data_da_me || '-'}</td>
            <td>${reading.hora_da_me || '-'}</td>
            <td>${reading.nivel_da_a ? parseFloat(reading.nivel_da_a).toFixed(2) : '-'}</td>
            <td>${reading.field_8 || '-'}</td>
        `;
    }
};

function openRimasReadingsModal(idPonto, stationName, latitude, longitude) {
    openStationModal({
        ...rimasModalConfig,
        idPonto: idPonto,
        stationName: stationName,
        latitude: latitude,
        longitude: longitude,
        apiUrl: `/api/pocos-rimas/${idPonto}/readings`
    });
}

// Inicializar quando DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initStationModal(rimasModalConfig));
} else {
    initStationModal(rimasModalConfig);
}

// Configuração específica para SIAGAS
const siagasModalConfig = {
    modalId: 'siagasReadingsModal',
    idPontoId: 'siagasModalIdPonto',
    loadingId: 'siagasLoadingSpinner',
    dataContainerId: 'siagasDataContainer',
    errorMessageId: 'siagasErrorMessage',
    errorTextId: 'siagasErrorText',
    closeButtonId: 'closeSiagasModal'
};

function openSiagasReadingsModal(idPonto, stationName, latitude, longitude) {
    const modal = document.getElementById(siagasModalConfig.modalId);
    const modalIdPonto = document.getElementById(siagasModalConfig.idPontoId);
    const loadingSpinner = document.getElementById(siagasModalConfig.loadingId);
    const dataContainer = document.getElementById(siagasModalConfig.dataContainerId);
    const errorMessage = document.getElementById(siagasModalConfig.errorMessageId);

    modal.style.display = 'block';

    modalIdPonto.innerHTML = `
        <div class="siagas-header-content-title">
            <h2 style="margin: 0;">Informações sobre poços</h2>
            <p style="margin: 0;">Localização, profundidade, tipo de poço, testes de bombeamento, vazão, entre outros.</p>
        </div>
    `;

    loadingSpinner.style.display = 'block';
    dataContainer.style.display = 'none';
    errorMessage.style.display = 'none';

    fetch(`/api/pocos-siagas/${idPonto}/readings`)
        .then(response => {
            if (!response.ok) throw new Error('Erro ao buscar dados');
            return response.json();
        })
        .then(data => {
            loadingSpinner.style.display = 'none';

            if (data.success && data.data && data.data.poco) {
                const poco = data.data.poco;
                let html = '';

                // Renderizar todos os campos
                Object.keys(poco).forEach(key => {
                    const value = poco[key] !== null && poco[key] !== '' ? poco[key] : '-';
                    html += `
                        <div class="siagas-data-row">
                            <div class="siagas-data-label" style="text-transform: capitalize;">${key}:</div>
                            <div class="siagas-data-value">${value}</div>
                        </div>
                    `;
                });

                document.getElementById('siagasDataContent').innerHTML = html;
                dataContainer.style.display = 'block';
            } else {
                errorMessage.style.display = 'block';
                document.getElementById(siagasModalConfig.errorTextId).textContent = 'Nenhum dado encontrado.';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            loadingSpinner.style.display = 'none';
            errorMessage.style.display = 'block';
            document.getElementById(siagasModalConfig.errorTextId).textContent = error.message;
        });
}

// Inicializar modal SIAGAS
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initStationModal(siagasModalConfig));
} else {
    initStationModal(siagasModalConfig);
}

// Configuração específica para HidroWeb Qualidade da Água
const hidrowebQaModalConfig = {
    modalId: 'hidrowebQaReadingsModal',
    stationCodeId: 'hidrowebQaModalStationCode',
    loadingId: 'hidrowebQaLoadingSpinner',
    dataContainerId: 'hidrowebQaDataContainer',
    errorMessageId: 'hidrowebQaErrorMessage',
    errorTextId: 'hidrowebQaErrorText',
    closeButtonId: 'closeHidrowebQaModal'
};

function openHidrowebQaReadingsModal(stationCode, stationName, latitude, longitude) {
    const modal = document.getElementById(hidrowebQaModalConfig.modalId);
    const modalStationCode = document.getElementById(hidrowebQaModalConfig.stationCodeId);
    const loadingSpinner = document.getElementById(hidrowebQaModalConfig.loadingId);
    const tableContainer = document.getElementById('hidrowebQaTableContainer');
    const errorMessage = document.getElementById(hidrowebQaModalConfig.errorMessageId);
    const tableHeader = document.getElementById('hidrowebQaTableHeader');
    const tableBody = document.getElementById('hidrowebQaTableBody');

    modal.style.display = 'block';
    modalStationCode.innerHTML = stationCode + ' - ' + stationName +
        '<br><strong style="color: #3388ff;">Latitude:</strong> ' + latitude +
        ' | <strong style="color: #3388ff;">Longitude:</strong> ' + longitude;
    loadingSpinner.style.display = 'block';
    tableContainer.style.display = 'none';
    errorMessage.style.display = 'none';
    tableHeader.innerHTML = '';
    tableBody.innerHTML = '';

    fetch(`/api/hidroweb-qualidade-agua/${stationCode}/readings`)
        .then(response => {
            if (!response.ok) throw new Error('Erro ao buscar leituras');
            return response.json();
        })
        .then(data => {
            loadingSpinner.style.display = 'none';

            if (data.success && data.data && data.data.readings && data.data.readings.length > 0) {
                document.getElementById('hidrowebQaModalTotalReadings').textContent = data.data.readings.length;

                const readings = data.data.readings;

                // Criar cabeçalho da tabela com todos os campos do primeiro registro
                const headerRow = document.createElement('tr');
                Object.keys(readings[0]).forEach(key => {
                    const th = document.createElement('th');
                    th.textContent = key;
                    headerRow.appendChild(th);
                });
                tableHeader.appendChild(headerRow);

                // Criar linhas da tabela
                readings.forEach(reading => {
                    const row = document.createElement('tr');
                    Object.keys(reading).forEach(key => {
                        const td = document.createElement('td');
                        const value = reading[key];
                        td.textContent = value !== null && value !== '' ? value : '-';
                        row.appendChild(td);
                    });
                    tableBody.appendChild(row);
                });

                tableContainer.style.display = 'block';
            } else {
                errorMessage.style.display = 'block';
                document.getElementById(hidrowebQaModalConfig.errorTextId).textContent = 'Nenhuma leitura encontrada.';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            loadingSpinner.style.display = 'none';
            errorMessage.style.display = 'block';
            document.getElementById(hidrowebQaModalConfig.errorTextId).textContent = error.message;
        });
}

// Inicializar modal HidroWeb QA
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initStationModal(hidrowebQaModalConfig));
} else {
    initStationModal(hidrowebQaModalConfig);
}

// Configuração específica para LRGS Client (DCP)
const lrgsModalConfig = {
    modalId: 'lrgsReadingsModal',
    stationCodeId: 'lrgsModalStationCode',
    loadingId: 'lrgsLoadingSpinner',
    errorMessageId: 'lrgsErrorMessage',
    errorTextId: 'lrgsErrorText',
    closeButtonId: 'closeLrgsModal'
};

function openLrgsReadingsModal(stationCode, stationName, latitude, longitude) {
    const modal = document.getElementById(lrgsModalConfig.modalId);
    const modalStationCode = document.getElementById(lrgsModalConfig.stationCodeId);
    const loadingSpinner = document.getElementById(lrgsModalConfig.loadingId);
    const tableContainer = document.getElementById('lrgsTableContainer');
    const errorMessage = document.getElementById(lrgsModalConfig.errorMessageId);
    const tableHeader = document.getElementById('lrgsTableHeader');
    const tableBody = document.getElementById('lrgsTableBody');

    modal.style.display = 'block';
    modalStationCode.innerHTML = stationCode + ' - ' + stationName +
        '<br><strong style="color: #3388ff;">Latitude:</strong> ' + latitude +
        ' | <strong style="color: #3388ff;">Longitude:</strong> ' + longitude;
    loadingSpinner.style.display = 'block';
    tableContainer.style.display = 'none';
    errorMessage.style.display = 'none';
    tableHeader.innerHTML = '';
    tableBody.innerHTML = '';

    fetch(`/api/lrgs-client/${stationCode}/readings`)
        .then(response => {
            if (!response.ok) throw new Error('Erro ao buscar leituras');
            return response.json();
        })
        .then(data => {
            loadingSpinner.style.display = 'none';

            if (data.success && data.data && data.data.readings && data.data.readings.length > 0) {
                document.getElementById('lrgsModalTotalReadings').textContent = data.data.readings.length;

                const readings = data.data.readings;

                // Criar cabeçalho da tabela
                const headerRow = document.createElement('tr');
                Object.keys(readings[0]).forEach(key => {
                    const th = document.createElement('th');
                    th.textContent = key;
                    headerRow.appendChild(th);
                });
                tableHeader.appendChild(headerRow);

                // Criar linhas da tabela
                readings.forEach(reading => {
                    const row = document.createElement('tr');
                    Object.keys(reading).forEach(key => {
                        const td = document.createElement('td');
                        const value = reading[key];
                        td.textContent = value !== null && value !== '' ? value : '-';
                        row.appendChild(td);
                    });
                    tableBody.appendChild(row);
                });

                tableContainer.style.display = 'block';
            } else {
                errorMessage.style.display = 'block';
                document.getElementById(lrgsModalConfig.errorTextId).textContent = 'Nenhuma leitura encontrada.';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            loadingSpinner.style.display = 'none';
            errorMessage.style.display = 'block';
            document.getElementById(lrgsModalConfig.errorTextId).textContent = error.message;
        });
}

// Inicializar modal LRGS
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initStationModal(lrgsModalConfig));
} else {
    initStationModal(lrgsModalConfig);
}

// Configuração específica para CNARH
const cnarhModalConfig = {
    modalId: 'cnarhReadingsModal',
    codeId: 'cnarhModalCode',
    loadingId: 'cnarhLoadingSpinner',
    dataContainerId: 'cnarhDataContainer',
    errorMessageId: 'cnarhErrorMessage',
    errorTextId: 'cnarhErrorText',
    closeButtonId: 'closeCnarhModal'
};

function openCnarhReadingsModal(cnarhCode, stationName, latitude, longitude) {
    const modal = document.getElementById(cnarhModalConfig.modalId);
    const modalCode = document.getElementById(cnarhModalConfig.codeId);
    const loadingSpinner = document.getElementById(cnarhModalConfig.loadingId);
    const dataContainer = document.getElementById(cnarhModalConfig.dataContainerId);
    const errorMessage = document.getElementById(cnarhModalConfig.errorMessageId);

    modal.style.display = 'block';
    modalCode.innerHTML = cnarhCode + ' - ' + stationName +
        '<br><strong style="color: #A47864;">Latitude:</strong> ' + latitude +
        ' | <strong style="color: #A47864;">Longitude:</strong> ' + longitude;
    loadingSpinner.style.display = 'block';
    dataContainer.style.display = 'none';
    errorMessage.style.display = 'none';

    fetch(`/api/cnarh/${cnarhCode}/readings`)
        .then(response => {
            if (!response.ok) throw new Error('Erro ao buscar dados');
            return response.json();
        })
        .then(data => {
            loadingSpinner.style.display = 'none';

            if (data.success && data.data && data.data.cnarh) {
                const cnarh = data.data.cnarh;
                let html = '';

                Object.keys(cnarh).forEach(key => {
                    const value = cnarh[key] !== null && cnarh[key] !== '' ? cnarh[key] : '-';
                    html += `
                        <div class="cnarh-data-row">
                            <div class="cnarh-data-label">${key}:</div>
                            <div class="cnarh-data-value">${value}</div>
                        </div>
                    `;
                });

                document.getElementById('cnarhDataContent').innerHTML = html;
                dataContainer.style.display = 'block';
            } else {
                errorMessage.style.display = 'block';
                document.getElementById(cnarhModalConfig.errorTextId).textContent = 'Nenhum dado encontrado.';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            loadingSpinner.style.display = 'none';
            errorMessage.style.display = 'block';
            document.getElementById(cnarhModalConfig.errorTextId).textContent = error.message;
        });
}

// Inicializar modal CNARH
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initStationModal(cnarhModalConfig));
} else {
    initStationModal(cnarhModalConfig);
}


// ========================================
// HidroWeb Telemetria Data Modal (com Tabs: Leituras + Previsões)
// ========================================

// Configuração do modal
const hidrowebDataModalConfig = {
    modalId: 'hidrowebTelemetryDataModal',
    stationCodeId: 'hidrowebDataModalStationCode',
    stationNameId: 'hidrowebDataModalStationName',
    closeButtonId: 'closeHidrowebDataModal'
};

function openHidrowebTelemetryDataModal(stationCode, stationName, lat, lng) {
    const modal = document.getElementById(hidrowebDataModalConfig.modalId);

    // Atualizar informações da estação
    document.getElementById(hidrowebDataModalConfig.stationCodeId).textContent = stationCode;
    document.getElementById(hidrowebDataModalConfig.stationNameId).textContent = stationName;

    // Resetar tabs (voltar para Leituras)
    document.querySelectorAll('.hidroweb-data-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.hidroweb-data-tab-content').forEach(content => content.classList.remove('active'));
    document.querySelector('.hidroweb-data-tab-btn[data-tab="leituras"]').classList.add('active');
    document.getElementById('tab-leituras').classList.add('active');

    // Mostrar modal
    modal.style.display = 'block';

    // Carregar dados das abas
    loadHidrowebLeiturasData(stationCode);
    loadHidrowebPrevisoesData(stationCode);
}

function loadHidrowebLeiturasData(stationCode) {
    const elements = {
        loading: document.getElementById('leiturasLoadingSpinner'),
        tableContainer: document.getElementById('leiturasTableContainer'),
        error: document.getElementById('leiturasErrorMessage'),
        tableBody: document.getElementById('leiturasTableBody'),
        total: document.getElementById('leiturasTotal'),
        errorText: document.getElementById('leiturasErrorText')
    };

    // Limpar estado anterior
    if (window.leiturasChartInstance) {
        window.leiturasChartInstance.destroy();
        window.leiturasChartInstance = null;
    }

    document.querySelector('.view-controls')?.remove();
    document.getElementById('leiturasChartContainer')?.remove();
    document.getElementById('leiturasChart')?.remove();

    // Resetar UI
    Object.values(elements).forEach(el => {
        if (el && el.style) {
            if (el === elements.loading) el.style.display = 'block';
            else if (el === elements.tableContainer) el.style.display = 'none';
            else if (el === elements.error) el.style.display = 'none';
            else if (el === elements.tableBody) el.innerHTML = '';
        }
    });

    // Criar elementos do gráfico e controles
    const chartContainer = createChartContainer();
    const controlsContainer = createControlsContainer();
    elements.tableContainer.parentNode.insertBefore(chartContainer, elements.tableContainer);
    elements.tableContainer.parentNode.insertBefore(controlsContainer, elements.tableContainer.nextSibling);

    let chartData = null;

    // Configurar visualizações
    const views = {
        table: () => {
            controlsContainer.children[0].className = 'view-btn active';
            controlsContainer.children[0].style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            controlsContainer.children[1].className = 'view-btn';
            controlsContainer.children[1].style.cssText = 'padding: 10px 20px; background: #ffffff; color: #242731; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            elements.tableContainer.style.display = 'block';
            chartContainer.style.display = 'none';
        },
        chart: () => {
            controlsContainer.children[0].className = 'view-btn';
            controlsContainer.children[0].style.cssText = 'padding: 10px 20px; background: #ffffff; color: #333; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            controlsContainer.children[1].className = 'view-btn active';
            controlsContainer.children[1].style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            elements.tableContainer.style.display = 'none';
            chartContainer.style.display = 'block';
            if (chartData && (!window.leiturasChartInstance || window.leiturasChartInstance.canvas.id !== 'leiturasChart')) {
                createChart(chartData);
            }
        }
    };

    controlsContainer.children[0].onclick = views.table;
    controlsContainer.children[1].onclick = views.chart;

    // Buscar dados
    fetch(`/api/hidroweb-telemetria/${stationCode}/readings`)
        .then(response => response.ok ? response.json() : Promise.reject('Erro ao buscar leituras'))
        .then(data => {
            elements.loading.style.display = 'none';

            if (data.success && data.data?.readings?.length > 0) {
                chartData = data.data.readings;
                elements.total.textContent = data.data.readings.length;

                data.data.readings.forEach(reading => {
                    // Formatar data/hora para tabela: DD/MM/AAAA - HH:MM
                    const formattedDateTime = formatDateTimeForTable(reading.measurement_datetime);

                    elements.tableBody.innerHTML += `
                        <tr>
                            <td>${formattedDateTime}</td>
                            <td>${reading.adopted_rainfall || '-'}</td>
                            <td>${reading.adopted_quota || '-'}</td>
                            <td>${reading.adopted_flow || '-'}</td>
                        </tr>`;
                });

                views.table();
            } else {
                showError('Nenhuma leitura encontrada.');
            }
        })
        .catch(error => {
            console.error('Erro ao carregar leituras:', error);
            elements.loading.style.display = 'none';
            showError(error.message || error);
        });

    function showError(message) {
        elements.error.style.display = 'block';
        elements.errorText.textContent = message;
        controlsContainer.style.display = 'none';
        chartContainer.style.display = 'none';
    }

    function formatDateTimeForTable(dateTimeStr) {
        if (!dateTimeStr) return '-';

        try {
            const date = new Date(dateTimeStr);
            return date.toLocaleString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }).replace(',', ' -');
        } catch (e) {
            return dateTimeStr;
        }
    }

    function createChartContainer() {
        const container = document.createElement('div');
        container.id = 'leiturasChartContainer';
        container.style.cssText = 'display: none; width: 100%; height: 400px; margin-bottom: 20px; position: relative;';

        const canvas = document.createElement('canvas');
        canvas.id = 'leiturasChart';
        canvas.style.cssText = 'width: 100% !important; height: 100% !important;';
        container.appendChild(canvas);

        return container;
    }

    function createControlsContainer() {
        const container = document.createElement('div');
        container.className = 'view-controls';
        container.style.cssText = 'margin: 20px 0; display: flex; justify-content: center; gap: 15px; padding: 15px;';

        ['TABELA', 'GRÁFICO'].forEach((text, i) => {
            const btn = document.createElement('button');
            btn.id = i === 0 ? 'tableViewBtn' : 'chartViewBtn';
            btn.textContent = text;
            btn.className = i === 0 ? 'view-btn active' : 'view-btn';
            btn.style.cssText = `padding: 10px 20px; background: ${i === 0 ? '#242731' : '#ffffff'}; color: ${i === 0 ? 'white' : '#242731'}; border: ${i === 0 ? 'none' : '1px solid #79808F'}; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;`;
            container.appendChild(btn);
        });

        return container;
    }

    function createChart(readings) {
        if (window.leiturasChartInstance) {
            window.leiturasChartInstance.destroy();
            window.leiturasChartInstance = null;
        }

        const canvas = document.getElementById('leiturasChart');
        if (!canvas) {
            console.error('Canvas não encontrado!');
            return;
        }

        // Para o gráfico: mostrar apenas horas (HH:MM)
        const hours = readings.map(r => {
            if (!r.measurement_datetime) return '';
            try {
                const date = new Date(r.measurement_datetime);
                return date.toLocaleTimeString('pt-BR', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
            } catch (e) {
                return '';
            }
        }).filter(h => h);

        const datasets = [];
        const dataTypes = [
            { key: 'adopted_quota', label: 'Cota (m)', color: '#3388ff', axis: 'y' },
            { key: 'adopted_flow', label: 'Vazão (m³/s)', color: '#ff5733', axis: 'y1' },
            { key: 'adopted_rainfall', label: 'Precipitação (mm)', color: '#33ff57', axis: 'y2' }
        ];

        dataTypes.forEach((type, index) => {
            const values = readings.map(r => {
                const val = parseFloat(r[type.key]);
                return isNaN(val) ? null : val;
            });

            if (values.some(v => v !== null)) {
                datasets.push({
                    label: type.label,
                    data: values,
                    borderColor: type.color,
                    backgroundColor: type.color.replace(')', ', 0.1)').replace('rgb', 'rgba'),
                    borderWidth: 2,
                    tension: 0.1,
                    yAxisID: type.axis
                });
            }
        });

        if (datasets.length === 0) {
            chartContainer.innerHTML = '<div style="text-align: center; padding: 50px; color: #666;">Não há dados numéricos suficientes para exibir o gráfico.</div>';
            return;
        }

        const scales = {
            x: {
                title: { display: true, text: 'Hora' },
                ticks: { maxTicksLimit: 10, autoSkip: true }
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: { display: true, text: datasets[0].label.split(' ')[0] + ' ' + datasets[0].label.split(' ')[1] }
            }
        };

        datasets.forEach((dataset, i) => {
            if (i > 0) {
                scales[`y${i}`] = {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: dataset.label },
                    grid: { drawOnChartArea: false }
                };
            }
        });

        window.leiturasChartInstance = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: { labels: hours, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: `Estação ${stationCode} - Séries Temporais`,
                        font: { size: 16 }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            title: function (context) {
                                const index = context[0].dataIndex;
                                // Mostrar data e hora completa no tooltip
                                if (readings[index]?.measurement_datetime) {
                                    try {
                                        const date = new Date(readings[index].measurement_datetime);
                                        return date.toLocaleString('pt-BR', {
                                            day: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            hour12: false
                                        });
                                    } catch (e) {
                                        return readings[index].measurement_datetime;
                                    }
                                }
                                return '';
                            }
                        }
                    }
                },
                scales,
                interaction: { intersect: false, mode: 'nearest' }
            }
        });
    }
}

function loadHidrowebPrevisoesData(stationCode) {
    const loadingSpinner = document.getElementById('previsoesLoadingSpinner');
    const tableContainer = document.getElementById('previsoesTableContainer');
    const errorMessage = document.getElementById('previsoesErrorMessage');
    const emptyMessage = document.getElementById('previsoesEmpty');
    const tableBody = document.getElementById('previsoesTableBody');

    // Mostrar loading
    loadingSpinner.style.display = 'block';
    tableContainer.style.display = 'none';
    errorMessage.style.display = 'none';
    emptyMessage.style.display = 'none';
    tableBody.innerHTML = '';

    // Fetch dos dados (nova API que vamos criar)
    fetch(`/api/hidroweb-telemetria/${stationCode}/forecast`)
        .then(response => {
            if (response.status === 404) {
                // Sem previsões
                loadingSpinner.style.display = 'none';
                emptyMessage.style.display = 'block';
                return null;
            }
            if (!response.ok) throw new Error('Erro ao buscar previsões');
            return response.json();
        })
        .then(data => {
            if (!data) return;

            loadingSpinner.style.display = 'none';

            if (data.success && data.data && data.data.forecasts && data.data.forecasts.length > 0) {
                // Atualizar total
                document.getElementById('previsoesTotal').textContent = data.data.forecasts.length;

                // Preencher tabela
                data.data.forecasts.forEach(forecast => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${forecast.forecast_year || '-'}</td>
                        <td>${forecast.forecast_month || '-'}</td>
                        <td>${forecast.predicted_flow || '-'}</td>
                        <td>${forecast.alfa_pond || '-'}</td>
                        <td>${forecast.q_noventa || '-'}</td>
                        <td>${forecast.vsup || '-'}</td>
                    `;
                    tableBody.appendChild(row);
                });

                tableContainer.style.display = 'block';
            } else {
                emptyMessage.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar previsões:', error);
            loadingSpinner.style.display = 'none';
            errorMessage.style.display = 'block';
            document.getElementById('previsoesErrorText').textContent = error.message;
        });
}

// Event listeners para tabs e modal
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHidrowebDataModal);
} else {
    initHidrowebDataModal();
}

function initHidrowebDataModal() {
    // Controle de tabs
    const tabButtons = document.querySelectorAll('.hidroweb-data-tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            const targetTab = this.getAttribute('data-tab');

            // Remove active
            document.querySelectorAll('.hidroweb-data-tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.hidroweb-data-tab-content').forEach(content => content.classList.remove('active'));

            // Adiciona active
            this.classList.add('active');
            document.getElementById('tab-' + targetTab).classList.add('active');
        });
    });

    // Fechar modal
    const modal = document.getElementById(hidrowebDataModalConfig.modalId);
    const closeBtn = document.getElementById(hidrowebDataModalConfig.closeButtonId);

    if (closeBtn) {
        closeBtn.onclick = function () {
            modal.style.display = 'none';
        };
    }

    if (modal) {
        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };
    }
}
