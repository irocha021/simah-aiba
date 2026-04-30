// ========================================
// Configurações genéricas para modais
// ========================================

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

// ========================================
// RIMAS
// ========================================

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
                    tdDate.textContent = reading.data_da_me || '-';
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

// ========================================
// SIAGAS
// ========================================

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

    // Mapeamento de nomes personalizados
    const nameMapping = {
        'ponto': 'Número do ponto',
        'localizaca': 'Localização',
        'latitude_d': 'Latitude',
        'longitude_': 'Longitude',
        'utme': 'UTMe',
        'utmn': 'UTMn',
        'bacia': 'Bacia',
        'municipio': 'Município',
        'natureza': 'Natureza',
        'nome': 'Nome',
        'proprietar': 'Proprietário',
        'subbacia': 'Sub-bacia',
        'situacao': 'Situação',
        'uf': 'UF',
        'data_perfu': 'Data da perfuração',
        'perfurador': 'Perfurador',
        'profundida': 'Profundidade',
        'profundi_1': 'Profundidade total',
        'data_teste': 'Data do teste',
        'surgencia': 'Surgência',
        'nivel_dina': 'Nível dinâmico',
        'nivel_esta': 'Nível estático',
        'vazao_esta': 'Vazão estabilizada',
        'data_anali': 'Data análise',
        'data_colet': 'Data coleta',
        'condutivid': 'Condutividade elétrica',
        'cor': 'Cor',
        'turbidez': 'Turbidez'
    };

    // Mapeamento de unidades de medida
    const unitMapping = {
        'latitude_d': '°',
        'longitude_': '°',
        'utme': 'm',
        'utmn': 'm',
        'profundida': 'm',
        'profundi_1': 'm',
        'nivel_dina': 'm',
        'nivel_esta': 'm',
        'vazao_esta': 'm³/h'
    };

    // Verificar se usuário está logado
    const isLoggedIn = document.querySelector('meta[name="user-logged-in"]')?.getAttribute('content') === 'true';

    // Lista de campos que devem ser ocultados para usuários não logados
    const hiddenFieldsForNonLogged = [
        'proprietar',
        'perfurador',
        'data_teste',
        'surgencia',
        'data_anali',
        'data_colet',
        'condutivid',
        'cor',
        'turbidez'
    ];

    // Função para formatar valor com unidade
    function formatValueWithUnit(key, value) {
        if (value === null || value === undefined || value === '') return '-';
        
        // Se tiver unidade definida e for número
        const unit = unitMapping[key];
        if (unit) {
            const numValue = parseFloat(value);
            if (!isNaN(numValue)) {
                return `${numValue} ${unit}`;
            }
        }
        
        return value;
    }

    fetch(`/api/pocos-siagas/${idPonto}/readings`)
        .then(response => {
            if (!response.ok) throw new Error('Erro ao buscar dados');
            return response.json();
        })
        .then(data => {
            loadingSpinner.style.display = 'none';

            if (data.success && data.data && data.data.poco) {
                const poco = data.data.poco;

                // Filtrar e mapear os dados com nomes personalizados
                const entries = Object.entries(poco)
                    .filter(([key, value]) => value !== '' && value !== undefined)
                    .filter(([key]) => {
                        // Para usuário não logado, pular os campos da lista hiddenFieldsForNonLogged
                        if (!isLoggedIn && hiddenFieldsForNonLogged.includes(key)) {
                            return false;
                        }
                        return true;
                    })
                    .map(([key, value]) => {
                        // Usar nome personalizado ou manter o original formatado
                        const displayName = nameMapping[key] || formatSiagasKey(key);
                        const formattedValue = formatValueWithUnit(key, value);
                        return [displayName, formattedValue];
                    });

                const totalItems = entries.length;
                const itemsPerColumn = Math.ceil(totalItems / 2);

                let html = `
                    <div id="siagasTableContainer" class="siagas-table-container">
                        <div class="siagas-table-column">
                `;

                // Primeira coluna
                for (let i = 0; i < itemsPerColumn; i++) {
                    const [displayName, value] = entries[i];
                    html += `
                        <div class="siagas-data-row">
                            <div class="siagas-data-label">${displayName}:</div>
                            <div class="siagas-data-value">${value}</div>
                        </div>
                    `;
                }

                html += `
                        </div>
                        <div class="siagas-table-column">
                `;

                // Segunda coluna
                for (let i = itemsPerColumn; i < totalItems; i++) {
                    const [displayName, value] = entries[i];
                    html += `
                        <div class="siagas-data-row">
                            <div class="siagas-data-label">${displayName}:</div>
                            <div class="siagas-data-value">${value}</div>
                        </div>
                    `;
                }

                html += `
                        </div>
                    </div>
                `;

                document.getElementById('siagasDataContent').innerHTML = html;
                dataContainer.style.display = 'block';

                // 2. Analisar variáveis numéricas disponíveis para o gráfico
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
                                legend: { display: false },
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
                                    title: { display: true, text: 'Valores' },
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

                            tableContainer.style.display = 'flex';
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

    // Função auxiliar para formatar chaves do SIAGAS (fallback)
    function formatSiagasKey(key) {
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

// ========================================
// SIMAH
// ========================================

function openSimahReadingsModal(stationCode, stationName) {
    const modal = document.getElementById('simahReadingsModal');
    const loading = document.getElementById('simahLoadingSpinner');
    const tableContainer = document.getElementById('simahReadingsTableContainer');
    const tableBody = document.getElementById('simahReadingsTableBody');
    const errorMessage = document.getElementById('simahErrorMessage');
    const errorText = document.getElementById('simahErrorText');
    const totalReadings = document.getElementById('simahModalTotalReadings');
    const stationNameEl = document.getElementById('simahModalStationName');
    const stationCodeEl = document.getElementById('simahModalStationCode');
    const tableHeader = document.querySelector('#simahReadingsTableContainer table thead tr');

    modal.style.display = 'block';
    stationNameEl.textContent = stationName;
    stationCodeEl.textContent = stationCode;

    loading.style.display = 'block';
    tableContainer.style.display = 'none';
    errorMessage.style.display = 'none';
    tableBody.innerHTML = '';

    // Verificar se o usuário está logado
    const isLoggedIn = document.querySelector('meta[name="user-logged-in"]')?.getAttribute('content') === 'true';

    // Esconder colunas no cabeçalho se não estiver logado
    if (!isLoggedIn && tableHeader) {
        // Esconder a primeira coluna (Nº)
        if (tableHeader.children[0]) {
            tableHeader.children[0].style.display = 'none';
        }
        // Esconder a terceira coluna (Data/Hora UTC) - índice 2
        if (tableHeader.children[2]) {
            tableHeader.children[2].style.display = 'none';
        }
    } else if (isLoggedIn && tableHeader) {
        // Garantir que as colunas estejam visíveis se estiver logado
        if (tableHeader.children[0]) {
            tableHeader.children[0].style.display = '';
        }
        if (tableHeader.children[2]) {
            tableHeader.children[2].style.display = '';
        }
    }

    document.getElementById('closeSimahModal').onclick = function () {
        modal.style.display = 'none';
        // Restaurar visibilidade das colunas ao fechar o modal
        if (tableHeader) {
            if (tableHeader.children[0]) tableHeader.children[0].style.display = '';
            if (tableHeader.children[2]) tableHeader.children[2].style.display = '';
        }
    };

    window.onclick = function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            // Restaurar visibilidade das colunas ao fechar
            if (tableHeader) {
                if (tableHeader.children[0]) tableHeader.children[0].style.display = '';
                if (tableHeader.children[2]) tableHeader.children[2].style.display = '';
            }
        }
    };

    function formatSimahDate(val) {
        if (!val) return '-';
        try {
            const d = new Date(val.replace(' ', 'T'));
            if (isNaN(d)) return val;
            return d.toLocaleString('pt-BR', {
                timeZone: 'America/Sao_Paulo',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).replace(',', '');
        } catch (e) {
            return val;
        }
    }

    function formatSimahNum(val) {
        if (val === null || val === undefined || val === '') return '-';
        const num = parseFloat(val);
        if (isNaN(num)) return '-';

        if (Math.abs(num) < 0.0001 && num !== 0) {
            return num.toExponential(4).replace('.', ',');
        }

        if (Math.abs(num) >= 1000) {
            return num.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 4
            });
        }

        return num.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 6
        });
    }

    fetch(`/api/pocos-simah/${stationCode}/readings`)
        .then(response => {
            if (!response.ok) throw new Error('Erro ao buscar leituras');
            return response.json();
        })
        .then(data => {
            loading.style.display = 'none';
            const readings = data.data.readings;
            totalReadings.textContent = data.data.total;

            if (!readings || readings.length === 0) {
                errorText.textContent = 'Nenhuma leitura encontrada para este poço.';
                errorMessage.style.display = 'block';
                return;
            }

            readings.slice(0, 50).forEach(r => {
                const row = document.createElement('tr');
                
                if (isLoggedIn) {
                    // Usuário logado: mostra todas as colunas
                    row.innerHTML = `
                        <td>${r.number ?? '-'}</td>
                        <td>${formatSimahDate(r.datetime_local)}</td>
                        <td>${formatSimahDate(r.datetime_utc)}</td>
                        <td>${formatSimahNum(r.pd_bar)}</td>
                        <td>${formatSimahNum(r.p1_bar)}</td>
                        <td>${formatSimahNum(r.p2_bar)}</td>
                        <td>${formatSimahNum(r.tob1_celsius)}</td>
                        <td>${formatSimahNum(r.tob2_celsius)}</td>
                    `;
                } else {
                    // Usuário não logado: NÃO inclui as colunas Nº e Data/Hora UTC
                    row.innerHTML = `
                        <td>${formatSimahDate(r.datetime_local)}</td>
                        <td>${formatSimahNum(r.pd_bar)}</td>
                        <td>${formatSimahNum(r.p1_bar)}</td>
                        <td>${formatSimahNum(r.p2_bar)}</td>
                        <td>${formatSimahNum(r.tob1_celsius)}</td>
                        <td>${formatSimahNum(r.tob2_celsius)}</td>
                    `;
                }
                tableBody.appendChild(row);
            });

            tableContainer.style.display = 'block';
        })
        .catch(err => {
            loading.style.display = 'none';
            errorText.textContent = err.message;
            errorMessage.style.display = 'block';
        });
}

