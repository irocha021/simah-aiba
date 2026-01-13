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

    // Limpar gráficos anteriores
    if (window.siagasChartInstance) {
        window.siagasChartInstance.destroy();
        window.siagasChartInstance = null;
    }

    // Remover elementos de gráfico e controles anteriores
    document.querySelectorAll('.siagas-view-controls, #siagasChartContainer').forEach(el => el.remove());

    fetch(`/api/pocos-siagas/${idPonto}/readings`)
        .then(response => {
            if (!response.ok) throw new Error('Erro ao buscar dados');
            return response.json();
        })
        .then(data => {
            loadingSpinner.style.display = 'none';

            if (data.success && data.data && data.data.poco) {
                const poco = data.data.poco;

                // 1. Primeiro renderizar a tabela
                let html = '<div id="siagasTableContainer">';
                Object.keys(poco).forEach(key => {
                    const value = poco[key] !== null && poco[key] !== '' ? poco[key] : '-';
                    html += `
                        <div class="siagas-data-row">
                            <div class="siagas-data-label" style="text-transform: capitalize;">${key}:</div>
                            <div class="siagas-data-value">${value}</div>
                        </div>
                    `;
                });
                html += '</div>';

                document.getElementById('siagasDataContent').innerHTML = html;
                dataContainer.style.display = 'block';

                // 2. Analisar variáveis numéricas disponíveis
                const numericFields = (function extractNumericFields(poco) {
                    const numericFields = [];
                    const potentialNumericFields = [
                        'cota_terre', 'profundi_1', 'nivel_agua', 'vazao',
                        'nivel_dina', 'nivel_esta', 'vazao_espe', 'vazao_livr',
                        'coeficient', 'permeabili', 'transmissi', 'vazao_esta',
                        'condutivid', 'temperatur', 'turbidez', 'solidos_se', 'solidos_su'
                    ];

                    potentialNumericFields.forEach(field => {
                        if (poco[field] && poco[field] !== '-' && poco[field] !== null) {
                            const numValue = parseFloat(poco[field]);
                            if (!isNaN(numValue)) {
                                numericFields.push({
                                    key: field,
                                    label: (function formatFieldLabel(field) {
                                        const labels = {
                                            'cota_terre': 'Altitude do Terreno (m)',
                                            'profundi_1': 'Profundidade Total (m)',
                                            'nivel_agua': 'Nível d\'Água (m)',
                                            'vazao': 'Vazão (m³/h)',
                                            'nivel_dina': 'Nível Dinâmico (m)',
                                            'nivel_esta': 'Nível Estático (m)',
                                            'vazao_espe': 'Vazão Específica (m³/h/m)',
                                            'vazao_livr': 'Vazão Livre (m³/h)',
                                            'coeficient': 'Coeficiente de Armazenamento',
                                            'permeabili': 'Permeabilidade (m/s)',
                                            'transmissi': 'Transmissividade (m²/s)',
                                            'vazao_esta': 'Vazão Estabilizada (m³/h)',
                                            'condutivid': 'Condutividade Elétrica (µS/cm)',
                                            'temperatur': 'Temperatura (°C)',
                                            'turbidez': 'Turbidez (NTU)',
                                            'solidos_se': 'Sólidos Sedimentáveis (mL/L)',
                                            'solidos_su': 'Sólidos Suspendidos (mg/L)'
                                        };
                                        return labels[field] || field.replace(/_/g, ' ').toUpperCase();
                                    })(field),
                                    value: numValue
                                });
                            }
                        }
                    });

                    return numericFields;
                })(poco);

                // 3. Criar controles e gráfico se houver dados numéricos
                if (numericFields.length > 0) {
                    // Criar os controles
                    const controlsContainer = (function createSiagasControls() {
                        const controlsContainer = document.createElement('div');
                        controlsContainer.className = 'siagas-view-controls';
                        controlsContainer.style.cssText = 'margin: 20px 0; display: flex; justify-content: center; gap: 15px; padding: 15px;';

                        ['TABELA', 'GRÁFICO'].forEach((text, i) => {
                            const btn = document.createElement('button');
                            btn.id = i === 0 ? 'siagasTableViewBtn' : 'siagasChartViewBtn';
                            btn.textContent = text;
                            btn.className = i === 0 ? 'siagas-view-btn active' : 'siagas-view-btn';
                            btn.style.cssText = `padding: 10px 20px; background: ${i === 0 ? '#242731' : '#ffffff'}; color: ${i === 0 ? 'white' : '#242731'}; border: ${i === 0 ? 'none' : '1px solid #79808F'}; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;`;
                            controlsContainer.appendChild(btn);
                        });

                        // Inserir controles depois do dataContainer (fora de qualquer scroll)
                        const dataContainerElement = document.getElementById('siagasDataContainer');
                        if (dataContainerElement) {
                            dataContainerElement.parentNode.insertBefore(controlsContainer, dataContainerElement.nextSibling);
                        }

                        return controlsContainer;
                    })();

                    // Depois criar o container do gráfico (DENTRO do dataContent)
                    const chartContainer = (function createSiagasChartContainer() {
                        const chartContainer = document.createElement('div');
                        chartContainer.id = 'siagasChartContainer';
                        chartContainer.style.cssText = 'display: none; width: 100%; height: 400px; margin: 20px 0; position: relative;';

                        const canvas = document.createElement('canvas');
                        canvas.id = 'siagasChart';
                        canvas.style.cssText = 'width: 100% !important; height: 100% !important;';
                        chartContainer.appendChild(canvas);

                        // Inserir o container do gráfico DENTRO do dataContent
                        const dataContent = document.getElementById('siagasDataContent');
                        dataContent.appendChild(chartContainer);

                        return chartContainer;
                    })();

                    // Configurar e criar o gráfico
                    (function setupSiagasChart() {
                        const canvas = document.getElementById('siagasChart');
                        if (!canvas) return;

                        // Se já existir um gráfico, destruí-lo
                        if (window.siagasChartInstance) {
                            window.siagasChartInstance.destroy();
                        }

                        // Ordenar campos por valor (do maior para o menor)
                        const sortedFields = [...numericFields].sort((a, b) => b.value - a.value);

                        const labels = sortedFields.map(field => field.label);
                        const values = sortedFields.map(field => field.value);

                        // Gerar cores para o gráfico
                        const backgroundColors = (function generateColors(count) {
                            const colors = [];
                            const hueStep = 360 / count;

                            for (let i = 0; i < count; i++) {
                                const hue = (i * hueStep) % 360;
                                colors.push(`hsla(${hue}, 70%, 60%, 0.7)`);
                            }

                            return colors;
                        })(values.length);

                        // Obter unidade de medida para cada campo
                        const getUnit = function (fieldKey) {
                            const units = {
                                'cota_terre': 'm',
                                'profundi_1': 'm',
                                'nivel_agua': 'm',
                                'nivel_dina': 'm',
                                'nivel_esta': 'm',
                                'vazao': 'm³/h',
                                'vazao_espe': 'm³/h/m',
                                'vazao_livr': 'm³/h',
                                'vazao_esta': 'm³/h',
                                'temperatur': '°C',
                                'condutivid': 'µS/cm',
                                'turbidez': 'NTU',
                                'solidos_se': 'mL/L',
                                'solidos_su': 'mg/L'
                            };

                            return units[fieldKey] || '';
                        };

                        // Escolher o tipo de gráfico baseado na quantidade de dados
                        let chartType = 'bar';
                        let chartOptions = {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Valores Numéricos do Poço',
                                    font: { size: 16 }
                                },
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            const field = sortedFields[context.dataIndex];
                                            return `${field.label}: ${context.parsed.y.toLocaleString('pt-BR')} ${getUnit(field.key)}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Valores'
                                    },
                                    ticks: {
                                        callback: function (value) {
                                            return value.toLocaleString('pt-BR');
                                        }
                                    }
                                },
                                x: {
                                    ticks: {
                                        autoSkip: false,
                                        maxRotation: 45,
                                        minRotation: 45
                                    }
                                }
                            }
                        };

                        // Se tiver muitos dados, usar gráfico de linha
                        if (sortedFields.length > 8) {
                            chartType = 'line';
                            chartOptions.scales.x.ticks = {
                                autoSkip: true,
                                maxTicksLimit: 10
                            };
                        }

                        window.siagasChartInstance = new Chart(canvas.getContext('2d'), {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: values,
                                    backgroundColor: chartType === 'bar' ? backgroundColors : 'rgba(51, 136, 255, 0.5)',
                                    borderColor: chartType === 'bar' ? backgroundColors.map(c => c.replace('0.7', '1')) : '#3388ff',
                                    borderWidth: chartType === 'bar' ? 1 : 2,
                                    fill: chartType === 'line',
                                    tension: chartType === 'line' ? 0.1 : 0
                                }]
                            },
                            options: chartOptions
                        });
                    })();

                    // Função para mostrar tabela
                    function showSiagasTableView() {
                        const tableBtn = document.getElementById('siagasTableViewBtn');
                        const chartBtn = document.getElementById('siagasChartViewBtn');
                        const tableContainer = document.getElementById('siagasTableContainer');
                        const chartContainer = document.getElementById('siagasChartContainer');

                        if (tableBtn && chartBtn && tableContainer && chartContainer) {
                            tableBtn.className = 'siagas-view-btn active';
                            tableBtn.style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';

                            chartBtn.className = 'siagas-view-btn';
                            chartBtn.style.cssText = 'padding: 10px 20px; background: #ffffff; color: #242731; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';

                            tableContainer.style.display = 'block';
                            chartContainer.style.display = 'none';
                        }
                    }

                    // Função para mostrar gráfico
                    function showSiagasChartView() {
                        const tableBtn = document.getElementById('siagasTableViewBtn');
                        const chartBtn = document.getElementById('siagasChartViewBtn');
                        const tableContainer = document.getElementById('siagasTableContainer');
                        const chartContainer = document.getElementById('siagasChartContainer');

                        if (tableBtn && chartBtn && tableContainer && chartContainer) {
                            tableBtn.className = 'siagas-view-btn';
                            tableBtn.style.cssText = 'padding: 10px 20px; background: #ffffff; color: #333; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';

                            chartBtn.className = 'siagas-view-btn active';
                            chartBtn.style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';

                            tableContainer.style.display = 'none';
                            chartContainer.style.display = 'block';

                            // Garantir que o gráfico seja renderizado
                            if (window.siagasChartInstance) {
                                window.siagasChartInstance.update();
                            }
                        }
                    }

                    // Adicionar event listeners aos botões
                    document.getElementById('siagasTableViewBtn').onclick = showSiagasTableView;
                    document.getElementById('siagasChartViewBtn').onclick = showSiagasChartView;

                    // Mostrar tabela por padrão
                    showSiagasTableView();
                }

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
    const elements = {
        loading: document.getElementById('hidrowebQaLoadingSpinner'),
        tableContainer: document.getElementById('hidrowebQaTableContainer'),
        error: document.getElementById('hidrowebQaErrorMessage'),
        tableHeader: document.getElementById('hidrowebQaTableHeader'),
        tableBody: document.getElementById('hidrowebQaTableBody'),
        total: document.getElementById('hidrowebQaModalTotalReadings'),
        errorText: document.getElementById('hidrowebQaErrorText')
    };

    const modal = document.getElementById(hidrowebQaModalConfig.modalId);
    const modalStationCode = document.getElementById(hidrowebQaModalConfig.stationCodeId);

    modal.style.display = 'block';
    modalStationCode.innerHTML = stationCode + ' - ' + stationName +
        '<br><strong style="color: #3388ff;">Latitude:</strong> ' + latitude +
        ' | <strong style="color: #3388ff;">Longitude:</strong> ' + longitude;

    // Limpar estado anterior
    if (window.hidrowebQaChartInstance) {
        window.hidrowebQaChartInstance.destroy();
        window.hidrowebQaChartInstance = null;
    }

    document.querySelector('.hidroweb-qa-view-controls')?.remove();
    document.getElementById('hidrowebQaChartContainer')?.remove();
    document.getElementById('hidrowebQaChart')?.remove();

    // Resetar UI
    Object.values(elements).forEach(el => {
        if (el && el.style) {
            if (el === elements.loading) el.style.display = 'block';
            else if (el === elements.tableContainer) el.style.display = 'none';
            else if (el === elements.error) el.style.display = 'none';
            else if (el === elements.tableHeader) el.innerHTML = '';
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
            controlsContainer.children[0].className = 'hidroweb-qa-view-btn active';
            controlsContainer.children[0].style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            controlsContainer.children[1].className = 'hidroweb-qa-view-btn';
            controlsContainer.children[1].style.cssText = 'padding: 10px 20px; background: #ffffff; color: #242731; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            elements.tableContainer.style.display = 'block';
            chartContainer.style.display = 'none';
        },
        chart: () => {
            controlsContainer.children[0].className = 'hidroweb-qa-view-btn';
            controlsContainer.children[0].style.cssText = 'padding: 10px 20px; background: #ffffff; color: #333; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            controlsContainer.children[1].className = 'hidroweb-qa-view-btn active';
            controlsContainer.children[1].style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            elements.tableContainer.style.display = 'none';
            chartContainer.style.display = 'block';
            if (chartData && (!window.hidrowebQaChartInstance || window.hidrowebQaChartInstance.canvas.id !== 'hidrowebQaChart')) {
                createChart(chartData);
            }
        }
    };

    controlsContainer.children[0].onclick = views.table;
    controlsContainer.children[1].onclick = views.chart;

    // Buscar dados
    fetch(`/api/hidroweb-qualidade-agua/${stationCode}/readings`)
        .then(response => response.ok ? response.json() : Promise.reject('Erro ao buscar leituras'))
        .then(data => {
            elements.loading.style.display = 'none';

            if (data.success && data.data?.readings?.length > 0) {
                chartData = data.data.readings;
                elements.total.textContent = data.data.readings.length;

                // Criar cabeçalho da tabela
                const headerRow = document.createElement('tr');
                Object.keys(chartData[0]).forEach(key => {
                    const th = document.createElement('th');
                    th.textContent = key;
                    th.style.whiteSpace = 'nowrap';
                    headerRow.appendChild(th);
                });
                elements.tableHeader.appendChild(headerRow);

                // Criar linhas da tabela
                chartData.forEach(reading => {
                    const row = document.createElement('tr');
                    Object.keys(reading).forEach(key => {
                        const td = document.createElement('td');
                        const value = reading[key];
                        td.textContent = value !== null && value !== '' ? value : '-';
                        td.style.whiteSpace = 'nowrap';
                        row.appendChild(td);
                    });
                    elements.tableBody.appendChild(row);
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

    function createChartContainer() {
        const container = document.createElement('div');
        container.id = 'hidrowebQaChartContainer';
        container.style.cssText = 'display: none; width: 90%; height: 400px; margin: auto; position: relative;';

        const canvas = document.createElement('canvas');
        canvas.id = 'hidrowebQaChart';
        canvas.style.cssText = 'width: 90% !important; height: 90% !important;';
        container.appendChild(canvas);

        return container;
    }

    function createControlsContainer() {
        const container = document.createElement('div');
        container.className = 'hidroweb-qa-view-controls';
        container.style.cssText = 'margin: 20px 0; display: flex; justify-content: center; gap: 15px; padding: 15px;';

        ['TABELA', 'GRÁFICO'].forEach((text, i) => {
            const btn = document.createElement('button');
            btn.id = i === 0 ? 'hidrowebQaTableViewBtn' : 'hidrowebQaChartViewBtn';
            btn.textContent = text;
            btn.className = i === 0 ? 'hidroweb-qa-view-btn active' : 'hidroweb-qa-view-btn';
            btn.style.cssText = `padding: 10px 20px; background: ${i === 0 ? '#242731' : '#ffffff'}; color: ${i === 0 ? 'white' : '#242731'}; border: ${i === 0 ? 'none' : '1px solid #79808F'}; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;`;
            container.appendChild(btn);
        });

        return container;
    }

    function createChart(readings) {
        if (window.hidrowebQaChartInstance) {
            window.hidrowebQaChartInstance.destroy();
            window.hidrowebQaChartInstance = null;
        }

        const canvas = document.getElementById('hidrowebQaChart');
        if (!canvas) {
            console.error('Canvas não encontrado!');
            return;
        }

        // Analisar variáveis numéricas disponíveis para gráficos
        const numericFields = (function extractNumericFields(readings) {
            const numericFields = [];
            const usedKeys = new Set();

            // Ignorar campos não numéricos
            const ignoreFields = ['id', 'station_code', 'data_hora_dado', 'data_ultima_alteracao',
                'nivel_consistencia', 'num_medicao', 'posicao_horizontal_coleta',
                'posicao_vertical_coleta', 'profundidade_m', 'choveu', 'created_at',
                'updated_at', 'deleted_at'];

            // Analisar o primeiro registro para encontrar campos numéricos
            if (readings.length > 0) {
                Object.keys(readings[0]).forEach(key => {
                    // Ignorar campos de status (_status) e campos não numéricos
                    if (ignoreFields.includes(key) || key.endsWith('_status') ||
                        key.includes('deleted') || key.includes('created') || key.includes('updated')) {
                        return;
                    }

                    // Verificar se o campo tem valores numéricos em pelo menos um registro
                    for (let i = 0; i < Math.min(5, readings.length); i++) {
                        const value = readings[i][key];
                        if (value !== null && value !== '' && value !== '-' && !isNaN(parseFloat(value))) {
                            const numValue = parseFloat(value);
                            if (!isNaN(numValue)) {
                                // Extrair nome do parâmetro (remove números iniciais)
                                let displayName = key.replace(/^\d+_/, '').replace(/_/g, ' ');

                                // Formatar nome para exibição
                                displayName = displayName.split('_').map(word => {
                                    if (word.length <= 3) return word.toUpperCase();
                                    return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
                                }).join(' ');

                                numericFields.push({
                                    key: key,
                                    label: displayName,
                                    unit: extractUnit(key),
                                    values: readings.map(r => {
                                        const val = parseFloat(r[key]);
                                        return isNaN(val) ? null : val;
                                    })
                                });
                                usedKeys.add(key);
                                break;
                            }
                        }
                    }
                });
            }

            // Filtrar apenas campos que têm pelo menos 2 valores numéricos
            return numericFields.filter(field => {
                const validValues = field.values.filter(v => v !== null);
                return validValues.length >= 2;
            });

            function extractUnit(fieldKey) {
                // Extrair unidade do nome do campo
                if (fieldKey.includes('_mgl_')) {
                    return 'mg/L';
                } else if (fieldKey.includes('_ugl')) {
                    return 'µg/L';
                } else if (fieldKey.includes('_us_cm')) {
                    return 'µS/cm';
                } else if (fieldKey.includes('_ntu')) {
                    return 'NTU';
                } else if (fieldKey.includes('_c')) {
                    return '°C';
                } else if (fieldKey.includes('_m')) {
                    return 'm';
                } else if (fieldKey.includes('_m3s')) {
                    return 'm³/s';
                } else if (fieldKey.includes('_perc')) {
                    return '%';
                } else if (fieldKey.includes('_ufc') || fieldKey.includes('_nmp')) {
                    return 'UFC/100mL';
                } else if (fieldKey.includes('_celulas')) {
                    return 'células/100mL';
                }
                return '';
            }
        })(readings);

        // Se não houver dados numéricos suficientes
        if (numericFields.length === 0) {
            chartContainer.innerHTML = '<div style="text-align: center; padding: 50px; color: #666;">Não há dados numéricos suficientes para exibir o gráfico.</div>';
            return;
        }

        // Extrair datas para o eixo X
        const dates = readings.map(r => {
            if (r.data_hora_dado) {
                try {
                    const date = new Date(r.data_hora_dado);
                    return date.toLocaleDateString('pt-BR');
                } catch (e) {
                    return r.data_hora_dado;
                }
            }
            return '';
        });

        // Selecionar os parâmetros mais interessantes para o gráfico
        const interestingParams = [
            'ph', 'temperatura_amostra_c', 'od_mgl_02', 'condutividade_especifica_25oc_us_cm_a_25c',
            'turbidez_ntu', 'dbo_mgl_02', 'dqo_mgl_02', 'fosforo_total_mgl',
            'nitratos_mgl_n', 'nitrogenio_amoniacal_mgl'
        ];

        // Filtrar campos disponíveis que estão na lista de interessantes
        const selectedFields = numericFields.filter(field =>
            interestingParams.some(param => field.key.includes(param))
        ).slice(0, 5); // Limitar a 5 parâmetros

        // Se não encontrou parâmetros interessantes, pegar os primeiros 5
        if (selectedFields.length === 0) {
            selectedFields.push(...numericFields.slice(0, 5));
        }

        // Criar datasets para o gráfico
        const datasets = selectedFields.map((field, index) => {
            const colors = ['#3388ff', '#ff5733', '#33ff57', '#ff33a1', '#33fff6'];
            return {
                label: `${field.label} ${field.unit ? `(${field.unit})` : ''}`,
                data: field.values,
                borderColor: colors[index % colors.length],
                backgroundColor: colors[index % colors.length].replace(')', ', 0.1)').replace('rgb', 'rgba'),
                borderWidth: 2,
                tension: 0.1,
                fill: false,
                yAxisID: `y${index}`
            };
        });

        // Configurar escalas
        const scales = {
            x: {
                title: {
                    display: true,
                    text: 'Data da Coleta'
                },
                ticks: {
                    maxTicksLimit: 10,
                    autoSkip: true
                }
            }
        };

        // Adicionar eixo Y para cada dataset
        datasets.forEach((dataset, index) => {
            scales[`y${index}`] = {
                type: 'linear',
                display: true,
                position: index === 0 ? 'left' : 'right',
                title: {
                    display: true,
                    text: dataset.label.split('(')[0].trim()
                },
                grid: {
                    drawOnChartArea: index === 0 // Apenas o primeiro eixo mostra grid
                }
            };
        });

        window.hidrowebQaChartInstance = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: dates,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: `Estação ${stationCode} - Parâmetros de Qualidade da Água`,
                        font: { size: 16 }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            title: function (context) {
                                const index = context[0].dataIndex;
                                if (readings[index]?.data_hora_dado) {
                                    try {
                                        const date = new Date(readings[index].data_hora_dado);
                                        return date.toLocaleString('pt-BR', {
                                            day: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            hour12: false
                                        });
                                    } catch (e) {
                                        return readings[index].data_hora_dado;
                                    }
                                }
                                return '';
                            }
                        }
                    }
                },
                scales: scales,
                interaction: {
                    intersect: false,
                    mode: 'nearest'
                }
            }
        });
    }
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
    const elements = {
        loading: document.getElementById('lrgsLoadingSpinner'),
        tableContainer: document.getElementById('lrgsTableContainer'),
        error: document.getElementById('lrgsErrorMessage'),
        tableHeader: document.getElementById('lrgsTableHeader'),
        tableBody: document.getElementById('lrgsTableBody'),
        total: document.getElementById('lrgsModalTotalReadings'),
        errorText: document.getElementById('lrgsErrorText')
    };

    const modal = document.getElementById(lrgsModalConfig.modalId);
    const modalStationCode = document.getElementById(lrgsModalConfig.stationCodeId);

    modal.style.display = 'block';
    modalStationCode.innerHTML = stationCode + ' - ' + stationName +
        '<br><strong style="color: #3388ff;">Latitude:</strong> ' + latitude +
        ' | <strong style="color: #3388ff;">Longitude:</strong> ' + longitude;

    // Limpar estado anterior
    if (window.lrgsChartInstance) {
        window.lrgsChartInstance.destroy();
        window.lrgsChartInstance = null;
    }

    document.querySelector('.lrgs-view-controls')?.remove();
    document.getElementById('lrgsChartContainer')?.remove();
    document.getElementById('lrgsChart')?.remove();

    // Resetar UI
    Object.values(elements).forEach(el => {
        if (el && el.style) {
            if (el === elements.loading) el.style.display = 'block';
            else if (el === elements.tableContainer) el.style.display = 'none';
            else if (el === elements.error) el.style.display = 'none';
            else if (el === elements.tableHeader) el.innerHTML = '';
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
            controlsContainer.children[0].className = 'lrgs-view-btn active';
            controlsContainer.children[0].style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            controlsContainer.children[1].className = 'lrgs-view-btn';
            controlsContainer.children[1].style.cssText = 'padding: 10px 20px; background: #ffffff; color: #242731; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            elements.tableContainer.style.display = 'block';
            chartContainer.style.display = 'none';
        },
        chart: () => {
            controlsContainer.children[0].className = 'lrgs-view-btn';
            controlsContainer.children[0].style.cssText = 'padding: 10px 20px; background: #ffffff; color: #333; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            controlsContainer.children[1].className = 'lrgs-view-btn active';
            controlsContainer.children[1].style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            elements.tableContainer.style.display = 'none';
            chartContainer.style.display = 'block';
            if (chartData && (!window.lrgsChartInstance || window.lrgsChartInstance.canvas.id !== 'lrgsChart')) {
                createChart(chartData);
            }
        }
    };

    controlsContainer.children[0].onclick = views.table;
    controlsContainer.children[1].onclick = views.chart;

    // Buscar dados
    fetch(`/api/lrgs-client/${stationCode}/readings`)
        .then(response => response.ok ? response.json() : Promise.reject('Erro ao buscar leituras'))
        .then(data => {
            elements.loading.style.display = 'none';

            if (data.success && data.data?.readings?.length > 0) {
                chartData = data.data.readings;
                elements.total.textContent = data.data.readings.length;

                // Criar cabeçalho da tabela
                const headerRow = document.createElement('tr');
                Object.keys(chartData[0]).forEach(key => {
                    const th = document.createElement('th');
                    th.textContent = key;
                    th.style.whiteSpace = 'nowrap';
                    headerRow.appendChild(th);
                });
                elements.tableHeader.appendChild(headerRow);

                // Criar linhas da tabela
                chartData.forEach(reading => {
                    const row = document.createElement('tr');
                    Object.keys(reading).forEach(key => {
                        const td = document.createElement('td');
                        const value = reading[key];
                        td.textContent = value !== null && value !== '' ? value : '-';
                        td.style.whiteSpace = 'nowrap';
                        row.appendChild(td);
                    });
                    elements.tableBody.appendChild(row);
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

    function createChartContainer() {
        const container = document.createElement('div');
        container.id = 'lrgsChartContainer';
        container.style.cssText = 'display: none; width: 90%; height: 400px; margin: auto; position: relative;';

        const canvas = document.createElement('canvas');
        canvas.id = 'lrgsChart';
        canvas.style.cssText = 'width: 90% !important; height: 90% !important;';
        container.appendChild(canvas);

        return container;
    }

    function createControlsContainer() {
        const container = document.createElement('div');
        container.className = 'lrgs-view-controls';
        container.style.cssText = 'margin: 20px 0; display: flex; justify-content: center; gap: 15px; padding: 15px;';

        ['TABELA', 'GRÁFICO'].forEach((text, i) => {
            const btn = document.createElement('button');
            btn.id = i === 0 ? 'lrgsTableViewBtn' : 'lrgsChartViewBtn';
            btn.textContent = text;
            btn.className = i === 0 ? 'lrgs-view-btn active' : 'lrgs-view-btn';
            btn.style.cssText = `padding: 10px 20px; background: ${i === 0 ? '#242731' : '#ffffff'}; color: ${i === 0 ? 'white' : '#242731'}; border: ${i === 0 ? 'none' : '1px solid #79808F'}; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;`;
            container.appendChild(btn);
        });

        return container;
    }

    function createChart(readings) {
        if (window.lrgsChartInstance) {
            window.lrgsChartInstance.destroy();
            window.lrgsChartInstance = null;
        }

        const canvas = document.getElementById('lrgsChart');
        if (!canvas) {
            console.error('Canvas não encontrado!');
            return;
        }

        // Analisar variáveis numéricas disponíveis para gráficos
        const numericFields = (function extractNumericFields(readings) {
            const numericFields = [];
            const usedKeys = new Set();

            // Ignorar campos não numéricos
            const ignoreFields = ['id', 'dcp_station_id', 'raw_header', 'address', 'failure_code',
                'signal_strength', 'modulation_index', 'data_quality', 'channel',
                'spacecraft', 'reception_source', 'display_value', 'door_sensor_open',
                'serial_number', 'program_signature', 'operating_system_version',
                'transmitter_serial_number', 'firmware_version', 'goes_antenna_signal',
                'program_version', 'restart_time', 'sensor_type', 'extra',
                'created_at', 'updated_at', 'deleted_at'];

            // Analisar o primeiro registro para encontrar campos numéricos
            if (readings.length > 0) {
                Object.keys(readings[0]).forEach(key => {
                    // Ignorar campos não numéricos
                    if (ignoreFields.includes(key) ||
                        key.includes('created') || key.includes('updated') || key.includes('deleted')) {
                        return;
                    }

                    // Verificar se o campo tem valores numéricos em pelo menos um registro
                    for (let i = 0; i < Math.min(5, readings.length); i++) {
                        const value = readings[i][key];
                        if (value !== null && value !== '' && value !== '-' && !isNaN(parseFloat(value))) {
                            const numValue = parseFloat(value);
                            if (!isNaN(numValue)) {
                                // Formatar nome para exibição
                                let displayName = key.replace(/_/g, ' ');
                                displayName = displayName.split(' ').map(word => {
                                    if (word.length <= 3) return word.toUpperCase();
                                    return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
                                }).join(' ');

                                numericFields.push({
                                    key: key,
                                    label: displayName,
                                    unit: extractUnit(key),
                                    values: readings.map(r => {
                                        const val = parseFloat(r[key]);
                                        return isNaN(val) ? null : val;
                                    })
                                });
                                usedKeys.add(key);
                                break;
                            }
                        }
                    }
                });
            }

            // Filtrar campos com valores numéricos
            return numericFields.filter(field => {
                const validValues = field.values.filter(v => v !== null);
                return validValues.length >= 1;
            });

            function extractUnit(fieldKey) {
                // Extrair unidade do nome do campo
                if (fieldKey.includes('water_level') || fieldKey.includes('level_adjustment')) {
                    return 'm';
                } else if (fieldKey.includes('rain')) {
                    return 'mm';
                } else if (fieldKey.includes('temperature')) {
                    return '°C';
                } else if (fieldKey.includes('battery_voltage')) {
                    return 'V';
                } else if (fieldKey.includes('atmospheric_pressure')) {
                    return 'hPa';
                } else if (fieldKey.includes('frequency_offset')) {
                    return 'Hz';
                }
                return '';
            }
        })(readings);

        // Se não houver dados numéricos suficientes
        if (numericFields.length === 0) {
            chartContainer.innerHTML = '<div style="text-align: center; padding: 50px; color: #666;">Não há dados numéricos suficientes para exibir o gráfico.</div>';
            return;
        }

        // Extrair data/hora para o eixo X (criar timestamp a partir dos campos separados)
        const timestamps = readings.map(r => {
            try {
                // Usar year, julian_day, hour, minute, second para criar data
                if (r.year && r.julian_day && r.hour !== undefined && r.minute !== undefined) {
                    // Converter dia juliano para data normal
                    const date = new Date(parseInt(r.year), 0); // 1º de janeiro do ano
                    const julianDay = parseInt(r.julian_day) - 1; // Ajustar porque 1º de janeiro é dia 1
                    date.setDate(date.getDate() + julianDay);
                    date.setHours(parseInt(r.hour) || 0, parseInt(r.minute) || 0, parseInt(r.second) || 0);

                    return date.toLocaleString('pt-BR', {
                        day: '2-digit',
                        month: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            } catch (e) {
                console.error('Erro ao processar data:', e);
            }
            return '';
        });

        // Selecionar os parâmetros mais interessantes para o gráfico
        const interestingParams = [
            'water_level', 'rain', 'water_temperature', 'battery_voltage',
            'atmospheric_pressure', 'internal_temperature'
        ];

        // Filtrar campos disponíveis que estão na lista de interessantes
        const selectedFields = numericFields.filter(field =>
            interestingParams.some(param => field.key.includes(param))
        ).slice(0, 5); // Limitar a 5 parâmetros

        // Se não encontrou parâmetros interessantes, pegar os primeiros 5
        if (selectedFields.length === 0) {
            selectedFields.push(...numericFields.slice(0, 5));
        }

        // Criar datasets para o gráfico
        const datasets = selectedFields.map((field, index) => {
            const colors = ['#3388ff', '#ff5733', '#33ff57', '#ff33a1', '#33fff6'];
            return {
                label: `${field.label} ${field.unit ? `(${field.unit})` : ''}`,
                data: field.values,
                borderColor: colors[index % colors.length],
                backgroundColor: colors[index % colors.length].replace(')', ', 0.1)').replace('rgb', 'rgba'),
                borderWidth: 2,
                tension: 0.1,
                fill: false,
                yAxisID: `y${index}`
            };
        });

        // Configurar escalas
        const scales = {
            x: {
                title: {
                    display: true,
                    text: 'Data/Hora'
                },
                ticks: {
                    maxTicksLimit: 10,
                    autoSkip: true
                }
            }
        };

        // Adicionar eixo Y para cada dataset
        datasets.forEach((dataset, index) => {
            scales[`y${index}`] = {
                type: 'linear',
                display: true,
                position: index === 0 ? 'left' : 'right',
                title: {
                    display: true,
                    text: dataset.label.split('(')[0].trim()
                },
                grid: {
                    drawOnChartArea: index === 0 // Apenas o primeiro eixo mostra grid
                }
            };
        });

        window.lrgsChartInstance = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: timestamps,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: `Estação ${stationCode} - Dados LRGS`,
                        font: { size: 16 }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            title: function (context) {
                                const index = context[0].dataIndex;
                                const reading = readings[index];

                                try {
                                    if (reading.year && reading.julian_day && reading.hour !== undefined && reading.minute !== undefined) {
                                        const date = new Date(parseInt(reading.year), 0);
                                        const julianDay = parseInt(reading.julian_day) - 1;
                                        date.setDate(date.getDate() + julianDay);
                                        date.setHours(parseInt(reading.hour) || 0, parseInt(reading.minute) || 0, parseInt(reading.second) || 0);

                                        return date.toLocaleString('pt-BR', {
                                            day: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            second: '2-digit',
                                            hour12: false
                                        });
                                    }
                                } catch (e) {
                                    console.error('Erro no tooltip:', e);
                                }

                                return `Registro ${index + 1}`;
                            }
                        }
                    }
                },
                scales: scales,
                interaction: {
                    intersect: false,
                    mode: 'nearest'
                }
            }
        });
    }
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
