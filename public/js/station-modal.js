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
    closeButtonId: 'closeRimasModal'
};

function openRimasReadingsModal(idPonto, stationName, latitude, longitude) {
    const elements = {
        loading: document.getElementById('rimasLoadingSpinner'),
        tableContainer: document.getElementById('rimasReadingsTableContainer'),
        error: document.getElementById('rimasErrorMessage'),
        tableBody: document.getElementById('rimasReadingsTableBody'),
        total: document.getElementById('rimasModalTotalReadings'),
        errorText: document.getElementById('rimasErrorText')
    };

    const modal = document.getElementById(rimasModalConfig.modalId);
    const modalIdPonto = document.getElementById(rimasModalConfig.idPontoId);

    modal.style.display = 'block';
    modalIdPonto.innerHTML = `
        <div class="rimas-header-content-title">
            <h2 style="margin: 0;">Série temporal de níveis estáticos</h2>
            <p style="margin: 0;">${stationName} - Registros históricos de medição na rede RIMAS/CPRM.</p>
        </div>
    `;

    // Limpar estado anterior
    if (window.rimasChartInstance) {
        window.rimasChartInstance.destroy();
        window.rimasChartInstance = null;
    }

    document.querySelector('.rimas-view-controls')?.remove();
    document.getElementById('rimasChartContainer')?.remove();

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

    // Inserir no DOM
    const modalContent = document.querySelector('.rimas-modal-content');
    if (modalContent) {
        modalContent.insertBefore(chartContainer, elements.tableContainer);
        modalContent.insertBefore(controlsContainer, elements.tableContainer.nextSibling);
    }

    let chartData = null;

    // Configurar visualizações
    const views = {
        table: () => {
            controlsContainer.children[0].className = 'rimas-view-btn active';
            controlsContainer.children[0].style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            controlsContainer.children[1].className = 'rimas-view-btn';
            controlsContainer.children[1].style.cssText = 'padding: 10px 20px; background: #ffffff; color: #242731; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            elements.tableContainer.style.display = 'block';
            chartContainer.style.display = 'none';
        },
        chart: () => {
            controlsContainer.children[0].className = 'rimas-view-btn';
            controlsContainer.children[0].style.cssText = 'padding: 10px 20px; background: #ffffff; color: #333; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            controlsContainer.children[1].className = 'rimas-view-btn active';
            controlsContainer.children[1].style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            elements.tableContainer.style.display = 'none';
            chartContainer.style.display = 'block';
            if (chartData && (!window.rimasChartInstance || window.rimasChartInstance.canvas.id !== 'rimasChart')) {
                createChart(chartData, idPonto, chartContainer);
            }
        }
    };

    controlsContainer.children[0].onclick = views.table;
    controlsContainer.children[1].onclick = views.chart;

    // Buscar dados
    fetch(`/api/pocos-rimas/${idPonto}/readings`)
        .then(response => response.ok ? response.json() : Promise.reject('Erro ao buscar leituras'))
        .then(data => {
            elements.loading.style.display = 'none';

            if (data.success && data.data?.readings?.length > 0) {
                chartData = data.data.readings;
                elements.total.textContent = chartData.length;

                // Criar linhas da tabela
                chartData.forEach(reading => {
                    const row = document.createElement('tr');

                    // Número da medição
                    const tdNum = document.createElement('td');
                    tdNum.textContent = reading.numero_de || '-';
                    row.appendChild(tdNum);

                    // Data
                    const tdDate = document.createElement('td');
                    if (reading.data_da_me) {
                        try {
                            const date = new Date(reading.data_da_me);
                            tdDate.textContent = date.toLocaleDateString('pt-BR');
                        } catch (e) {
                            tdDate.textContent = reading.data_da_me;
                        }
                    } else {
                        tdDate.textContent = '-';
                    }
                    row.appendChild(tdDate);

                    // Hora
                    const tdTime = document.createElement('td');
                    if (reading.hora_da_me) {
                        try {
                            const time = reading.hora_da_me.split(' ')[1] || reading.hora_da_me;
                            tdTime.textContent = time.substring(0, 8); // HH:MM:SS
                        } catch (e) {
                            tdTime.textContent = reading.hora_da_me;
                        }
                    } else {
                        tdTime.textContent = '-';
                    }
                    row.appendChild(tdTime);

                    // Nível da água
                    const tdLevel = document.createElement('td');
                    if (reading.nivel_da_a) {
                        const level = parseFloat(reading.nivel_da_a);
                        tdLevel.textContent = isNaN(level) ? '-' : level.toFixed(2);
                    } else {
                        tdLevel.textContent = '-';
                    }
                    row.appendChild(tdLevel);

                    // Observação
                    const tdObs = document.createElement('td');
                    tdObs.textContent = reading.field_8 || '-';
                    row.appendChild(tdObs);

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
        container.id = 'rimasChartContainer';
        container.style.cssText = 'display: none; width: 92%; height: 400px; margin: 20px auto; position: relative;';

        const canvas = document.createElement('canvas');
        canvas.id = 'rimasChart';
        canvas.style.cssText = 'width: 100% !important; height: 100% !important;';
        container.appendChild(canvas);

        return container;
    }

    function createControlsContainer() {
        const container = document.createElement('div');
        container.className = 'rimas-view-controls';
        container.style.cssText = 'margin: 20px auto; width: 92%; display: flex; justify-content: center; gap: 15px; padding: 15px;';

        ['TABELA', 'GRÁFICO'].forEach((text, i) => {
            const btn = document.createElement('button');
            btn.id = i === 0 ? 'rimasTableViewBtn' : 'rimasChartViewBtn';
            btn.textContent = text;
            btn.className = i === 0 ? 'rimas-view-btn active' : 'rimas-view-btn';
            btn.style.cssText = `padding: 10px 20px; background: ${i === 0 ? '#242731' : '#ffffff'}; color: ${i === 0 ? 'white' : '#242731'}; border: ${i === 0 ? 'none' : '1px solid #79808F'}; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;`;
            container.appendChild(btn);
        });

        return container;
    }

    function createChart(readings, idPonto, chartContainer) {
        if (window.rimasChartInstance) {
            window.rimasChartInstance.destroy();
            window.rimasChartInstance = null;
        }

        const canvas = document.getElementById('rimasChart');
        if (!canvas) {
            console.error('Canvas não encontrado!');
            return;
        }

        // Extrair dados para o gráfico
        const processedData = readings
            .filter(r => r.nivel_da_a && !isNaN(parseFloat(r.nivel_da_a)))
            .map(r => ({
                dateTime: combineDateTime(r.data_da_me, r.hora_da_me),
                level: parseFloat(r.nivel_da_a),
                numero: r.numero_de
            }))
            .sort((a, b) => new Date(a.dateTime) - new Date(b.dateTime)); // Ordenar por data

        if (processedData.length < 2) {
            chartContainer.innerHTML = '<div style="text-align: center; padding: 50px; color: #666;">Dados insuficientes para gráfico.</div>';
            return;
        }

        // Preparar labels e dados
        const labels = processedData.map(d => {
            try {
                const date = new Date(d.dateTime);
                return date.toLocaleDateString('pt-BR');
            } catch (e) {
                return d.numero || '';
            }
        });

        const levels = processedData.map(d => d.level);

        window.rimasChartInstance = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Nível da Água (m)',
                    data: levels,
                    borderColor: '#ff7800',
                    backgroundColor: 'rgba(255, 120, 0, 0.1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true,
                    pointBackgroundColor: '#ff7800',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: `Poço RIMAS ${idPonto} - Variação do Nível da Água`,
                        font: { size: 16 }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            title: function (context) {
                                const index = context[0].dataIndex;
                                const data = processedData[index];
                                if (data && data.dateTime) {
                                    try {
                                        const date = new Date(data.dateTime);
                                        return date.toLocaleString('pt-BR', {
                                            day: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            hour12: false
                                        });
                                    } catch (e) {
                                        return data.numero || `Medição ${index + 1}`;
                                    }
                                }
                                return `Medição ${index + 1}`;
                            },
                            label: function (context) {
                                return `Nível: ${context.parsed.y.toFixed(2)} m`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Data'
                        },
                        ticks: {
                            maxTicksLimit: 15,
                            autoSkip: true
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Nível da Água (m)'
                        },
                        beginAtZero: false,
                        ticks: {
                            callback: function (value) {
                                return value.toFixed(2) + ' m';
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'nearest'
                }
            }
        });
    }

    function combineDateTime(dateStr, timeStr) {
        try {
            if (!dateStr || !timeStr) return null;

            // Formatar data
            let datePart;
            if (dateStr.includes('/')) {
                // Formato DD/MM/AAAA
                const [day, month, year] = dateStr.split('/');
                datePart = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
            } else {
                // Assumir formato ISO
                datePart = dateStr.split('T')[0];
            }

            // Formatar hora
            let timePart;
            if (timeStr.includes(':')) {
                const timeParts = timeStr.split(':');
                timePart = `${timeParts[0].padStart(2, '0')}:${timeParts[1].padStart(2, '0')}:${(timeParts[2] || '00').padStart(2, '0')}`;
            } else {
                timePart = '00:00:00';
            }

            return `${datePart}T${timePart}`;
        } catch (e) {
            console.error('Erro ao combinar data/hora:', e);
            return null;
        }
    }
}

// Event listener para fechar modal
document.getElementById('closeRimasModal')?.addEventListener('click', function () {
    document.getElementById('rimasReadingsModal').style.display = 'none';
    if (window.rimasChartInstance) {
        window.rimasChartInstance.destroy();
        window.rimasChartInstance = null;
    }
});

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

                // 1. Primeiro renderizar a tabela EM DUAS COLUNAS
                const entries = Object.entries(poco).filter(([key, value]) =>
                    value !== null && value !== '' && value !== undefined
                );

                const totalItems = entries.length;
                const itemsPerColumn = Math.ceil(totalItems / 2);

                let html = `
                    <div id="siagasTableContainer" class="siagas-table-container">
                        <div class="siagas-table-column">
                `;

                // Primeira coluna
                for (let i = 0; i < itemsPerColumn; i++) {
                    const [key, value] = entries[i];
                    html += `
                        <div class="siagas-data-row">
                            <div class="siagas-data-label" style="text-transform: capitalize;">${formatSiagasKey(key)}:</div>
                            <div class="siagas-data-value">${value !== null && value !== '' ? value : '-'}</div>
                        </div>
                    `;
                }

                html += `
                        </div>
                        <div class="siagas-table-column">
                `;

                // Segunda coluna
                for (let i = itemsPerColumn; i < totalItems; i++) {
                    const [key, value] = entries[i];
                    html += `
                        <div class="siagas-data-row">
                            <div class="siagas-data-label" style="text-transform: capitalize;">${formatSiagasKey(key)}:</div>
                            <div class="siagas-data-value">${value !== null && value !== '' ? value : '-'}</div>
                        </div>
                    `;
                }

                html += `
                        </div>
                    </div>
                `;

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
                                        return labels[field] || formatSiagasKey(field);
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

                            tableContainer.style.display = 'flex'; // Alterado para 'flex'
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

    // Função auxiliar para formatar chaves do SIAGAS
    function formatSiagasKey(key) {
        // Primeiro, tentar mapear para nomes mais legíveis
        const labels = {
            'cota_terre': 'Altitude do Terreno',
            'profundi_1': 'Profundidade Total',
            'nivel_agua': 'Nível d\'Água',
            'vazao': 'Vazão',
            'nivel_dina': 'Nível Dinâmico',
            'nivel_esta': 'Nível Estático',
            'vazao_espe': 'Vazão Específica',
            'vazao_livr': 'Vazão Livre',
            'coeficient': 'Coeficiente de Armazenamento',
            'permeabili': 'Permeabilidade',
            'transmissi': 'Transmissividade',
            'vazao_esta': 'Vazão Estabilizada',
            'condutivid': 'Condutividade Elétrica',
            'temperatur': 'Temperatura',
            'turbidez': 'Turbidez',
            'solidos_se': 'Sólidos Sedimentáveis',
            'solidos_su': 'Sólidos Suspendidos',
            'cod_siagas': 'Código SIAGAS',
            'data_perfu': 'Data de Perfuração',
            'tipo_poço': 'Tipo de Poço',
            'tipologia': 'Tipologia',
            'cod_orgao': 'Código Órgão',
            'nome_orgao': 'Nome Órgão',
            'aquifero': 'Aquífero',
            'municipio': 'Município',
            'uf': 'UF',
            'latitude': 'Latitude',
            'longitude': 'Longitude'
        };

        if (labels[key]) {
            return labels[key];
        }

        // Se não estiver no mapeamento, formatação padrão
        return key
            .replace(/_/g, ' ')
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
    }
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
    modalStationCode.innerHTML = `
        <div class="hidroweb-qa-header-content-title">
            <h2 style="margin: 0;">${stationName}</h2>
            <p style="margin: 0;">Parâmetros físico-químicos, biológicos e contaminantes medidos na estação de monitoramento.</p>
        </div>
    `;

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
        container.style.cssText = 'display: none; width: 90%; height: 375px; margin: auto; position: relative;';

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

// Configuração específica para LRGS tabela e gráfico
function openLrgsReadingsModal(stationCode, stationName, latitude, longitude) {
    const elements = {
        loading: document.getElementById('lrgsLoadingSpinner'),
        tableContainer: document.getElementById('lrgsTableContainer'),
        error: document.getElementById('lrgsErrorMessage'),
        tableHeader: document.getElementById('lrgsTableHeader'),
        tableBody: document.getElementById('lrgsTableBody'),
        total: document.getElementById('lrgsModalTotalReadings'),
        errorText: document.getElementById('lrgsErrorText'),
        stationLocation: document.getElementById('lrgsStationLocation')
    };

    const modal = document.getElementById(lrgsModalConfig.modalId);
    const modalStationCode = document.getElementById(lrgsModalConfig.stationCodeId);

    modal.style.display = 'block';
    modalStationCode.innerHTML = `
        <div class="lrgs-header-content-title">
            <h2 style="margin: 0;">${stationName}</h2>
            <p style="margin: 0;">Dados de transmissão via satélite, níveis d'água em tempo quase-real e parâmetros operacionais das estações.</p>
        </div>
    `;

    if (elements.stationLocation) {
        elements.stationLocation.textContent = `Latitude: ${latitude} | Longitude: ${longitude}`;
    }

    if (window.lrgsChartInstance) {
        window.lrgsChartInstance.destroy();
        window.lrgsChartInstance = null;
    }

    document.querySelector('.lrgs-view-controls')?.remove();
    document.getElementById('lrgsChartContainer')?.remove();
    document.getElementById('lrgsChart')?.remove();
    document.querySelector('.lrgs-filter-container')?.remove();

    Object.values(elements).forEach(el => {
        if (el && el.style) {
            if (el === elements.loading) el.style.display = 'block';
            else if (el === elements.tableContainer) el.style.display = 'none';
            else if (el === elements.error) el.style.display = 'none';
            else if (el === elements.tableHeader) el.innerHTML = '';
            else if (el === elements.tableBody) el.innerHTML = '';
        }
    });

    const chartContainer = createChartContainer();
    const controlsContainer = createControlsContainer();
    const filterContainer = createFilterContainer();

    elements.tableContainer.parentNode.insertBefore(chartContainer, elements.tableContainer);
    elements.tableContainer.parentNode.insertBefore(controlsContainer, elements.tableContainer.nextSibling);
    elements.tableContainer.parentNode.insertBefore(filterContainer, chartContainer);

    let chartData = null;
    let filteredData = null;

    const views = {
        table: () => {
            controlsContainer.children[0].className = 'lrgs-view-btn active';
            controlsContainer.children[0].style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            controlsContainer.children[1].className = 'lrgs-view-btn';
            controlsContainer.children[1].style.cssText = 'padding: 10px 20px; background: #ffffff; color: #242731; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            elements.tableContainer.style.display = 'block';
            chartContainer.style.display = 'none';
            filterContainer.style.display = 'none';
        },
        chart: () => {
            controlsContainer.children[0].className = 'lrgs-view-btn';
            controlsContainer.children[0].style.cssText = 'padding: 10px 20px; background: #ffffff; color: #333; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            controlsContainer.children[1].className = 'lrgs-view-btn active';
            controlsContainer.children[1].style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            elements.tableContainer.style.display = 'none';
            chartContainer.style.display = 'block';
            filterContainer.style.display = 'flex';
            if (chartData) {
                filteredData = chartData;
                createChart(filteredData);
            }
        }
    };

    controlsContainer.children[0].onclick = views.table;
    controlsContainer.children[1].onclick = views.chart;

    fetch(`/api/lrgs-client/${stationCode}/readings`)
        .then(response => response.ok ? response.json() : Promise.reject('Erro ao buscar leituras'))
        .then(data => {
            elements.loading.style.display = 'none';

            if (data.success && data.data?.readings?.length > 0) {
                chartData = data.data.readings;
                filteredData = chartData;
                const isAdmin = data.is_admin === true;

                elements.total.textContent = data.data.readings.length;

                const BASIC_FIELDS = [
                    'reading_datetime',
                    'water_level_60min', 'water_level_45min', 'water_level_30min', 'water_level_15min', 'flow_15min',
                    'rain_60min', 'rain_45min', 'rain_30min', 'rain_15min',
                    'water_temperature', 'atmospheric_pressure'
                ];

                const fieldsToShow = BASIC_FIELDS.filter(f => chartData[0].hasOwnProperty(f));

                const headerRow = document.createElement('tr');
                fieldsToShow.forEach(key => {
                    const th = document.createElement('th');
                    th.textContent = key;
                    th.style.whiteSpace = 'nowrap';
                    headerRow.appendChild(th);
                });
                if (isAdmin) {
                    const th = document.createElement('th');
                    th.textContent = 'Ações';
                    th.style.whiteSpace = 'nowrap';
                    headerRow.appendChild(th);
                }
                elements.tableHeader.appendChild(headerRow);

                chartData.forEach(reading => {
                    const row = document.createElement('tr');
                    fieldsToShow.forEach(key => {
                        const td = document.createElement('td');
                        const value = reading[key];
                        if (key === 'reading_datetime' && value) {
                            const d = new Date(value);
                            td.textContent = d.toLocaleString('pt-BR');
                        } else {
                            td.textContent = value !== null && value !== '' ? value : '-';
                        }
                        td.style.whiteSpace = 'nowrap';
                        row.appendChild(td);
                    });
                    if (isAdmin) {
                        const td = document.createElement('td');
                        const btn = document.createElement('button');
                        btn.textContent = 'Ver completo';
                        btn.className = 'lrgs-full-data-btn';
                        btn.onclick = () => openLrgsFullDataModal(reading);
                        td.appendChild(btn);
                        row.appendChild(td);
                    }
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
        filterContainer.style.display = 'none';
    }

    function createFilterContainer() {
        const container = document.createElement('div');
        container.className = 'lrgs-filter-container';
        container.style.cssText = 'display: none; justify-content: center; align-items: center; gap: 15px;';

        const deLabel = document.createElement('label');
        deLabel.textContent = 'De:';
        deLabel.style.cssText = 'font-weight: 500; color: #333;';

        const deInput = document.createElement('input');
        deInput.type = 'date';
        deInput.id = 'filterDateFrom';
        deInput.style.cssText = 'padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; cursor: pointer;';

        const ateLabel = document.createElement('label');
        ateLabel.textContent = 'Até:';
        ateLabel.style.cssText = 'font-weight: 500; color: #333;';

        const ateInput = document.createElement('input');
        ateInput.type = 'date';
        ateInput.id = 'filterDateTo';
        ateInput.style.cssText = 'padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; cursor: pointer;';

        const aplicarBtn = document.createElement('button');
        aplicarBtn.textContent = 'Aplicar';
        aplicarBtn.style.cssText = 'padding: 8px 16px; background: #242731; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500;';

        const limparBtn = document.createElement('button');
        limparBtn.textContent = 'Limpar';
        limparBtn.style.cssText = 'padding: 8px 16px; background: #ffffff; color: #242731; border: 1px solid #79808F; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500;';

        aplicarBtn.onclick = () => {
            if (!chartData) return;

            const fromDate = deInput.value ? new Date(deInput.value) : null;
            const toDate = ateInput.value ? new Date(ateInput.value) : null;

            // Ajustar para UTC para evitar problemas de fuso
            if (fromDate) {
                fromDate.setUTCHours(0, 0, 0, 0);
            }
            if (toDate) {
                toDate.setUTCHours(23, 59, 59, 999);
            }

            filteredData = chartData.filter(reading => {
                let readingDate = null;

                if (reading.reading_datetime) {
                    readingDate = new Date(reading.reading_datetime);
                } else if (reading.year && reading.julian_day && reading.hour !== undefined && reading.minute !== undefined) {
                    readingDate = new Date(Date.UTC(
                        parseInt(reading.year),
                        0,
                        parseInt(reading.julian_day),
                        parseInt(reading.hour) || 0,
                        parseInt(reading.minute) || 0,
                        parseInt(reading.second) || 0
                    ));
                }

                if (!readingDate) return true;

                // Comparar usando UTC
                const readingTime = readingDate.getTime();

                if (fromDate && readingTime < fromDate.getTime()) return false;
                if (toDate && readingTime > toDate.getTime()) return false;

                return true;
            });

            createChart(filteredData);
        };

        limparBtn.onclick = () => {
            deInput.value = '';
            ateInput.value = '';
            filteredData = chartData;
            createChart(filteredData);
        };

        container.appendChild(deLabel);
        container.appendChild(deInput);
        container.appendChild(ateLabel);
        container.appendChild(ateInput);
        container.appendChild(aplicarBtn);
        container.appendChild(limparBtn);

        return container;
    }

    function createChartContainer() {
        const container = document.createElement('div');
        container.id = 'lrgsChartContainer';
        container.style.cssText = 'display: none; width: 90%; height: 42%; margin: auto; position: relative;';

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

        // Limpar mensagem de erro anterior se existir
        const existingError = chartContainer.querySelector('.lrgs-chart-error');
        if (existingError) {
            existingError.remove();
        }

        const numericFields = (function extractNumericFields(readings) {
            const numericFields = [];

            const targetFields = [
                { key: 'water_level_15min', label: 'Nível da água' },
                { key: 'flow_15min', label: 'Vazão' },
                { key: 'rain_15min', label: 'Chuva' },
                { key: 'water_temperature', label: 'Temperatura da água' },
                { key: 'atmospheric_pressure', label: 'Pressão atmosférica' }
            ];

            if (readings.length > 0) {
                targetFields.forEach(field => {
                    for (let i = 0; i < Math.min(5, readings.length); i++) {
                        const value = readings[i][field.key];
                        if (value !== null && value !== '' && value !== '-' && !isNaN(parseFloat(value))) {
                            const numValue = parseFloat(value);
                            if (!isNaN(numValue)) {
                                numericFields.push({
                                    key: field.key,
                                    label: field.label,
                                    unit: extractUnit(field.key),
                                    values: readings.map(r => {
                                        const val = parseFloat(r[field.key]);
                                        return isNaN(val) ? null : val;
                                    })
                                });
                                break;
                            }
                        }
                    }
                });
            }

            return numericFields.filter(field => {
                const validValues = field.values.filter(v => v !== null);
                return validValues.length >= 1;
            });

            function extractUnit(fieldKey) {
                if (fieldKey.includes('water_level')) return 'm';
                else if (fieldKey.includes('flow')) return 'm³/s';
                else if (fieldKey.includes('rain')) return 'mm';
                else if (fieldKey.includes('temperature')) return '°C';
                else if (fieldKey.includes('atmospheric_pressure')) return 'hPa';
                return '';
            }
        })(readings);

        if (numericFields.length === 0) {
            canvas.style.display = 'none';
            const errorDiv = document.createElement('div');
            errorDiv.className = 'lrgs-chart-error';
            errorDiv.style.cssText = 'text-align: center; padding: 50px; color: #666; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%;';
            errorDiv.textContent = 'Não há dados numéricos suficientes para exibir o gráfico.';
            chartContainer.appendChild(errorDiv);
            return;
        }

        canvas.style.display = 'block';

        const timestamps = readings.map(r => {
            try {
                if (r.year && r.julian_day && r.hour !== undefined && r.minute !== undefined) {
                    const date = new Date(Date.UTC(
                        parseInt(r.year),
                        0,
                        parseInt(r.julian_day),
                        parseInt(r.hour) || 0,
                        parseInt(r.minute) || 0,
                        parseInt(r.second) || 0
                    ));

                    return date.toLocaleString('pt-BR', {
                        day: '2-digit',
                        month: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        timeZone: 'UTC'
                    });
                }
            } catch (e) {
                console.error('Erro ao processar data:', e);
            }
            return '';
        });

        const selectedFields = numericFields;

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
                    drawOnChartArea: index === 0
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
                                        const date = new Date(Date.UTC(
                                            parseInt(reading.year),
                                            0,
                                            parseInt(reading.julian_day),
                                            parseInt(reading.hour) || 0,
                                            parseInt(reading.minute) || 0,
                                            parseInt(reading.second) || 0
                                        ));

                                        return date.toLocaleString('pt-BR', {
                                            day: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            second: '2-digit',
                                            hour12: false,
                                            timeZone: 'UTC'
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

// Função para abrir sub-modal de dados completos (admin)
function openLrgsFullDataModal(reading) {
    const fullModal = document.getElementById('lrgsFullDataModal');
    const fullBody = document.getElementById('lrgsFullDataBody');
    const closeBtn = document.getElementById('closeLrgsFullModal');

    if (!fullModal || !fullBody) return;

    const dl = document.createElement('dl');
    Object.entries(reading).forEach(([key, value]) => {
        if (value === null || value === '' || value === undefined) return;

        const dt = document.createElement('dt');
        dt.textContent = key;

        const dd = document.createElement('dd');
        dd.textContent = value;

        dl.appendChild(dt);
        dl.appendChild(dd);
    });

    fullBody.innerHTML = '';
    fullBody.appendChild(dl);
    fullModal.style.display = 'block';

    if (closeBtn) {
        closeBtn.onclick = () => { fullModal.style.display = 'none'; };
    }

    fullModal.onclick = (e) => {
        if (e.target === fullModal) {
            fullModal.style.display = 'none';
        }
    };
}

// Função para abrir sub-modal de dados completos (admin)
function openLrgsFullDataModal(reading) {
    const fullModal = document.getElementById('lrgsFullDataModal');
    const fullBody = document.getElementById('lrgsFullDataBody');
    const closeBtn = document.getElementById('closeLrgsFullModal');

    if (!fullModal || !fullBody) return;

    const dl = document.createElement('dl');
    Object.entries(reading).forEach(([key, value]) => {
        if (value === null || value === '' || value === undefined) return;

        const dt = document.createElement('dt');
        dt.textContent = key;

        const dd = document.createElement('dd');
        dd.textContent = value;

        dl.appendChild(dt);
        dl.appendChild(dd);
    });

    fullBody.innerHTML = '';
    fullBody.appendChild(dl);
    fullModal.style.display = 'block';

    if (closeBtn) {
        closeBtn.onclick = () => { fullModal.style.display = 'none'; };
    }

    fullModal.onclick = (e) => {
        if (e.target === fullModal) {
            fullModal.style.display = 'none';
        }
    };
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
    const elements = {
        loading: document.getElementById('cnarhLoadingSpinner'),
        dataContainer: document.getElementById('cnarhDataContainer'),
        error: document.getElementById('cnarhErrorMessage'),
        errorText: document.getElementById('cnarhErrorText')
    };

    const modal = document.getElementById(cnarhModalConfig.modalId);
    const modalCode = document.getElementById(cnarhModalConfig.codeId);

    modal.style.display = 'block';
    modalCode.innerHTML = `
        <div class="cnarh-header-content-title">
            <h2 style="margin: 0;">${stationName} - ${cnarhCode}</h2>
            <p style="margin: 0;">Detalhes da outorga, vazões autorizadas, períodos de validade, localização e informações do usuário.</p>
        </div>
    `;

    // Limpar estado anterior
    if (window.cnarhChartInstance) {
        window.cnarhChartInstance.destroy();
    }

    document.querySelector('.cnarh-view-controls')?.remove();
    document.getElementById('cnarhChartContainer')?.remove();

    // Resetar UI
    elements.loading.style.display = 'block';
    elements.dataContainer.style.display = 'none';
    elements.error.style.display = 'none';

    // Buscar dados
    fetch(`/api/cnarh/${cnarhCode}/readings`)
        .then(response => response.ok ? response.json() : Promise.reject('Erro ao buscar dados'))
        .then(data => {
            elements.loading.style.display = 'none';

            if (data.success && data.data?.cnarh) {
                const cnarhData = data.data.cnarh;

                // Renderizar tabela
                renderTable(cnarhData);
                elements.dataContainer.style.display = 'block';

                // Extrair campos numéricos
                const numericFields = extractNumericFields(cnarhData);

                // Criar gráfico e controles se houver dados
                if (numericFields.length > 0) {
                    setupChartAndControls(cnarhData, numericFields, cnarhCode);
                }

            } else {
                showError('Nenhum dado encontrado.');
            }
        })
        .catch(error => {
            elements.loading.style.display = 'none';
            showError(error.message || error);
        });

    // Funções auxiliares
    function showError(message) {
        elements.error.style.display = 'block';
        elements.errorText.textContent = message;
    }

    function renderTable(cnarhData) {
        // Filtrar apenas os itens que têm valores
        const entries = Object.entries(cnarhData).filter(([key, value]) =>
            value !== null && value !== '' && value !== undefined
        );

        const totalItems = entries.length;
        const itemsPerColumn = Math.ceil(totalItems / 2);

        let html = `
            <div id="cnarhTableContainer" class="cnarh-table-container">
                <div class="cnarh-table-column">
        `;

        // Primeira coluna
        for (let i = 0; i < itemsPerColumn; i++) {
            const [key, value] = entries[i];
            html += `
                <div class="cnarh-data-row">
                    <div class="cnarh-data-label">${formatKey(key)}:</div>
                    <div class="cnarh-data-value">${value}</div>
                </div>
            `;
        }

        html += `
            </div>
            <div class="cnarh-table-column">
        `;

        // Segunda coluna
        for (let i = itemsPerColumn; i < totalItems; i++) {
            const [key, value] = entries[i];
            html += `
            <div class="cnarh-data-row">
                <div class="cnarh-data-label">${formatKey(key)}:</div>
                <div class="cnarh-data-value">${value}</div>
            </div>
        `;
        }

        html += `
            </div>
            </div>
        `;

        document.getElementById('cnarhDataContent').innerHTML = html;
    }

    function formatKey(key) {
        return key
            .replace(/^[a-z]+_/, '')
            .replace(/_/g, ' ')
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
    }

    function extractNumericFields(cnarhData) {
        const numericFields = [];
        const patterns = {
            vazao: /vazao|volume/i,
            tempo: /hora|dia|minuto/i,
            coordenada: /latitude|longitude/i,
            medida: /profundidade|cota|temperatura|condutividade|ph|std/i
        };

        Object.entries(cnarhData).forEach(([key, value]) => {
            // Verificar se é numérico
            if (value && value !== '-' && !isNaN(parseFloat(value))) {
                const numValue = parseFloat(value);

                // Verificar se é um campo interessante
                let isInteresting = false;
                for (const [type, pattern] of Object.entries(patterns)) {
                    if (pattern.test(key)) {
                        isInteresting = true;
                        break;
                    }
                }

                if (isInteresting) {
                    numericFields.push({
                        key,
                        label: formatKey(key),
                        unit: getUnit(key),
                        value: numValue
                    });
                }
            }
        });

        return numericFields;
    }

    function getUnit(key) {
        // Ordem de prioridade (mais específico primeiro)
        if (key.includes('volumeanual')) return 'm³/ano';
        if (key.includes('vazaodia')) return 'L/s';
        if (key.includes('vazao')) return 'L/s';
        if (key.includes('volume')) return 'm³';
        if (key.includes('hora')) return 'h';
        if (key.includes('dia') && !key.includes('vazao')) return 'dias';
        if (key.includes('latitude') || key.includes('longitude')) return '°';
        if (key.includes('profundidade') || key.includes('cota')) return 'm';
        if (key.includes('condutividade')) return 'µS/cm';
        if (key.includes('temperatura')) return '°C';
        if (key.includes('ph')) return 'pH';
        if (key.includes('std')) return 'mg/L';
        return '';
    }

    function setupChartAndControls(cnarhData, numericFields, cnarhCode) {
        // Criar elementos
        const chartContainer = createChartContainer();
        const controlsContainer = createControlsContainer();

        // Inserir no DOM
        const modalBody = document.querySelector('.modal-body') ||
            document.querySelector('.modal-content') ||
            elements.dataContainer.parentNode;

        if (modalBody) {
            modalBody.insertBefore(chartContainer, elements.dataContainer.nextSibling);
            modalBody.insertBefore(controlsContainer, chartContainer.nextSibling);
        }

        // Configurar views
        const views = {
            table: () => showView('table', chartContainer, controlsContainer),
            chart: () => {
                showView('chart', chartContainer, controlsContainer);
                renderChart(cnarhData, numericFields, cnarhCode, chartContainer);
            }
        };

        // Event listeners
        document.getElementById('cnarhTableViewBtn').onclick = views.table;
        document.getElementById('cnarhChartViewBtn').onclick = views.chart;

        // Mostrar tabela por padrão
        views.table();
    }

    function showView(viewType, chartContainer, controlsContainer) {
        const isTable = viewType === 'table';
        const tableBtn = document.getElementById('cnarhTableViewBtn');
        const chartBtn = document.getElementById('cnarhChartViewBtn');

        // Atualizar botões
        tableBtn.className = `cnarh-view-btn ${isTable ? 'active' : ''}`;
        tableBtn.style.cssText = `padding: 10px 20px; background: ${isTable ? '#242731' : '#ffffff'}; color: ${isTable ? 'white' : '#242731'}; border: ${isTable ? 'none' : '1px solid #79808F'}; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;`;

        chartBtn.className = `cnarh-view-btn ${!isTable ? 'active' : ''}`;
        chartBtn.style.cssText = `padding: 10px 20px; background: ${!isTable ? '#242731' : '#ffffff'}; color: ${!isTable ? 'white' : '#242731'}; border: ${!isTable ? 'none' : '1px solid #79808F'}; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;`;

        // Mostrar/ocultar elementos
        elements.dataContainer.style.display = isTable ? 'block' : 'none';
        chartContainer.style.display = isTable ? 'none' : 'block';

        // Posicionar controles
        const targetElement = isTable ? elements.dataContainer : chartContainer;
        if (controlsContainer.parentNode !== targetElement.nextSibling) {
            targetElement.parentNode.insertBefore(controlsContainer, targetElement.nextSibling);
        }
    }

    function createChartContainer() {
        const container = document.createElement('div');
        container.id = 'cnarhChartContainer';
        container.style.cssText = 'display: none; width: 90%; height: 400px; margin: auto; position: relative;';

        const canvas = document.createElement('canvas');
        canvas.id = 'cnarhChart';
        canvas.style.cssText = 'width: 90% !important; height: 90% !important;';
        container.appendChild(canvas);

        return container;
    }

    function createControlsContainer() {
        const container = document.createElement('div');
        container.className = 'cnarh-view-controls';
        container.style.cssText = 'margin: 20px 0; display: flex; justify-content: center; gap: 15px; padding: 15px;';

        ['TABELA', 'GRÁFICO'].forEach((text, i) => {
            const btn = document.createElement('button');
            btn.id = i === 0 ? 'cnarhTableViewBtn' : 'cnarhChartViewBtn';
            btn.textContent = text;
            btn.className = i === 0 ? 'cnarh-view-btn active' : 'cnarh-view-btn';
            btn.style.cssText = `padding: 10px 20px; background: ${i === 0 ? '#242731' : '#ffffff'}; color: ${i === 0 ? 'white' : '#242731'}; border: ${i === 0 ? 'none' : '1px solid #79808F'}; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;`;
            container.appendChild(btn);
        });

        return container;
    }

    function renderChart(cnarhData, numericFields, cnarhCode, chartContainer) {
        // Limpar gráfico anterior
        if (window.cnarhChartInstance) {
            window.cnarhChartInstance.destroy();
        }

        const canvas = document.getElementById('cnarhChart');
        if (!canvas) return;

        // Preparar dados (limitar a 10 campos)
        const displayFields = [...numericFields]
            .sort((a, b) => b.value - a.value)
            .slice(0, 10);

        if (displayFields.length === 0) {
            chartContainer.innerHTML = '<div style="text-align: center; padding: 50px; color: #666;">Sem dados para gráfico.</div>';
            return;
        }

        // Configurar gráfico
        const isHorizontal = displayFields.length > 5;
        const colors = ['#A47864', '#3388ff', '#ff5733', '#33ff57', '#ff33a1',
            '#33fff6', '#ffcc00', '#9966ff', '#00cc99', '#ff6666'];

        window.cnarhChartInstance = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: displayFields.map(f => `${f.label} ${f.unit ? `(${f.unit})` : ''}`),
                datasets: [{
                    label: 'Valores',
                    data: displayFields.map(f => f.value),
                    backgroundColor: displayFields.map((_, i) => colors[i % colors.length]),
                    borderColor: displayFields.map((_, i) => colors[i % colors.length]),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: isHorizontal ? 'y' : 'x',
                plugins: {
                    title: {
                        display: true,
                        text: `CNARH ${cnarhCode} - Dados Principais`,
                        font: { size: 16 }
                    },
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const field = displayFields[context.dataIndex];
                                const value = context.parsed[isHorizontal ? 'x' : 'y'];

                                // Formatação personalizada
                                let formattedValue;

                                if (Math.abs(value) >= 1000) {
                                    // Para números grandes, mostrar com separador de milhar
                                    formattedValue = value.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 2
                                    });
                                } else if (Math.abs(value) < 0.01 && value !== 0) {
                                    // Para números muito pequenos, notação científica
                                    formattedValue = value.toExponential(2);
                                } else {
                                    formattedValue = value.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 4
                                    });
                                }

                                return `${field.label}: ${formattedValue} ${field.unit}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => {
                                if (Math.abs(value) >= 1000) {
                                    return value.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    });
                                }
                                return value.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 1,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    },
                    y: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: isHorizontal ? 0 : 45,
                            minRotation: isHorizontal ? 0 : 45
                        }
                    }
                }
            }
        });
    }
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
        container.style.cssText = 'display: none; width: 100%; height: 395px; margin-bottom: 20px; position: relative;';

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