// ========================================
// Hidroweb Qualidade de água
// ========================================

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

    // Verificar se o usuário está logado
    const isLoggedIn = document.querySelector('meta[name="user-logged-in"]')?.getAttribute('content') === 'true';

    // Criar elementos do gráfico e controles
    const chartContainer = createChartContainer();
    const controlsContainer = createControlsContainer();
    elements.tableContainer.parentNode.insertBefore(chartContainer, elements.tableContainer);
    elements.tableContainer.parentNode.insertBefore(controlsContainer, elements.tableContainer.nextSibling);

    let chartData = null;
    let filteredTableData = null;

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

    // Função para formatar data/hora para fuso horário de Brasília
    function formatDateTimeToBrazilian(dateString) {
        if (!dateString || dateString === '-') return '-';

        try {
            const utcDate = new Date(dateString);
            if (isNaN(utcDate.getTime())) return dateString;

            return utcDate.toLocaleString('pt-BR', {
                timeZone: 'America/Sao_Paulo',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }).replace(',', ' -');
        } catch (e) {
            return dateString;
        }
    }

    // Mapeamento de unidades de medida
    const unitMapping = {
        'ID': '',
        'Código': '',
        'Data e hora do registro': '',
        'Data última alteração': '',
        'Nível de consistência': '',
        'Número da medição': '',
        'Posição horizontal da coleta': '',
        'Posição vertical da coleta': '',
        'Profundidade': 'm',
        'Choveu': '',
        'Alcalinidade (CaCO3)': 'mg/L',
        'Carbono orgânico total': 'mg/L',
        'Cloretos': 'mg/L',
        'Clorofila': 'µg/L',
        'Coliformes termo tolerantes': 'UFC/100mL',
        'Condutividade específica': 'µS/cm a 25°C',
        'DBO': 'mg/L',
        'Descarga líquida': 'm³/s',
        'DQO': 'mg/L',
        'Escherichia Coli': 'UFC/100mL',
        'Fitoplancton': 'células/100mL',
        'Fósforo total': 'mg/L',
        'Nitratos': 'mg/L',
        'Nitrogênio amoniacal': 'mg/L',
        'Nitrogênio total': 'mg/L',
        'Ortofosfato total': 'mg/L',
        'OD': 'mg/L',
        'Ph': '',
        'Sólidos dissolvidos totais': 'mg/L',
        'Sólidos em suspensão totais': 'mg/L',
        'Temperatura da amostra': '°C',
        'Temperatura': '°C',
        'Transparência': 'm',
        'Turbidez': 'NTU',
        'Acidez CaCO3': 'mg/L',
        'Alcalinidade CO3': 'mg/L',
        'Alcalinidade HCO3': 'mg/L',
        'Alcalinidade OH': 'mg/L',
        'Alumínio dissolvido': 'mg/L',
        'Alumínio': 'mg/L',
        'Amônia não ionizável': 'mg/L',
        'Arsênio': 'mg/L',
        'Bario': 'mg/L',
        'Berílio': 'mg/L',
        'Bismuto': 'mg/L',
        'Boro dissolvido': 'mg/L',
        'Boro': 'mg/L',
        'Cádmio': 'mg/L',
        'Cálcio total': 'mg/L',
        'Chumbo': 'mg/L',
        'Cianeto livre': 'mg/L',
        'Cianetos': 'mg/L',
        'Cobalto': 'mg/L',
        'Cobre dissolvido': 'mg/L',
        'Cobre': 'mg/L',
        'Coliformes fecais': 'NMP/100mL',
        'Coliformes totais': 'NMP/100mL',
        'Compostos orgânicos clorados': 'mg/L',
        'Compostos orgânicos fosforados': 'mg/L',
        'Condutividade elétrica': 'µS/cm a 20°C',
        'Cor': 'mg Pt-Co/L',
        'Cromo hexavalente': 'mg/L',
        'Cromo total': 'mg/L',
        'Cromo trivalente': 'mg/L',
        'Densidade ciano bactérias': 'mL',
        'Detergentes': 'mg/L',
        'Dureza (CaCO3)': 'mg/L',
        'Dureza (MgCO3)': 'mg/L',
        'Dureza total': 'mg/L',
        'Estanho': 'mg/L',
        'Estreptococos fecais': 'NMP/100mL',
        'Ferro dissolvido': 'mg/L',
        'Ferro total': 'mg/L',
        'Fluoretos': 'mg/L',
        'Fosfato total': 'mg/L',
        'Hidrocarbonetos': 'mg/L',
        'Indicefenois': 'mg/L',
        'IQA': '',
        'Lítio': 'mg/L',
        'Magnésio total': 'mg/L',
        'Manganês': 'mg/L',
        'Mercúrio': 'mg/L',
        'Níquel': 'mg/L',
        'Nitritos': 'mg/L',
        'Nitrogênio orgânico': 'mg/L',
        'Nitrogênio total (Kjeldahl)': 'mg/L',
        'Óleos e graxas': 'mg/L',
        'OD (% Sat)': '%',
        'Potássio total': 'mg/L',
        'Prata': 'mg/L',
        'Selênio': 'mg/L',
        'Sílica dissolvida': 'mg/L',
        'Sódio total': 'mg/L',
        'Sólidos dissolvidos fixos': 'mg/L',
        'Sólidos dissolvidos voláteis': 'mg/L',
        'Sólidos em suspensão fixos': 'mg/L',
        'Sólidos em suspensão voláteis': 'mg/L',
        'Sólidos fixos': 'mg/L',
        'Sólidos sedimentáveis': 'mg/L',
        'Sólidos totais': 'mg/L',
        'Sólidos voláteis': 'mg/L',
        'Sulfatos': 'mg/L',
        'Sulfetos': 'mg/L',
        'Urânio': 'mg/L',
        'Vanádio': 'mg/L',
        'Zinco': 'mg/L',
        '1,1 Dicloroeteno': 'mg/L',
        '1,2 Dicloroetano': 'mg/L',
        '2,4,5 T': 'mg/L',
        '2,4,5 TP': 'mg/L',
        'Triclorofenol': 'mg/L',
        'Ácido diclorofenoxiacetico': 'mg/L',
        'Aldrin': 'mg/L',
        'Azinfosetil': 'mg/L',
        'Benzeno': 'mg/L',
        'Benzoapireno': 'mg/L',
        'BHC': 'mg/L',
        'Bifenilaspolicloradas': 'mg/L',
        'Carbaril': 'mg/L',
        'Clordano': 'mg/L',
        'DDEPP': 'mg/L',
        'DDT': 'mg/L',
        'Demeton': 'mg/L',
        'Diazinon': 'mg/L',
        'Dieldrin': 'mg/L',
        'Dodecacloro no cloro': 'mg/L',
        'Dysyston/Disulfton': 'mg/L',
        'Endossulfan': 'mg/L',
        'Endrin': 'mg/L',
        'Epoxidoheptacloro': 'mg/L',
        'Ethion': 'mg/L',
        'Gution': 'mg/L',
        'Heptacloro': 'mg/L',
        'Lindano': 'mg/L',
        'Malation': 'mg/L',
        'Metilparation': 'mg/L',
        'Metoxicloro': 'mg/L',
        'Paration': 'mg/L',
        'Pentaclorofenol': 'mg/L',
        'Phosdrin': 'mg/L',
        'Tetracloreto de carbono': 'mg/L',
        'Tetracloro de eteno': 'mg/L',
        'Toxafeno': 'mg/L',
        'Tricloro de eteno': 'mg/L',
        'Algas': 'UPA/mL',
        'Amônia': 'mg/L',
        'Bactérias heterotróficas': 'UFC/mL',
        'Cloro residual': 'mg/L',
        'Colifagos': 'NMP/100mL',
        'Contagem bactérias em placa': 'UFC/mL',
        'Enterobactérias patogênicas': 'org/mL',
        'Fungos': 'UFC/mL',
        'Nitrogênio albuminoide': 'mg/L',
        'Protozoários': 'org/mL',
        'Salmonelas': 'NMP/mL',
        'Zooplancton total': 'org/mL',
        'created_at': '',
        'updated_at': '',
        'deleted_at': ''
    };

    // Função para filtrar e renomear dados APENAS para a tabela
    function filterAndRenameForTable(readings) {
        const fieldMapping = {
            'id': 'ID',
            'station_code': 'Código',
            'data_hora_dado': 'Data e hora do registro',
            'data_ultima_alteracao': 'Data última alteração',
            'nivel_consistencia': 'Nível de consistência',
            'num_medicao': 'Número da medição',
            'posicao_horizontal_coleta': 'Posição horizontal da coleta',
            'posicao_vertical_coleta': 'Posição vertical da coleta',
            'profundidade_m': 'Profundidade',
            'choveu': 'Choveu',
            '1_alcalinidade_total_mgl_caco3': 'Alcalinidade (CaCO3)',
            '1_status': 'Status',
            '2_carbono_organico_total_mgl': 'Carbono orgânico total',
            '2_status': 'Status',
            '3_cloretos_mgl_cl': 'Cloretos',
            '3_status': 'Status',
            '4_clorofila_ugl': 'Clorofila',
            '4_status': 'Status',
            '5_coliformes_termo_tolerantes_ufc_100ml': 'Coliformes termo tolerantes',
            '5_status': 'Status',
            '6_condutividade_especifica_25oc_us_cm_a_25c': 'Condutividade específica',
            '6_status': 'Status',
            '7_dbo_mgl_02': 'DBO',
            '7_status': 'Status',
            '8_descarga_liquida_m3s': 'Descarga líquida',
            '8_status': 'Status',
            '9_dqo_mgl_02': 'DQO',
            '9_status': 'Status',
            '10_escherichiacoli_ufc_100ml': 'Escherichia Coli',
            '10_status': 'Status',
            '11_fitoplancton_quantitativo_celulas_100ml': 'Fitoplancton',
            '11_status': 'Status',
            '12_fosforo_total_mgl': 'Fósforo total',
            '12_status': 'Status',
            '13_nitratos_mgl_n': 'Nitratos',
            '13_status': 'Status',
            '14_nitrogenio_amoniacal_mgl': 'Nitrogênio amoniacal',
            '14_status': 'Status',
            '15_nitrogenio_total_mgl_n': 'Nitrogênio total',
            '15_status': 'Status',
            '16_ortofosfato_total_mgl_po4': 'Ortofosfato total',
            '16_status': 'Status',
            '17_od_mgl_02': 'OD',
            '17_status': 'Status',
            '18_ph': 'Ph',
            '18_status': 'Status',
            '19_soldissolvidos_totais_mgl': 'Sólidos dissolvidos totais',
            '19_status': 'Status',
            '20_solsuspensao_totais_mgl': 'Sólidos em suspensão totais',
            '20_status': 'Status',
            '21_temperatura_amostra_c': 'Temperatura da amostra',
            '21_status': 'Status',
            '22_tempar_c': 'Temperatura',
            '22_status': 'Status',
            '23_transparencia_m': 'Transparência',
            '23_status': 'Status',
            '24_turbidez_ntu': 'Turbidez',
            '24_status': 'Status',
            '25_acidez_mgl_caco3': 'Acidez CaCO3',
            '25_status': 'Status',
            '26_alcalinidade_co3_mgl': 'Alcalinidade CO3',
            '26_status': 'Status',
            '27_alcalinidade_hco3_mgl': 'Alcalinidade HCO3',
            '27_status': 'Status',
            '28_alcalinidade_oh_mgl': 'Alcalinidade OH',
            '28_status': 'Status',
            '29_aluminio_dissolvido_mgl': 'Alumínio dissolvido',
            '29_status': 'Status',
            '30_aluminio_mgl_al': 'Alumínio',
            '30_status': 'Status',
            '31_amonia_nao_ionizavel_mgl_nh3': 'Amônia não ionizável',
            '31_status': 'Status',
            '32_arsenio_mgl': 'Arsênio',
            '32_status': 'Status',
            '33_bario_mgl_ba': 'Bario',
            '33_status': 'Status',
            '34_berilio_mgl': 'Berílio',
            '34_status': 'Status',
            '35_bismuto_total_mgl': 'Bismuto',
            '35_status': 'Status',
            '36_borodissolvido_mgl': 'Boro dissolvido',
            '36_status': 'Status',
            '37_boro_mgl_b': 'Boro',
            '37_status': 'Status',
            '38_cadmio_mgl_cd': 'Cádmio',
            '38_status': 'Status',
            '39_calcio_total_mgl': 'Cálcio total',
            '39_status': 'Status',
            '40_chumbo_mgl': 'Chumbo',
            '40_status': 'Status',
            '41_cianeto_livre_mgl': 'Cianeto livre',
            '41_status': 'Status',
            '42_cianetos_mgl_cn': 'Cianetos',
            '42_status': 'Status',
            '43_cobalto_mgl_co': 'Cobalto',
            '43_status': 'Status',
            '44_cobre_dissolvido_mgl': 'Cobre dissolvido',
            '44_status': 'Status',
            '45_cobre_mgl_cu': 'Cobre',
            '45_status': 'Status',
            '46_coliformes_fecais_nmp_100ml': 'Coliformes fecais',
            '46_status': 'Status',
            '47_coliformes_totais_nmp_100ml': 'Coliformes totais',
            '47_status': 'Status',
            '48_compostos_organo_clorados_mgl': 'Compostos orgânicos clorados',
            '48_status': 'Status',
            '49_compostos_organo_fosforados_mgl': 'Compostos orgânicos fosforados',
            '49_status': 'Status',
            '50_condutivida_de_eletrica_us_cm_a_20c': 'Condutividade elétrica',
            '50_status': 'Status',
            '51_cor_mg_pt_col': 'Cor',
            '51_status': 'Status',
            '52_cromo_hexavalente_mgl': 'Cromo hexavalente',
            '52_status': 'Status',
            '53_cromo_total_mgl_cr': 'Cromo total',
            '53_status': 'Status',
            '54_cromo_trivalente_mgl': 'Cromo trivalente',
            '54_status': 'Status',
            '55_densidade_ciano_bacterias_cel_ml': 'Densidade ciano bactérias',
            '55_status': 'Status',
            '56_detergentes_mgl_las': 'Detergentes',
            '56_status': 'Status',
            '57_dureza_mgl_caco3': 'Dureza (CaCO3)',
            '57_status': 'Status',
            '58_dureza_magnesio_mgl_mgco3': 'Dureza (MgCO3)',
            '58_status': 'Status',
            '59_dureza_total_mgl': 'Dureza total',
            '59_status': 'Status',
            '60_estanho_mgl': 'Estanho',
            '60_status': 'Status',
            '61_estreptococos_fecais_nmp_100ml': 'Estreptococos fecais',
            '61_status': 'Status',
            '62_ferro_dissolvido_mgl': 'Ferro dissolvido',
            '62_status': 'Status',
            '63_ferro_total_mgl': 'Ferro total',
            '63_status': 'Status',
            '64_fluoretos_mgl': 'Fluoretos',
            '64_status': 'Status',
            '65_fosfato_total_mgl': 'Fosfato total',
            '65_status': 'Status',
            '66_hidrocarbonetos_mgl': 'Hidrocarbonetos',
            '66_status': 'Status',
            '67_indicefenois_mgl_c6h5oh': 'Indicefenois',
            '67_status': 'Status',
            '68_iqa': 'IQA',
            '68_status': 'Status',
            '69_litio_mgl': 'Lítio',
            '69_status': 'Status',
            '70_magnesio_total_mgl': 'Magnésio total',
            '70_status': 'Status',
            '71_manganes_mgl': 'Manganês',
            '71_status': 'Status',
            '72_mercurio_mgl': 'Mercúrio',
            '72_status': 'Status',
            '73_niquel_mgl': 'Níquel',
            '73_status': 'Status',
            '74_nitritos_mgl': 'Nitritos',
            '74_status': 'Status',
            '75_nitrogenio_organico_mgl': 'Nitrogênio orgânico',
            '75_status': 'Status',
            '76_nitrogenio_total_kjeldahl_mgl': 'Nitrogênio total (Kjeldahl)',
            '76_status': 'Status',
            '77_oleos_graxas_mgl': 'Óleos e graxas',
            '77_status': 'Status',
            '78_od_perc_saturacao': 'OD (% Sat)',
            '78_status': 'Status',
            '79_potassio_total_mgl': 'Potássio total',
            '79_status': 'Status',
            '80_prata_mgl': 'Prata',
            '80_status': 'Status',
            '81_parametro_profundidade_m': 'Profundidade',
            '81_status': 'Status',
            '82_selenio_mgl': 'Selênio',
            '82_status': 'Status',
            '83_silicadissolvida_mgl': 'Sílica dissolvida',
            '83_status': 'Status',
            '84_sodiototal_mgl': 'Sódio total',
            '84_status': 'Status',
            '85_soldissolvidos_fixos_mgl_a_180c': 'Sólidos dissolvidos fixos',
            '85_status': 'Status',
            '86_soldissolvidos_volateis_mgl': 'Sólidos dissolvidos voláteis',
            '86_status': 'Status',
            '87_sol_suspensao_fixos_mgl': 'Sólidos em suspensão fixos',
            '87_status': 'Status',
            '88_sol_suspensao_volateis_mgl': 'Sólidos em suspensão voláteis',
            '88_status': 'Status',
            '89_solfixos_mgl': 'Sólidos fixos',
            '89_status': 'Status',
            '90_sol_sedimentaveis_mgl': 'Sólidos sedimentáveis',
            '90_status': 'Status',
            '91_sol_totais_mgl': 'Sólidos totais',
            '91_status': 'Status',
            '92_sol_volateis_mgl': 'Sólidos voláteis',
            '92_status': 'Status',
            '93_sulfatos_mgl': 'Sulfatos',
            '93_status': 'Status',
            '94_sulfetos_mgl': 'Sulfetos',
            '94_status': 'Status',
            '95_uranio_total_mgl': 'Urânio',
            '95_status': 'Status',
            '96_vanadio_mgl': 'Vanádio',
            '96_status': 'Status',
            '97_zinco_mgl': 'Zinco',
            '97_status': 'Status',
            '98_1_1_dicloroeteno_mgl': '1,1 Dicloroeteno',
            '98_status': 'Status',
            '99_1_2_dicloroetano_mgl': '1,2 Dicloroetano',
            '99_status': 'Status',
            '100_2_4_5_t_mgl': '2,4,5 T',
            '100_status': 'Status',
            '101_2_4_5_tp_mgl': '2,4,5 TP',
            '101_status': 'Status',
            '102_2_4_6_triclorofenol_mgl': 'Triclorofenol',
            '102_status': 'Status',
            '103_acido_2_4_diclorofenoxiacetico_mgl': 'Ácido diclorofenoxiacetico',
            '103_status': 'Status',
            '104_aldrin_mgl': 'Aldrin',
            '104_status': 'Status',
            '105_azinfosetil_mgl': 'Azinfosetil',
            '105_status': 'Status',
            '106_benzeno_mgl': 'Benzeno',
            '106_status': 'Status',
            '107_benzoapireno_mgl': 'Benzoapireno',
            '107_status': 'Status',
            '108_bhc_mgl': 'BHC',
            '108_status': 'Status',
            '109_bifenilaspolicloradas_mgl': 'Bifenilaspolicloradas',
            '109_status': 'Status',
            '110_carbaril_mgl': 'Carbaril',
            '110_status': 'Status',
            '111_clordano_mgl': 'Clordano',
            '111_status': 'Status',
            '112_ddepp_mgl': 'DDEPP',
            '112_status': 'Status',
            '113_ddt_mgl': 'DDT',
            '113_status': 'Status',
            '114_demeton_mgl': 'Demeton',
            '114_status': 'Status',
            '115_diazinon_mgl': 'Diazinon',
            '115_status': 'Status',
            '116_dieldrin_mgl': 'Dieldrin',
            '116_status': 'Status',
            '117_dodecaclorononacloro_mgl': 'Dodecacloro no cloro',
            '117_status': 'Status',
            '118_dysystondisulfton_mgl': 'Dysyston/Disulfton',
            '118_status': 'Status',
            '119_endossulfan_mgl': 'Endossulfan',
            '119_status': 'Status',
            '120_endrin_mgl': 'Endrin',
            '120_status': 'Status',
            '121_epoxidoheptacloro_mgl': 'Epoxidoheptacloro',
            '121_status': 'Status',
            '122_ethion_mgl': 'Ethion',
            '122_status': 'Status',
            '123_gution_mgl': 'Gution',
            '123_status': 'Status',
            '124_heptacloro_mgl': 'Heptacloro',
            '124_status': 'Status',
            '125_lindano_mgl': 'Lindano',
            '125_status': 'Status',
            '126_malation_mgl': 'Malation',
            '126_status': 'Status',
            '127_metilparation_mgl': 'Metilparation',
            '127_status': 'Status',
            '128_metoxicloro_mgl': 'Metoxicloro',
            '128_status': 'Status',
            '129_paration_mgl': 'Paration',
            '129_status': 'Status',
            '130_pentaclorofenol_mgl': 'Pentaclorofenol',
            '130_status': 'Status',
            '131_phosdrin_mgl': 'Phosdrin',
            '131_status': 'Status',
            '132_tetra_cloreto_carbono_mgl': 'Tetracloreto de carbono',
            '132_status': 'Status',
            '133_tetra_cloro_eteno_mgl': 'Tetracloro de eteno',
            '133_status': 'Status',
            '134_toxafeno_mgl': 'Toxafeno',
            '134_status': 'Status',
            '135_tricloro_eteno_mgl': 'Tricloro de eteno',
            '135_status': 'Status',
            '136_algas_n_upa_ml': 'Algas',
            '136_status': 'Status',
            '137_amoniaco_mgl': 'Amônia',
            '137_status': 'Status',
            '138_bacterias_heterotroficas_ufc_ml': 'Bactérias heterotróficas',
            '138_status': 'Status',
            '139_cloro_residual_mgl': 'Cloro residual',
            '139_status': 'Status',
            '140_colifagos_nmp_100ml': 'Colifagos',
            '140_status': 'Status',
            '141_contagem_bacterias_placa_ufc_ml': 'Contagem bactérias em placa',
            '141_status': 'Status',
            '142_entero_bacterias_patogenicas_n_org_ml': 'Enterobactérias patogênicas',
            '142_status': 'Status',
            '143_fungos_ufc_ml': 'Fungos',
            '143_status': 'Status',
            '144_nitrogenio_albuminoide_mgl': 'Nitrogênio albuminoide',
            '144_status': 'Status',
            '145_protozoarios_n_org_ml': 'Protozoários',
            '145_status': 'Status',
            '146_salmonelas_nmp_ml': 'Salmonelas',
            '146_status': 'Status',
            '147_zooplanctontotal_n_org_ml': 'Zooplancton total',
            '147_status': 'Status',
            'created_at': 'Data de criação',
            'updated_at': 'Data de atualização',
            'deleted_at': 'Data de exclusão'
        };

        // Lista de campos que devem ser ocultados para usuários não logados
        const hiddenFieldsForNonLogged = [
            'id',
            'posicao_horizontal_coleta',
            'posicao_vertical_coleta',
            '1_status',
            '2_status',
            '3_cloretos_mgl_cl',
            '3_status',
            '4_clorofila_ugl',
            '4_status',
            '5_status',
            '6_condutividade_especifica_25oc_us_cm_a_25c',
            '6_status',
            '7_status',
            '8_status',
            '9_status',
            '10_status',
            '11_fitoplancton_quantitativo_celulas_100ml',
            '11_status',
            '12_status',
            '13_status',
            '14_status',
            '15_status',
            '16_ortofosfato_total_mgl_po4',
            '16_status',
            '17_status',
            '18_status',
            '19_status',
            '20_status',
            '21_status',
            '22_status',
            '23_status',
            '24_status',
            '25_acidez_mgl_caco3',
            '25_status',
            '26_alcalinidade_co3_mgl',
            '26_status',
            '27_alcalinidade_hco3_mgl',
            '27_status',
            '28_alcalinidade_oh_mgl',
            '28_status',
            '29_aluminio_dissolvido_mgl',
            '29_status',
            '30_aluminio_mgl_al',
            '30_status',
            '31_amonia_nao_ionizavel_mgl_nh3',
            '31_status',
            '32_arsenio_mgl',
            '32_status',
            '33_bario_mgl_ba',
            '33_status',
            '34_berilio_mgl',
            '34_status',
            '35_bismuto_total_mgl',
            '35_status',
            '36_borodissolvido_mgl',
            '36_status',
            '37_boro_mgl_b',
            '37_status',
            '38_cadmio_mgl_cd',
            '38_status',
            '39_calcio_total_mgl',
            '39_status',
            '40_chumbo_mgl',
            '40_status',
            '41_cianeto_livre_mgl',
            '41_status',
            '42_cianetos_mgl_cn',
            '42_status',
            '43_cobalto_mgl_co',
            '43_status',
            '44_cobre_dissolvido_mgl',
            '44_status',
            '45_cobre_mgl_cu',
            '45_status',
            '46_status',
            '47_status',
            '48_compostos_organo_clorados_mgl',
            '48_status',
            '49_compostos_organo_fosforados_mgl',
            '49_status',
            '50_status',
            '51_status',
            '52_cromo_hexavalente_mgl',
            '52_status',
            '53_cromo_total_mgl_cr',
            '53_status',
            '54_cromo_trivalente_mgl',
            '54_status',
            '55_densidade_ciano_bacterias_cel_ml',
            '55_status',
            '56_detergentes_mgl_las',
            '56_status',
            '57_dureza_mgl_caco3',
            '57_status',
            '58_dureza_magnesio_mgl_mgco3',
            '58_status',
            '59_dureza_total_mgl',
            '59_status',
            '60_estanho_mgl',
            '60_status',
            '61_estreptococos_fecais_nmp_100ml',
            '61_status',
            '62_ferro_dissolvido_mgl',
            '62_status',
            '63_ferro_total_mgl',
            '63_status',
            '64_fluoretos_mgl',
            '64_status',
            '65_status',
            '66_hidrocarbonetos_mgl',
            '66_status',
            '67_indicefenois_mgl_c6h5oh',
            '67_status',
            '68_status',
            '69_litio_mgl',
            '69_status',
            '70_magnesio_total_mgl',
            '70_status',
            '71_manganes_mgl',
            '71_status',
            '72_mercurio_mgl',
            '72_status',
            '73_niquel_mgl',
            '73_status',
            '74_nitritos_mgl',
            '74_status',
            '75_status',
            '76_status',
            '77_oleos_graxas_mgl',
            '77_status',
            '78_status',
            '79_status',
            '80_prata_mgl',
            '80_status',
            '81_parametro_profundidade_m',
            '81_status',
            '82_selenio_mgl',
            '82_status',
            '83_silicadissolvida_mgl',
            '83_status',
            '84_sodiototal_mgl',
            '84_status',
            '85_soldissolvidos_fixos_mgl_a_180c',
            '85_status',
            '86_soldissolvidos_volateis_mgl',
            '86_status',
            '87_sol_suspensao_fixos_mgl',
            '87_status',
            '88_sol_suspensao_volateis_mgl',
            '88_status',
            '89_solfixos_mgl',
            '89_status',
            '90_sol_sedimentaveis_mgl',
            '90_status',
            '91_status',
            '92_sol_volateis_mgl',
            '92_status',
            '93_sulfatos_mgl',
            '93_status',
            '94_sulfetos_mgl',
            '94_status',
            '95_uranio_total_mgl',
            '95_status',
            '96_vanadio_mgl',
            '96_status',
            '97_zinco_mgl',
            '97_status',
            '98_1_1_dicloroeteno_mgl',
            '98_status',
            '99_1_2_dicloroetano_mgl',
            '99_status',
            '100_2_4_5_t_mgl',
            '100_status',
            '101_2_4_5_tp_mgl',
            '101_status',
            '102_2_4_6_triclorofenol_mgl',
            '102_status',
            '103_acido_2_4_diclorofenoxiacetico_mgl',
            '103_status',
            '104_aldrin_mgl',
            '104_status',
            '105_azinfosetil_mgl',
            '105_status',
            '106_benzeno_mgl',
            '106_status',
            '107_benzoapireno_mgl',
            '107_status',
            '108_bhc_mgl',
            '108_status',
            '109_bifenilaspolicloradas_mgl',
            '109_status',
            '110_carbaril_mgl',
            '110_status',
            '111_clordano_mgl',
            '111_status',
            '112_ddepp_mgl',
            '112_status',
            '113_ddt_mgl',
            '113_status',
            '114_demeton_mgl',
            '114_status',
            '115_diazinon_mgl',
            '115_status',
            '116_dieldrin_mgl',
            '116_status',
            '117_dodecaclorononacloro_mgl',
            '117_status',
            '118_dysystondisulfton_mgl',
            '118_status',
            '119_endossulfan_mgl',
            '119_status',
            '120_endrin_mgl',
            '120_status',
            '121_epoxidoheptacloro_mgl',
            '121_status',
            '122_ethion_mgl',
            '122_status',
            '123_gution_mgl',
            '123_status',
            '124_heptacloro_mgl',
            '124_status',
            '125_lindano_mgl',
            '125_status',
            '126_malation_mgl',
            '126_status',
            '127_metilparation_mgl',
            '127_status',
            '128_metoxicloro_mgl',
            '128_status',
            '129_paration_mgl',
            '129_status',
            '130_pentaclorofenol_mgl',
            '130_status',
            '131_phosdrin_mgl',
            '131_status',
            '132_tetra_cloreto_carbono_mgl',
            '132_status',
            '133_tetra_cloro_eteno_mgl',
            '133_status',
            '134_toxafeno_mgl',
            '134_status',
            '135_tricloro_eteno_mgl',
            '135_status',
            '136_algas_n_upa_ml',
            '136_status',
            '137_amoniaco_mgl',
            '137_status',
            '138_bacterias_heterotroficas_ufc_ml',
            '138_status',
            '139_cloro_residual_mgl',
            '139_status',
            '140_colifagos_nmp_100ml',
            '140_status',
            '141_contagem_bacterias_placa_ufc_ml',
            '141_status',
            '142_entero_bacterias_patogenicas_n_org_ml',
            '142_status',
            '143_fungos_ufc_ml',
            '143_status',
            '144_nitrogenio_albuminoide_mgl',
            '144_status',
            '145_protozoarios_n_org_ml',
            '145_status',
            '146_salmonelas_nmp_ml',
            '146_status',
            '147_zooplanctontotal_n_org_ml',
            '147_status',
            'created_at',
            'updated_at',
            'deleted_at'
        ];

        // Filtrar e renomear os campos em cada leitura
        return readings.map(reading => {
            const filteredReading = {};

            Object.keys(reading).forEach(originalKey => {
                if (!isLoggedIn && hiddenFieldsForNonLogged.includes(originalKey)) {
                    return;
                }
                if (fieldMapping[originalKey]) {
                    const newKey = fieldMapping[originalKey];

                    // Se for um campo de Status, adiciona o nome do parâmetro ao status
                    if (originalKey.endsWith('_status')) {
                        const paramNumber = originalKey.split('_')[0];
                        const paramKey = Object.keys(fieldMapping).find(key =>
                            key.startsWith(`${paramNumber}_`) && !key.endsWith('_status')
                        );

                        if (paramKey && fieldMapping[paramKey]) {
                            filteredReading[`Status - ${fieldMapping[paramKey]}`] = reading[originalKey];
                        } else {
                            filteredReading[newKey] = reading[originalKey];
                        }
                    } else {
                        let value = reading[originalKey];

                        // Aplicar formatação de data para campos específicos
                        if (originalKey === 'data_hora_dado' ||
                            originalKey === 'data_ultima_alteracao' ||
                            originalKey === 'created_at' ||
                            originalKey === 'updated_at' ||
                            originalKey === 'deleted_at') {
                            value = formatDateTimeToBrazilian(value);
                        }

                        filteredReading[newKey] = value;
                    }
                }
            });

            return filteredReading;
        });
    }

    // Buscar dados
    fetch(`/api/hidroweb-qualidade-agua/${stationCode}/readings`)
        .then(response => response.ok ? response.json() : Promise.reject('Erro ao buscar leituras'))
        .then(data => {
            elements.loading.style.display = 'none';

            if (data.success && data.data?.readings?.length > 0) {
                // Dados originais para o gráfico
                chartData = data.data.readings;

                // Dados filtrados e renomeados apenas para a tabela
                filteredTableData = filterAndRenameForTable(data.data.readings);

                elements.total.textContent = data.data.readings.length;

                // Criar cabeçalho da tabela com unidades de medida
                const headerRow = document.createElement('tr');
                Object.keys(filteredTableData[0]).forEach(key => {
                    const th = document.createElement('th');
                    const unit = unitMapping[key];
                    th.textContent = unit ? `${key} (${unit})` : key;
                    th.style.whiteSpace = 'nowrap';
                    th.style.padding = '12px';
                    th.style.fontWeight = '600';
                    headerRow.appendChild(th);
                });
                elements.tableHeader.appendChild(headerRow);

                // Criar linhas da tabela com dados formatados
                filteredTableData.forEach(reading => {
                    const row = document.createElement('tr');
                    Object.keys(reading).forEach(key => {
                        const td = document.createElement('td');
                        const value = reading[key];
                        td.textContent = value !== null && value !== '' ? value : '-';
                        td.style.whiteSpace = 'nowrap';
                        td.style.padding = '8px 12px';
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

        // Extrair datas para o eixo X (já formatadas para exibição)
        const dates = readings.map(r => {
            if (r.data_hora_dado) {
                try {
                    const date = new Date(r.data_hora_dado);
                    return date.toLocaleDateString('pt-BR', { timeZone: 'America/Sao_Paulo' });
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
        ).slice(0, 5);

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
                    drawOnChartArea: index === 0
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
                                            timeZone: 'America/Sao_Paulo',
                                            day: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            hour12: false
                                        }).replace(',', ' -');
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

// ========================================
// LRGS Client (DCP)
// ========================================

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
    let fieldsToShow = [];
    let isAdmin = false;

    // Mapeamento dos nomes das colunas com suas unidades
    const columnMapping = {
        'reading_datetime': 'Data/Hora',
        'water_level': 'Nível de Água (m)',
        'rain': 'Precipitação (mm)',
        'water_temperature': 'Temperatura da Água (°C)',
        'atmospheric_pressure': 'Pressão Atmosférica (hPa)',
        'flow': 'Vazão (m³/s)',
        'water_level_15min': 'Nível da Água 15min (m)',
        'rain_15min': 'Precipitação 15min (mm)'
    };

    // Função para formatar data/hora no padrão brasileiro
    function formatDateTimeToBrazilian(dateString) {
        if (!dateString || dateString === null || dateString === '') return '-';

        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;

            return date.toLocaleString('pt-BR', {
                timeZone: 'America/Sao_Paulo',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }).replace(',', ' -');
        } catch (e) {
            return dateString;
        }
    }

    // Função para formatar valor numérico com unidade
    function formatValueWithUnit(key, value) {
        if (value === null || value === undefined || value === '') return '-';

        // Se for data, retorna formatado
        if (key === 'reading_datetime') {
            return formatDateTimeToBrazilian(value);
        }

        // Para campos numéricos, retorna apenas o número
        const numValue = parseFloat(value);
        if (!isNaN(numValue)) {
            return numValue;
        }

        return value;
    }

    const views = {
        table: () => {
            controlsContainer.children[0].className = 'lrgs-view-btn active';
            controlsContainer.children[0].style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            controlsContainer.children[1].className = 'lrgs-view-btn';
            controlsContainer.children[1].style.cssText = 'padding: 10px 20px; background: #ffffff; color: #242731; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            elements.tableContainer.style.display = 'block';
            chartContainer.style.display = 'none';
            filterContainer.style.display = 'flex';
            const exportBtns = document.querySelector('.lrgs-export-btns');
            if (exportBtns) exportBtns.style.display = 'flex';
        },
        chart: () => {
            controlsContainer.children[0].className = 'lrgs-view-btn';
            controlsContainer.children[0].style.cssText = 'padding: 10px 20px; background: #ffffff; color: #333; border: 1px solid #79808F; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            controlsContainer.children[1].className = 'lrgs-view-btn active';
            controlsContainer.children[1].style.cssText = 'padding: 10px 20px; background: #242731; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
            elements.tableContainer.style.display = 'none';
            chartContainer.style.display = 'block';
            filterContainer.style.display = 'flex';
            const exportBtns = document.querySelector('.lrgs-export-btns');
            if (exportBtns) exportBtns.style.display = 'none';
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
                isAdmin = data.is_admin === true;

                elements.total.textContent = data.data.readings.length;

                const BASIC_FIELDS = [
                    'reading_datetime',
                    'water_level', 'flow',
                    'rain',
                    'water_temperature', 'atmospheric_pressure'
                ];

                fieldsToShow = BASIC_FIELDS.filter(f => chartData[0].hasOwnProperty(f));

                // Criar cabeçalho da tabela com nomes traduzidos
                const headerRow = document.createElement('tr');
                fieldsToShow.forEach(key => {
                    const th = document.createElement('th');
                    th.textContent = columnMapping[key] || key;
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

                // Preencher linhas da tabela com valores formatados
                chartData.forEach(reading => {
                    const row = document.createElement('tr');
                    fieldsToShow.forEach(key => {
                        const td = document.createElement('td');
                        const value = reading[key];
                        const formattedValue = formatValueWithUnit(key, value);
                        td.textContent = formattedValue;
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

                // Botões de exportação — no filterContainer à direita
                const buildExportUrl = (format) => {
                    const dateFrom = document.getElementById('filterDateFrom')?.value || '';
                    const dateTo = document.getElementById('filterDateTo')?.value || '';
                    let url = `/api/lrgs-client/${stationCode}/export?format=${format}`;
                    if (dateFrom) url += `&date_from=${dateFrom}`;
                    if (dateTo) url += `&date_to=${dateTo}`;
                    if (isAdmin) url += `&admin=1`;
                    return url;
                };

                const csvBtn = document.createElement('button');
                csvBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>CSV`;
                csvBtn.title = 'Exportar CSV';
                csvBtn.style.cssText = 'padding: 6px 12px; background: #ffffff; color: #242731; border: 1px solid #79808F; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 5px;';
                const showExportSuccess = (msg) => {
                    const toast = document.createElement('div');
                    toast.textContent = msg;
                    toast.style.cssText = 'position:fixed; bottom:30px; right:30px; background:#1e6e3e; color:#fff; padding:12px 20px; border-radius:6px; font-size:14px; font-weight:500; z-index:99999; box-shadow:0 4px 12px rgba(0,0,0,0.15); transition:opacity 0.5s;';
                    document.body.appendChild(toast);
                    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 500); }, 3000);
                };

                const csvSvg = csvBtn.innerHTML;
                csvBtn.onclick = () => {
                    csvBtn.disabled = true;
                    csvBtn.textContent = 'Exportando...';
                    fetch(buildExportUrl('csv'))
                        .then(r => r.blob())
                        .then(blob => {
                            const a = document.createElement('a');
                            a.href = URL.createObjectURL(blob);
                            a.download = `lrgs_${stationCode}.csv`;
                            a.click();
                            URL.revokeObjectURL(a.href);
                            showExportSuccess('✓ Download CSV concluído!');
                        })
                        .finally(() => {
                            csvBtn.disabled = false;
                            csvBtn.innerHTML = csvSvg;
                        });
                };

                const xlsBtn = document.createElement('button');
                xlsBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13l2.5 4 2.5-4"/><path d="M8 17l2.5-4 2.5 4"/></svg>Excel`;
                xlsBtn.title = 'Exportar Excel';
                xlsBtn.style.cssText = 'padding: 6px 12px; background: #ffffff; color: #1e6e3e; border: 1px solid #1e6e3e; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 5px;';
                const xlsSvg = xlsBtn.innerHTML;
                xlsBtn.onclick = () => {
                    xlsBtn.disabled = true;
                    xlsBtn.textContent = 'Exportando...';
                    fetch(buildExportUrl('excel'))
                        .then(r => r.blob())
                        .then(blob => {
                            const a = document.createElement('a');
                            a.href = URL.createObjectURL(blob);
                            a.download = `lrgs_${stationCode}.xlsx`;
                            a.click();
                            URL.revokeObjectURL(a.href);
                            showExportSuccess('✓ Download Excel concluído!');
                        })
                        .finally(() => {
                            xlsBtn.disabled = false;
                            xlsBtn.innerHTML = xlsSvg;
                        });
                };

                const existingExportBtns = elements.tableContainer.parentNode.querySelector('.lrgs-export-btns');
                if (existingExportBtns) existingExportBtns.remove();
                const exportBtns = document.createElement('div');
                exportBtns.className = 'lrgs-export-btns';
                exportBtns.style.cssText = 'display: flex; justify-content: flex-end; gap: 8px; padding: 6px 0; width: 92%; margin: 0 auto;';
                exportBtns.appendChild(csvBtn);
                exportBtns.appendChild(xlsBtn);
                elements.tableContainer.parentNode.insertBefore(exportBtns, elements.tableContainer);

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
            const fromVal = deInput.value;
            const toVal = ateInput.value;
            const isTableActive = chartContainer.style.display === 'none';

            if (!fromVal || !toVal) return;
            elements.tableBody.innerHTML = '';
            elements.loading.style.display = 'block';
            fetch(`/api/lrgs-client/${stationCode}/readings?date_from=${fromVal}&date_to=${toVal}`)
                .then(r => r.json())
                .then(data => {
                    elements.loading.style.display = 'none';
                    if (data.success && data.data?.readings?.length > 0) {
                        const readings = data.data.readings;
                        elements.total.textContent = readings.length;

                        // Atualiza tabela com valores formatados
                        elements.tableBody.innerHTML = '';
                        readings.forEach(reading => {
                            const row = document.createElement('tr');
                            fieldsToShow.forEach(key => {
                                const td = document.createElement('td');
                                const value = reading[key];
                                const formattedValue = formatValueWithUnit(key, value);
                                td.textContent = formattedValue;
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

                        // Atualiza gráfico com os mesmos dados
                        filteredData = readings;
                        createChart(filteredData);
                    } else {
                        elements.tableBody.innerHTML = '<tr><td colspan="99">Nenhuma leitura encontrada.</td></tr>';
                        filteredData = [];
                        createChart(filteredData);
                    }
                });
        };

        limparBtn.onclick = () => {
            deInput.value = '';
            ateInput.value = '';
            elements.tableBody.innerHTML = '';
            elements.loading.style.display = 'block';
            fetch(`/api/lrgs-client/${stationCode}/readings`)
                .then(r => r.json())
                .then(data => {
                    elements.loading.style.display = 'none';
                    if (data.success && data.data?.readings?.length > 0) {
                        const readings = data.data.readings;
                        chartData = readings;
                        filteredData = readings;
                        elements.total.textContent = readings.length;

                        elements.tableBody.innerHTML = '';
                        readings.forEach(reading => {
                            const row = document.createElement('tr');
                            fieldsToShow.forEach(key => {
                                const td = document.createElement('td');
                                const value = reading[key];
                                const formattedValue = formatValueWithUnit(key, value);
                                td.textContent = formattedValue;
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

                        createChart(filteredData);
                    }
                });
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
        container.style.cssText = 'display: none; width: 90%; height: 40%; margin: auto; position: relative;';

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

        // Inverter a ordem dos readings apenas para o gráfico
        const reversedReadings = [...readings].reverse();

        const numericFields = (function extractNumericFields(readings) {
            const numericFields = [];

            const targetFields = [
                { key: 'water_level_15min', label: 'Nível da água' },
                { key: 'flow', label: 'Vazão' },
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
        })(reversedReadings);

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

        const timestamps = reversedReadings.map(r => {
            if (!r.reading_datetime) return '';
            return new Date(r.reading_datetime).toLocaleString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
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
                    maxTicksLimit: 20,
                    autoSkip: true
                }
            }
        };

        datasets.forEach((dataset, index) => {
            scales[`y${index}`] = {
                type: 'linear',
                display: true,
                position: index === 0 ? 'left' : 'right',
                min: 0,
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
                                const reading = reversedReadings[index];
                                if (!reading?.reading_datetime) return `Registro ${index + 1}`;
                                return formatDateTimeToBrazilian(reading.reading_datetime);
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

// ========================================
// CNARH
// ========================================

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

    // Verificar se o usuário está logado
    const isLoggedIn = document.querySelector('meta[name="user-logged-in"]')?.getAttribute('content') === 'true';

    // Função para formatar data/hora para o padrão brasileiro
    function formatDateTimeToBrazilian(dateString) {
        if (!dateString || dateString === null || dateString === '') return dateString;

        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;

            return date.toLocaleString('pt-BR', {
                timeZone: 'America/Sao_Paulo',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }).replace(',', ' -');
        } catch (e) {
            return dateString;
        }
    }

    // Mapeamento dos campos (chave original -> nome a ser exibido)
    const fieldMapping = {
        // Informações básicas da interferência
        'int_cd': 'Código Identificador Incremental Da Interferência',
        'int_tin_ds': 'Tipo De Interferencia',
        'int_tin_cd': 'Código De Interferencia',
        'int_tsu_ds': 'Subtipo De Interferencia',
        'int_tsu_cd': 'Código De Interferencia',
        'int_tch_cd': 'Código Tipo De Corpo',
        'int_tch_ds': 'Tipo De Corpo',
        'int_tsi_ds': 'Tipo Da Situação Da Interferência',
        'int_tsi_cd': 'Código Da Situação Da Interferência',
        'int_tod_ds': 'Origem Do Dado Cadastrado',
        'int_tdm_ds': 'Dominio Da Interferência',
        'int_nu_cnarh': 'Número Cnarh Do Empreendimento',
        'int_nu_siagas': 'Número De Registro SIAGAS',
        'int_nu_latitude': 'Latitude',
        'int_nu_longitude': 'Longitude',

        // Município
        'ing_nu_ibgemunicipio': 'Código Ibge',
        'ing_sg_ufmunicipio': 'Uf',
        'ing_nm_municipio': 'Nome Do Município',
        'ing_cd_comiteestadual': 'Código Comitê Estadual',
        'ing_nm_comiteestadual': 'Nome Comitê Estadual',
        'ing_cd_comitefederal': 'Código Comitê Federal',
        'ing_nm_comitefederal': 'Nome Comitê Federal',
        'ing_cd_ottobacia_trecho': 'Código Otto Bacia Trecho',
        'ing_cs_conama': 'Classe CONAMA',

        // Corpo hídrico
        'int_nm_corpohidrico': 'Corpo Hídrico',
        'int_nm_corpohidricoalterado': 'Corpo Hídrico Alterado',

        // Órgão e registro
        'int_ds_orgao': 'Nome Do Órgão',
        'int_cd_interferenciaoriginal': 'Código Da Interferência Original',
        'int_dt_registro': 'Data De Cadastro',
        'int_cd_declaracao': 'Código Da Declaração',
        'int_cd_origem': 'Código Origem Da Interferência',
        'int_ds_opcional': 'Descrição',
        'int_cd_regla': 'Código REGLA',
        'int_cd_cnarh40': 'Código CNARH 40',

        // Empreendimento e usuário
        'emp_nm_empreendimento': 'Nome Do Empreendimento',
        'emp_nm_usuario': 'Nome Do Usuário',
        'emp_nu_cpfcnpj': 'CPF/CNPJ',
        'emp_ds_emailresponsavel': 'Email',
        'emp_nu_cependereco': 'CEP',
        'emp_cd_ibgemuncorrespondencia': 'IBGE Correspondência',
        'emp_ds_logradouro': 'Logradouro do Usuário',
        'emp_ds_complementoendereco': 'Complemento Do Endereço',
        'emp_nu_logradouro': 'Número Do Logradouro',
        'emp_nu_caixapostal': 'Código Postal',
        'emp_ds_bairro': 'Bairro Do Endereço',
        'emp_nu_ddd': 'Ddd',
        'emp_nu_telefone': 'Número Do Telefone',
        'emp_sg_uf': 'Uf Do Responsável',
        'emp_nm_municipio': 'Munícipio Do Responsável',

        // Vazões
        'int_qt_vazaomaxima': 'Vazão Máxima',
        'int_qt_vazaomedia': 'Vazão Media',
        'int_qt_volumeanual': 'Volume Anual',

        // Finalidade
        'fin_tfn_ds': 'Tipo Da Finalidade Da Interferência',
        'fin_tfn_cd': 'Código da Finalidade',

        // Outorga
        'out_tpo_ds': 'Tipo De Pedido De Outorga',
        'out_tpo_cd': 'Código do Pedido',
        'out_tsp_ds': 'Situação Da Outorga',
        'out_tsp_cd': 'Código da Situação Da Outorga',
        'out_dt_outorgafinal': 'Data De Término',
        'out_dt_outorgainicial': 'Início de Outorga',
        'out_nu_processo': 'Número Do Processo',
        'out_tp_ato': 'Descrição Ato De Outorga',
        'out_nu_ato': 'Número Do Ato De Outorga',
        'out_tp_outorga': 'Tipo de Outorga',
        'out_tp_situacaooutorga': 'Situação da Outorga',

        // Dados mensais - Vazão
        'dad_qt_vazaodiajan': 'Atividade_JAN',
        'dad_qt_vazaodiafev': 'Atividade_FEV',
        'dad_qt_vazaodiamar': 'Atividade_MAR',
        'dad_qt_vazaodiaabr': 'Atividade_ABR',
        'dad_qt_vazaodiamai': 'Atividade_MAI',
        'dad_qt_vazaodiajun': 'Atividade_JUN',
        'dad_qt_vazaodiajul': 'Atividade_JUL',
        'dad_qt_vazaodiaago': 'Atividade_AGO',
        'dad_qt_vazaodiaset': 'Atividade_SET',
        'dad_qt_vazaodiaout': 'Atividade_OUT',
        'dad_qt_vazaodianov': 'Atividade_NOV',
        'dad_qt_vazaodiadez': 'Atividade_DEZ',

        // Dados mensais - Horas
        'dad_qt_horasjan': 'Operação_JAN',
        'dad_qt_horasfev': 'Operação_FEV',
        'dad_qt_horasmar': 'Operação_MAR',
        'dad_qt_horasabr': 'Operação_ABR',
        'dad_qt_horasmai': 'Operação_MAI',
        'dad_qt_horasjun': 'Operação_JUN',
        'dad_qt_horasjul': 'Operação_JUL',
        'dad_qt_horasago': 'Operação_AGO',
        'dad_qt_horasset': 'Operação_SET',
        'dad_qt_horasout': 'Operação_OUT',
        'dad_qt_horasnov': 'Operação_NOV',
        'dad_qt_horasdez': 'Operação_DEZ',

        // Dados mensais - Dias
        'dad_qt_diajan': 'Atividade_Dias_JAN',
        'dad_qt_diafev': 'Atividade_Dias_FEV',
        'dad_qt_diamar': 'Atividade_DIas_MAR',
        'dad_qt_diaabr': 'Atividade_Dias_ABR',
        'dad_qt_diamai': 'Atividade_Dias_MAI',
        'dad_qt_diajun': 'Atividade_Dias_JUN',
        'dad_qt_diajul': 'Atividade_Dias_JUL',
        'dad_qt_diaago': 'Atividade_Dias_AGO',
        'dad_qt_diaset': 'Atividade_Dias_SET',
        'dad_qt_diaout': 'Atividade_Dias_OUT',
        'dad_qt_dianov': 'Atividade_Dias_NOV',
        'dad_qt_diadez': 'Atividade_Dias_DEZ',

        // Dados do aquífero
        'asb_dt_instalacao': 'Data de Instalação',
        'asb_tnp_cd': 'Código Natureza do Ponto',
        'asb_tnp_ds': 'Natureza do Ponto',
        'asb_nu_diametroperfuracao': 'Diâmetro Perfuração',
        'asb_nu_diametrofiltro': 'Diâmetro Do Filtro',
        'asb_aqp_ds': 'Código Identificador Do Aquífero Ponto',
        'asb_aqp_cd': 'Código Identificador Aquífero',
        'asb_nu_topo': 'Profundidade Do Topo Do Aquífero',
        'asb_nu_base': 'Profundidade Da Base Do Aquífero',
        'asb_tpn_ds': 'Tipo De Penetração Do Aquífero',
        'asb_tpn_cd': 'Código Penetração Do Aquífero',
        'asb_tca_ds': 'Condição Do Aquífero',
        'asb_tca_cd': 'Condição Do Aquífero',
        'asb_nu_profundidadefinal': 'Profundidade Do Poço',
        'asb_nu_alturabocatubo': 'Altura Da Boca Da Tubulação',
        'asb_nu_cotaterreno': 'Altitude Do Terreno',

        // Teste de bombeamento
        'tst_dt': 'Data Do Teste Do Bombeamento',
        'tst_ttb_ds': 'Tipo De Teste De Bombeamento',
        'tst_ttb_cd': 'Código Teste De Bombeamento',
        'tst_ds_tempoduracao': 'Descrição Do Tempo De Duração',
        'tst_nu_nd': 'Nível Dinâmico',
        'tst_nu_ne': 'Nível Estático',
        'tst_vz_estabilizacao': 'Vazão De Estabilização',
        'tst_tmi_ds': 'Tipo De Método De Interpretação',
        'tst_tmi_cd': 'Tipo De Método De Interpretação',
        'tst_nu_coeficientearmazenamento': 'Coeficiente De Armazenamento',
        'tst_nu_transmissividade': 'Transmissividade',
        'tst_nu_condutividadehidraulica': 'Condutividade Hidraulica',
        'tst_nu_permeabilidade': 'Permeabilidade',

        // Dados da qualidade da água
        'ama_dt_coleta': 'Data Da Coleta',
        'ama_dt_analise': 'Data Da Análise',
        'ama_nu_condutividadeeletrica': 'Condutividade Elétrica',
        'ama_qt_temperatura': 'Temperatura',
        'ama_qt_std': 'Sólidos Totais Dissolvidos',
        'ama_qt_ph': 'Ph – Potencial Hidrogeniônico',
        'ama_qt_coliformestotais': 'Parâmetro Cloriformes Totais',
        'ama_qt_coliformesfecais': 'Parâmetro Cloriformes Fecais',
        'ama_qt_bicarbonato': 'Parâmetro Bicarbonato',
        'ama_qt_calcio': 'Parâmetro Cálcio',
        'ama_qt_carbonato': 'Parâmetro Carbonato',
        'ama_qt_cloreto': 'Parâmetro Cloreto',
        'ama_qt_durezatotal': 'Parâmetro Dureza Total',
        'ama_qt_ferrototal': 'Parâmetro Ferro',
        'ama_qt_fluoretos': 'Parâmetro Fluoretos',
        'ama_qt_nitratos': 'Parâmetro Nitratos',
        'ama_qt_nitritos': 'Parâmetro Nitritos',
        'ama_qt_potassio': 'Parâmetro Potássio',
        'ama_qt_sodio': 'Parâmetro Sódio',
        'ama_qt_sulfato': 'Parâmetro Sulfato',
        'ama_qt_magnesio': 'Parâmetro Magnésio',

        // Outros
        'data_extracao': 'Data de Extração',
        'created_at': 'Data de Criação',
        'updated_at': 'Data de Atualização',
        'deleted_at': 'Data de Exclusão',
        'id': 'ID'
    };

    // Mapeamento de unidades para valores específicos
    const unitMapping = {
        'int_nu_latitude': '°',
        'int_nu_longitude': '°',
        'dad_qt_vazaodiajan': 'm³/h',
        'dad_qt_vazaodiafev': 'm³/h',
        'dad_qt_vazaodiamar': 'm³/h',
        'dad_qt_vazaodiaabr': 'm³/h',
        'dad_qt_vazaodiamai': 'm³/h',
        'dad_qt_vazaodiajun': 'm³/h',
        'dad_qt_vazaodiajul': 'm³/h',
        'dad_qt_vazaodiaago': 'm³/h',
        'dad_qt_vazaodiaset': 'm³/h',
        'dad_qt_vazaodiaout': 'm³/h',
        'dad_qt_vazaodianov': 'm³/h',
        'dad_qt_vazaodiadez': 'm³/h',
        'dad_qt_horasjan': 'h',
        'dad_qt_horasfev': 'h',
        'dad_qt_horasmar': 'h',
        'dad_qt_horasabr': 'h',
        'dad_qt_horasmai': 'h',
        'dad_qt_horasjun': 'h',
        'dad_qt_horasjul': 'h',
        'dad_qt_horasago': 'h',
        'dad_qt_horasset': 'h',
        'dad_qt_horasout': 'h',
        'dad_qt_horasnov': 'h',
        'dad_qt_horasdez': 'h',
        'dad_qt_diajan': 'dias',
        'dad_qt_diafev': 'dias',
        'dad_qt_diamar': 'dias',
        'dad_qt_diaabr': 'dias',
        'dad_qt_diamai': 'dias',
        'dad_qt_diajun': 'dias',
        'dad_qt_diajul': 'dias',
        'dad_qt_diaago': 'dias',
        'dad_qt_diaset': 'dias',
        'dad_qt_diaout': 'dias',
        'dad_qt_dianov': 'dias',
        'dad_qt_diadez': 'dias',
        'int_qt_vazaomaxima': 'm³/h',
        'int_qt_vazaomedia': 'm³/h',
        'int_qt_volumeanual': 'm³',
        'tst_nu_transmissividade': 'm³/h',
        'fes_nu_profundidademediatanque': 'm',
        'fes_nu_areatotaltanque': 'm²'
    };

    // Lista de campos que são datas
    const dateFields = [
        'int_dt_registro',
        'out_dt_outorgafinal',
        'out_dt_outorgainicial',
        'asb_dt_instalacao',
        'ama_dt_coleta',
        'ama_dt_analise',
        'tst_dt',
        'data_extracao',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Buscar dados
    fetch(`/api/cnarh/${cnarhCode}/readings`)
        .then(response => response.ok ? response.json() : Promise.reject('Erro ao buscar dados'))
        .then(data => {
            elements.loading.style.display = 'none';

            if (data.success && data.data?.cnarh) {
                const cnarhData = data.data.cnarh;

                // Renderizar tabela mantendo a ordem original
                renderTable(cnarhData);
                elements.dataContainer.style.display = 'block';

                // Extrair campos numéricos para o gráfico
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

    function formatValueWithUnit(key, value) {
        // Verificar se o campo tem unidade definida
        const unit = unitMapping[key];
        if (unit && value !== null && value !== undefined && value !== '') {
            // Se for número, formatar e adicionar unidade
            const numValue = parseFloat(value);
            if (!isNaN(numValue)) {
                return `${numValue} ${unit}`;
            }
        }
        return value;
    }

    function formatValueWithDate(key, value) {
        // Verificar se é um campo de data
        if (dateFields.includes(key) && value) {
            return formatDateTimeToBrazilian(value);
        }
        return value;
    }

    function renderTable(cnarhData) {
        // Manter a ordem original das chaves, filtrando apenas valores não nulos
        const entries = [];

        for (const [key, value] of Object.entries(cnarhData)) {
            // Ocultar Campo para usuários não logados
            if (!isLoggedIn && key === 'emp_nm_empreendimento') {
                continue;
            }
            // Incluir apenas valores que não são null, undefined ou string vazia
            if (value !== null && value !== undefined && value !== '') {
                // Usar o mapeamento se existir, senão formatar a chave
                let displayName = fieldMapping[key];
                if (!displayName) {
                    displayName = key
                        .replace(/_/g, ' ')
                        .split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                        .join(' ');
                }

                // Formatar o valor (primeiro data, depois unidade)
                let formattedValue = formatValueWithDate(key, value);
                formattedValue = formatValueWithUnit(key, formattedValue);

                entries.push([displayName, formattedValue]);
            }
        }

        const totalItems = entries.length;
        const itemsPerColumn = Math.ceil(totalItems / 2);

        let html = `
            <div id="cnarhTableContainer" class="cnarh-table-container">
                <div class="cnarh-table-column">
        `;

        // Primeira coluna (metade superior dos itens na ordem original)
        for (let i = 0; i < itemsPerColumn; i++) {
            const [displayName, value] = entries[i];
            html += `
                <div class="cnarh-data-row">
                    <div class="cnarh-data-label">${displayName}:</div>
                    <div class="cnarh-data-value">${value}</div>
                </div>
            `;
        }

        html += `
            </div>
            <div class="cnarh-table-column">
        `;

        // Segunda coluna (metade inferior dos itens na ordem original)
        for (let i = itemsPerColumn; i < totalItems; i++) {
            const [displayName, value] = entries[i];
            html += `
            <div class="cnarh-data-row">
                <div class="cnarh-data-label">${displayName}:</div>
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
            vazao: /vazao|volume|vazao/i,
            tempo: /hora|dia|minuto/i,
            coordenada: /latitude|longitude/i,
            medida: /profundidade|cota|temperatura|condutividade|ph|std/i
        };

        Object.entries(cnarhData).forEach(([key, value]) => {
            // Verificar se é numérico
            if (value !== null && value !== '' && value !== '-' && !isNaN(parseFloat(value))) {
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
                        label: fieldMapping[key] || formatKey(key),
                        unit: unitMapping[key] || getUnit(key),
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
        if (key.includes('vazaodia')) return 'm³/h';
        if (key.includes('vazao')) return 'm³/h';
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
    const tableHeader = document.querySelector('#previsoesTableContainer table thead tr');

    // Mostrar loading
    loadingSpinner.style.display = 'block';
    tableContainer.style.display = 'none';
    errorMessage.style.display = 'none';
    emptyMessage.style.display = 'none';
    tableBody.innerHTML = '';

    // Verificar se o usuário está logado
    const isLoggedIn = document.querySelector('meta[name="user-logged-in"]')?.getAttribute('content') === 'true';

    // Esconder a coluna alfa_pond no cabeçalho se não estiver logado
    if (!isLoggedIn && tableHeader) {
        // A coluna alfa_pond é a 4ª coluna (índice 3, porque começa em 0)
        if (tableHeader.children[3]) {
            tableHeader.children[3].style.display = 'none';
        }
    } else if (isLoggedIn && tableHeader) {
        // Garantir que esteja visível para usuário logado
        if (tableHeader.children[3]) {
            tableHeader.children[3].style.display = '';
        }
    }

    // Fetch dos dados
    fetch(`/api/hidroweb-telemetria/${stationCode}/forecast`)
        .then(response => {
            if (response.status === 404) {
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
                    
                    if (isLoggedIn) {
                        // Usuário logado: mostra todas as colunas
                        row.innerHTML = `
                            <td>${forecast.forecast_year || '-'}</td>
                            <td>${forecast.forecast_month || '-'}</td>
                            <td>${forecast.predicted_flow || '-'}</td>
                            <td>${forecast.alfa_pond || '-'}</td>
                            <td>${forecast.q_noventa || '-'}</td>
                            <td>${forecast.vsup || '-'}</td>
                        `;
                    } else {
                        // Usuário NÃO logado: NÃO inclui a coluna alfa_pond
                        row.innerHTML = `
                            <td>${forecast.forecast_year || '-'}</td>
                            <td>${forecast.forecast_month || '-'}</td>
                            <td>${forecast.predicted_flow || '-'}</td>
                            <td>${forecast.q_noventa || '-'}</td>
                            <td>${forecast.vsup || '-'}</td>
                        `;
                    }
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
