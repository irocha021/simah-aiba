<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Upload DBF | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dbf-upload.css') }}">
</head>

<body>
    <div class="upload-container">
        <!-- Lado esquerdo - Formulário -->
        <div class="upload-form-section">
            <!-- Logo no topo -->
            <div class="top-logo">
                <img src="{{ asset('images/Logo-login-SIGMAH.png') }}" alt="SIMAH">
            </div>

            <!-- Botão de voltar (canto superior direito) -->
            <div class="back-btn-container">
                <a href="{{ url('/') }}" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar ao Início</span>
                </a>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h1>Upload DBF</h1>
                    <p>Importe arquivos DBF compactados do SIAGAS ou RIMAS</p>
                </div>

                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf

                    <!-- Campo: Origem -->
                    <div class="form-group">
                        <label for="source">Origem do Arquivo</label>
                        <div class="select-with-icon">
                            <i class="fas fa-database"></i>
                            <select name="source" id="source" class="form-select" required>
                                <option value="">Selecione a origem...</option>
                                <option value="siagas">SIAGAS</option>
                                <option value="rimas">RIMAS</option>
                            </select>
                        </div>
                    </div>

                    <!-- Campo: Arquivo ZIP -->
                    <div class="form-group">
                        <label for="file">Arquivo ZIP</label>
                        <div class="file-input-container">
                            <div class="file-input-wrapper">
                                <i class="fas fa-file-archive"></i>
                                <label for="file" class="file-input-label" id="fileLabel"
                                    style="margin-bottom: 0px">
                                    <span id="fileText">Clique para selecionar ou arraste um arquivo ZIP</span>
                                </label>
                                <input type="file" name="file" id="file" class="file-input" accept=".zip"
                                    required>
                            </div>
                            <span id="fileName" class="file-name"></span>
                            <small style="display: block; margin-top: 5px; color: #666; padding-left: 45px;">
                                Apenas arquivos .zip são aceitos
                            </small>
                        </div>
                    </div>

                    <!-- Botão de Submit -->
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span class="btn-text" id="btnText">Processar Upload</span>
                        <i class="fas fa-spinner fa-spin btn-spinner" id="btnSpinner" style="display: none;"></i>
                    </button>
                </form>

                <!-- Resultado do Processamento -->
                <div id="result" class="result-container" style="display: none;"></div>
            </div>
        </div>

        <!-- Lado direito - Informações -->
        <div class="upload-info-section">
            <div class="info-container">
                <h2>Como funciona?</h2>
                <p>
                    Importe arquivos DBF compactados em ZIP dos sistemas SIAGAS e RIMAS
                    para processamento e análise automatizada.
                </p>

                <div class="info-list">
                    <h3>Requisitos do Arquivo:</h3>
                    <ul>
                        <li>Arquivo deve estar compactado em formato ZIP</li>
                        <li>Deve conter um arquivo DBF válido</li>
                        <li>Tamanho máximo: 100MB</li>
                        <li>Estrutura de dados compatível</li>
                    </ul>
                </div>

                <div class="info-list">
                    <h3>Processamento:</h3>
                    <ul>
                        <li>Validação automática do formato</li>
                        <li>Extração e leitura do DBF</li>
                        <li>Importação para banco de dados</li>
                        <li>Relatório detalhado do processo</li>
                    </ul>
                </div>
            </div>
        </div>
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
        const sourceSelect = document.getElementById('source');

        // Atualizar visual do file input quando arquivo for selecionado
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                fileLabel.classList.add('has-file');
                fileText.textContent = 'Arquivo selecionado';
                fileName.textContent = `📁 ${file.name} (${formatBytes(file.size)})`;
            } else {
                fileLabel.classList.remove('has-file');
                fileText.textContent = 'Clique para selecionar ou arraste um arquivo ZIP';
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

        // Submit do formulário
        uploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validações
            if (!sourceSelect.value) {
                showResult('Por favor, selecione a origem do arquivo.', 'error');
                return;
            }

            if (!fileInput.files[0]) {
                showResult('Por favor, selecione um arquivo ZIP.', 'error');
                return;
            }

            // Mostrar loading
            submitBtn.classList.add('loading');
            btnSpinner.style.display = 'block';
            btnText.style.opacity = '0';
            submitBtn.disabled = true;

            // Preparar form data
            const formData = new FormData(this);

            try {
                const response = await fetch('/dbf-import/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (result.success) {
                    showResult(`
                        <div class="result-success">
                            <div class="result-content">
                                <strong>✅ Upload Processado com Sucesso!</strong>
                                <div class="result-item">
                                    <span>Origem:</span>
                                    <span><strong>${result.source.toUpperCase()}</strong></span>
                                </div>
                                <div class="result-item">
                                    <span>Arquivo detectado:</span>
                                    <span>${result.detected_dbf_file}</span>
                                </div>
                                <div class="result-item">
                                    <span>Total importado:</span>
                                    <span><strong>${result.total_imported} registros</strong></span>
                                </div>
                                <div class="result-item">
                                    <span>Duração do processo:</span>
                                    <span>${result.duration_seconds} segundos</span>
                                </div>
                            </div>
                        </div>
                    `, 'success');

                    // Resetar formulário após sucesso
                    setTimeout(() => {
                        uploadForm.reset();
                        fileLabel.classList.remove('has-file');
                        fileText.textContent = 'Clique para selecionar ou arraste um arquivo ZIP';
                        fileName.textContent = '';
                    }, 3000);

                } else {
                    showResult(
                        `<div class="result-error"><strong>❌ Erro no Processamento:</strong><br>${result.message}</div>`,
                        'error');
                }

            } catch (error) {
                showResult(
                    `<div class="result-error"><strong>❌ Erro de Conexão:</strong><br>${error.message}</div>`,
                    'error');
            } finally {
                // Remover loading
                submitBtn.classList.remove('loading');
                btnSpinner.style.display = 'none';
                btnText.style.opacity = '1';
                submitBtn.disabled = false;
            }
        });

        // Função para mostrar resultado
        function showResult(message, type = 'info') {
            resultDiv.innerHTML = message;
            resultDiv.style.display = 'block';
            resultDiv.className = 'result-container';

            if (type === 'success') {
                resultDiv.classList.add('result-success');
            } else if (type === 'error') {
                resultDiv.classList.add('result-error');
            }

            // Scroll suave para o resultado
            resultDiv.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            // Adicionar máscara para aceitar apenas ZIP
            fileInput.addEventListener('input', function() {
                const file = this.files[0];
                if (file && !file.name.toLowerCase().endsWith('.zip')) {
                    showResult('Por favor, selecione apenas arquivos ZIP.', 'error');
                    this.value = '';
                    fileLabel.classList.remove('has-file');
                    fileText.textContent = 'Clique para selecionar ou arraste um arquivo ZIP';
                    fileName.textContent = '';
                }
            });
        });
    </script>
</body>

</html>
