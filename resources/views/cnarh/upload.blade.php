<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Upload CSV CNARH | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/cnarh-upload.css') }}">
</head>

<body>
    <div class="upload-container">
        <!-- Lado esquerdo - Formulário -->
        <div class="upload-form-section">
            <!-- Logo no topo -->
            <div class="top-logo">
                <img src="{{ asset('images/Logo-login-SIGMAH.png') }}" alt="SIMAH">
            </div>

            <!-- Botão de voltar -->
            <div class="back-btn-container">
                <a href="{{ url('/') }}" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar ao Início</span>
                </a>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h1>Upload CSV CNARH</h1>
                    <p>Importe arquivos CSV ou TXT do sistema CNARH para processamento</p>
                </div>

                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf

                    <!-- Campo: Arquivo CSV/TXT -->
                    <div class="form-group">
                        <label for="file">Arquivo CSV/TXT</label>
                        <div class="file-input-container">
                            <div class="file-input-wrapper">
                                <i class="fas fa-file-csv"></i>
                                <label for="file" class="file-input-label" id="fileLabel"
                                    style="margin-bottom: 0px;">
                                    <span id="fileText">Clique para selecionar ou arraste um arquivo</span>
                                </label>
                                <input type="file" name="file" id="file" class="file-input"
                                    accept=".csv,.txt" required>
                            </div>
                            <span id="fileName" class="file-name"></span>
                            <small style="display: block; margin-top: 5px; color: #666; padding-left: 45px;">
                                Formatos aceitos: .csv, .txt (Máx: 100MB)
                            </small>
                        </div>
                    </div>

                    <!-- Botão de Submit -->
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span class="btn-text" id="btnText">Fazer Upload e Importar</span>
                        <i class="fas fa-spinner fa-spin btn-spinner" id="btnSpinner" style="display: none;"></i>
                    </button>

                    <!-- Barra de Progresso (opcional) -->
                    <div id="progressContainer" class="progress-container" style="display: none;">
                        <div id="progressBar" class="progress-bar" style="width: 0%"></div>
                    </div>
                </form>

                <!-- Resultado do Processamento -->
                <div id="result" class="result-container" style="display: none;"></div>
            </div>
        </div>

        <!-- Lado direito - Informações CNARH -->
        <div class="upload-info-section">
            <div class="info-container">
                <h2>Sistema CNARH</h2>
                <p>
                    Cadastro Nacional de Usuários de Recursos Hídricos -
                    Importe dados para análise e gerenciamento de recursos hídricos.
                </p>

                <div class="info-list">
                    <h3>Especificações do Arquivo:</h3>
                    <ul>
                        <li>Arquivo CSV ou TXT com codificação UTF-8</li>
                        <li>Delimitador: ponto e vírgula (;)</li>
                        <li>Colunas específicas do CNARH</li>
                        <li>Linha de cabeçalho obrigatória</li>
                        <li>Tamanho máximo: 100MB</li>
                    </ul>
                </div>

                <div class="info-list">
                    <h3>Processamento Automático:</h3>
                    <ul>
                        <li>Validação da estrutura do arquivo</li>
                        <li>Verificação de codificação</li>
                        <li>Importação em lote otimizada</li>
                        <li>Log de processamento detalhado</li>
                        <li>Notificação de conclusão</li>
                    </ul>
                </div>

                <div class="info-list">
                    <h3>Atenção:</h3>
                    <ul>
                        <li>O processo pode levar vários minutos</li>
                        <li>Não feche a página durante o processamento</li>
                        <li>Verifique o log para detalhes completos</li>
                        <li>Mantenha o arquivo original como backup</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Botão flutuante para mobile -->
        <a href="{{ url('/') }}" class="floating-back-btn">
            <div class="btn-circle">
                <i class="fas fa-home"></i>
            </div>
            <span class="btn-label">Início</span>
        </a>
    </div>

    <script>
        // Elementos DOM
        const uploadForm = document.getElementById('uploadForm');
        const fileInput = document.getElementById('file');
        const fileLabel = document.getElementById('fileLabel');
        const fileName = document.getElementById('fileName');
        const fileText = document.getElementById('fileText');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        const resultDiv = document.getElementById('result');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');

        // Atualizar visual do file input quando arquivo for selecionado
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                fileLabel.classList.add('has-file');
                fileText.textContent = 'Arquivo selecionado';
                fileName.textContent = `📄 ${file.name} (${formatBytes(file.size)})`;

                // Validação de tamanho (100MB)
                if (file.size > 100 * 1024 * 1024) {
                    showResult('Arquivo muito grande. Tamanho máximo permitido: 100MB', 'error');
                    fileInput.value = '';
                    fileLabel.classList.remove('has-file');
                    fileText.textContent = 'Clique para selecionar ou arraste um arquivo';
                    fileName.textContent = '';
                }
            } else {
                fileLabel.classList.remove('has-file');
                fileText.textContent = 'Clique para selecionar ou arraste um arquivo';
                fileName.textContent = '';
            }
        });

        // Formatar tamanho do arquivo
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        // Drag and drop para arquivo
        fileLabel.addEventListener('dragover', function(e) {
            e.preventDefault();
            fileLabel.style.borderColor = '#165B9C';
            fileLabel.style.background = '#f0f7ff';
        });

        fileLabel.addEventListener('dragleave', function(e) {
            e.preventDefault();
            if (!fileInput.files[0]) {
                fileLabel.style.borderColor = '#ddd';
                fileLabel.style.background = '#fafafa';
            }
        });

        fileLabel.addEventListener('drop', function(e) {
            e.preventDefault();
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
            fileLabel.style.borderColor = fileInput.files[0] ? '#007952' : '#ddd';
            fileLabel.style.background = fileInput.files[0] ? '#f0fff5' : '#fafafa';
        });

        // Submit do formulário (MANTENDO A LÓGICA ORIGINAL)
        uploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validações
            if (!fileInput.files[0]) {
                showResult('Por favor, selecione um arquivo CSV ou TXT.', 'error');
                return;
            }

            // Mostrar loading
            submitBtn.classList.add('loading');
            btnSpinner.style.display = 'block';
            btnText.style.opacity = '0';
            submitBtn.disabled = true;
            progressContainer.style.display = 'block';
            progressBar.style.width = '10%';

            // Preparar form data
            const formData = new FormData(this);

            try {
                // Simular progresso inicial
                setTimeout(() => {
                    progressBar.style.width = '30%';
                }, 500);

                // Fazer a requisição - MANTENDO O ENDPOINT ORIGINAL
                const response = await fetch('/cnarh/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                // Simular progresso durante a resposta
                progressBar.style.width = '70%';

                const result = await response.json();

                // Progresso final
                progressBar.style.width = '100%';

                if (result.success) {
                    showResult(`
                        <div class="success">
                            <div class="result-content">
                                <strong>✅ Importação concluída com sucesso!</strong>
                                <br><br>
                                <div class="result-item">
                                    <span>Total de registros importados:</span>
                                    <span><strong>${result.total_imported}</strong></span>
                                </div>
                                <div class="result-item">
                                    <span>Duração do processamento:</span>
                                    <span>${result.duration_seconds} segundos</span>
                                </div>
                                <div class="result-item">
                                    <span>Mensagem:</span>
                                    <span>${result.message || 'Processamento concluído sem erros.'}</span>
                                </div>
                            </div>
                        </div>
                    `, 'success');

                    // Resetar formulário após sucesso
                    setTimeout(() => {
                        progressBar.style.width = '0%';
                        progressContainer.style.display = 'none';
                    }, 2000);

                } else {
                    showResult(`
                        <div class="error">
                            <strong>❌ Erro no Processamento:</strong><br><br>
                            ${result.message}
                        </div>
                    `, 'error');
                    progressBar.style.width = '0%';
                    progressContainer.style.display = 'none';
                }

            } catch (error) {
                showResult(`
                    <div class="error">
                        <strong>❌ Erro na requisição:</strong><br><br>
                        ${error.message}
                    </div>
                `, 'error');
                progressBar.style.width = '0%';
                progressContainer.style.display = 'none';
            } finally {
                // Remover loading
                submitBtn.classList.remove('loading');
                btnSpinner.style.display = 'none';
                btnText.style.opacity = '1';
                submitBtn.disabled = false;

                // Esconder progresso após 3 segundos
                setTimeout(() => {
                    progressContainer.style.display = 'none';
                    progressBar.style.width = '0%';
                }, 3000);
            }
        });

        // Função para mostrar resultado
        function showResult(message, type = 'info') {
            resultDiv.innerHTML = message;
            resultDiv.style.display = 'block';
            resultDiv.className = 'result-container';

            // Aplicar classe de tipo
            if (type === 'processing') {
                resultDiv.classList.add('processing');
            } else if (type === 'success') {
                resultDiv.classList.add('success');
            } else if (type === 'error') {
                resultDiv.classList.add('error');
            }

            // Scroll suave para o resultado
            resultDiv.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            // Adicionar máscara para aceitar apenas CSV/TXT
            fileInput.addEventListener('input', function() {
                const file = this.files[0];
                if (file) {
                    const validExtensions = ['.csv', '.txt'];
                    const fileName = file.name.toLowerCase();
                    const isValid = validExtensions.some(ext => fileName.endsWith(ext));

                    if (!isValid) {
                        showResult('Por favor, selecione apenas arquivos CSV ou TXT.', 'error');
                        this.value = '';
                        fileLabel.classList.remove('has-file');
                        fileText.textContent = 'Clique para selecionar ou arraste um arquivo';
                        document.getElementById('fileName').textContent = '';
                    }
                }
            });

            // Adicionar timer para mensagem de processamento longo
            let processingTimer;
            uploadForm.addEventListener('submit', function() {
                processingTimer = setTimeout(() => {
                    if (resultDiv.classList.contains('processing')) {
                        resultDiv.innerHTML +=
                            '<br><br><small><i class="fas fa-info-circle"></i> Processamento em andamento... Isso pode levar alguns minutos.</small>';
                    }
                }, 5000);
            });

            // Limpar timer quando o resultado for exibido
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' &&
                        (resultDiv.classList.contains('success') || resultDiv.classList.contains(
                            'error'))) {
                        clearTimeout(processingTimer);
                    }
                });
            });

            observer.observe(resultDiv, {
                attributes: true,
                attributeFilter: ['class']
            });
        });
    </script>
</body>

</html>
