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
    
    window.addEventListener('click', function(event) {
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
    renderRow: function(reading) {
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
    modalIdPonto.innerHTML = idPonto + ' - ' + stationName + 
        '<br><strong style="color: #e16ccfff;">Latitude:</strong> ' + latitude + 
        ' | <strong style="color: #e16ccfff;">Longitude:</strong> ' + longitude;
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
                            <div class="siagas-data-label">${key}:</div>
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
        '<br><strong style="color: #ff7800;">Latitude:</strong> ' + latitude + 
        ' | <strong style="color: #ff7800;">Longitude:</strong> ' + longitude;
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
    const loadingSpinner = document.getElementById('leiturasLoadingSpinner');
    const tableContainer = document.getElementById('leiturasTableContainer');
    const errorMessage = document.getElementById('leiturasErrorMessage');
    const tableBody = document.getElementById('leiturasTableBody');
    
    // Mostrar loading
    loadingSpinner.style.display = 'block';
    tableContainer.style.display = 'none';
    errorMessage.style.display = 'none';
    tableBody.innerHTML = '';
    
    // Fetch dos dados (USAR A MESMA API DO MODAL ANTIGO)
    fetch(`/api/hidroweb-telemetria/${stationCode}/readings`)
        .then(response => {
            if (!response.ok) throw new Error('Erro ao buscar leituras');
            return response.json();
        })
        .then(data => {
            loadingSpinner.style.display = 'none';
            
            // IMPORTANTE: mesma estrutura do modal antigo
            if (data.success && data.data && data.data.readings && data.data.readings.length > 0) {
                // Atualizar total
                document.getElementById('leiturasTotal').textContent = data.data.readings.length;
                
                // Preencher tabela
                data.data.readings.forEach(reading => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${reading.measurement_datetime || '-'}</td>
                        <td>${reading.adopted_rainfall || '-'}</td>
                        <td>${reading.adopted_quota || '-'}</td>
                        <td>${reading.adopted_flow || '-'}</td>
                    `;
                    tableBody.appendChild(row);
                });
                
                tableContainer.style.display = 'block';
            } else {
                errorMessage.style.display = 'block';
                document.getElementById('leiturasErrorText').textContent = 'Nenhuma leitura encontrada.';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar leituras:', error);
            loadingSpinner.style.display = 'none';
            errorMessage.style.display = 'block';
            document.getElementById('leiturasErrorText').textContent = error.message;
        });
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
        button.addEventListener('click', function() {
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
        closeBtn.onclick = function() {
            modal.style.display = 'none';
        };
    }
    
    if (modal) {
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };
    }
}
