<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Importar Leituras | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dbf-upload.css') }}">
</head>
<body>
<div class="upload-container">

    <!-- Lado esquerdo - Formulário -->
    <div class="upload-form-section">
        <div class="top-logo">
            <img src="{{ asset('images/Logo-login-SIGMAH.png') }}" alt="SIMAH">
        </div>

        <div class="back-btn-container">
            <a href="{{ route('poco-simah.stations.index') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar às Estações</span>
            </a>
        </div>

        <div class="form-container">
            <div class="form-header">
                <h1>Importar Leituras</h1>
                <p>Upload do arquivo CSV do equipamento SIMAH</p>
            </div>

            <!-- Info da estação -->
            <div style="display:flex; align-items:center; gap:14px; padding:14px 20px; background:#f0f7ff; border-radius:12px; border-left:4px solid #165b9c; margin-bottom:30px;">
                <i class="fas fa-tint" style="font-size:1.4rem; color:#165b9c;"></i>
                <div>
                    <strong style="display:block; font-size:1rem; color:#165b9c;">{{ $station->name }}</strong>
                    <span style="font-size:0.85rem; color:#666;">Código: {{ $station->station_code }}</span>
                </div>
            </div>

            <form id="uploadForm" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="file">Arquivo CSV</label>
                    <div class="file-input-container">
                        <div class="file-input-wrapper">
                            <i class="fas fa-file-csv"></i>
                            <label for="file" class="file-input-label" id="fileLabel" style="margin-bottom:0">
                                <span id="fileText">Clique para selecionar ou arraste um arquivo CSV</span>
                            </label>
                            <input type="file" name="csv_file" id="file" class="file-input" accept=".csv,.txt" required>
                        </div>
                        <span id="fileName" class="file-name"></span>
                        <small style="display:block; margin-top:5px; color:#666; padding-left:45px;">
                            Apenas arquivos .csv são aceitos
                        </small>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="btn-text" id="btnText">Processar Upload</span>
                    <i class="fas fa-spinner fa-spin btn-spinner" id="btnSpinner" style="display:none;"></i>
                </button>
            </form>

            <div id="result" class="result-container" style="display:none;"></div>
        </div>
    </div>

    <!-- Lado direito - Info -->
    <div class="upload-info-section">
        <div class="info-container">
            <h2>Como funciona?</h2>
            <p>Importe arquivos CSV exportados pelo equipamento de poço SIMAH para armazenamento e análise das leituras.</p>

            <div class="info-list">
                <h3>Requisitos do Arquivo:</h3>
                <ul>
                    <li>Formato CSV com separador <strong>;</strong></li>
                    <li>Colunas: data/hora local, UTC, pressão e temperatura</li>
                    <li>Tamanho máximo: 20MB</li>
                </ul>
            </div>

            <!-- <div class="info-list">
                <h3>Processamento:</h3>
                <ul>
                    <li>Validação automática do formato</li>
                    <li>Leituras duplicadas são atualizadas automaticamente</li>
                    <li>Importação em lote para o banco de dados</li>
                    <li>Relatório detalhado do resultado</li>
                </ul>
            </div> -->
        </div>
    </div>

    <a href="{{ url('/') }}" class="floating-back-btn">
        <div class="btn-circle"><i class="fas fa-home"></i></div>
        <span class="btn-label">Início</span>
    </a>

</div>

<script>
    var fileInput = document.getElementById('file');
    var fileLabel = document.getElementById('fileLabel');
    var fileName  = document.getElementById('fileName');
    var fileText  = document.getElementById('fileText');
    var submitBtn = document.getElementById('submitBtn');
    var btnText   = document.getElementById('btnText');
    var btnSpinner = document.getElementById('btnSpinner');
    var resultDiv = document.getElementById('result');
    var uploadForm = document.getElementById('uploadForm');

    fileInput.addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (file) {
            fileLabel.classList.add('has-file');
            fileText.textContent = 'Arquivo selecionado';
            fileName.textContent = '📁 ' + file.name + ' (' + formatBytes(file.size) + ')';
        } else {
            fileLabel.classList.remove('has-file');
            fileText.textContent = 'Clique para selecionar ou arraste um arquivo CSV';
            fileName.textContent = '';
        }
    });

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024, sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    fileLabel.addEventListener('dragover', function (e) {
        e.preventDefault();
        fileLabel.style.borderColor = '#165b9c';
        fileLabel.style.background  = '#f0f7ff';
    });

    fileLabel.addEventListener('dragleave', function () {
        if (!fileInput.files[0]) {
            fileLabel.style.borderColor = '#ddd';
            fileLabel.style.background  = '#fafafa';
        }
    });

    fileLabel.addEventListener('drop', function (e) {
        e.preventDefault();
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });

    uploadForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!fileInput.files[0]) {
            showResult('<div class="result-error"><strong>❌ Selecione um arquivo CSV.</strong></div>', 'error');
            return;
        }

        submitBtn.classList.add('loading');
        btnSpinner.style.display = 'block';
        btnText.style.opacity = '0';
        submitBtn.disabled = true;

        var formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('csv_file', fileInput.files[0]);

        try {
            var response = await fetch('{{ route('poco-simah.stations.import.store', $station->id) }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            var result = await response.json();

            var errorsHtml = '';
            if (result.errors && result.errors.length > 0) {
                errorsHtml = '<div style="margin-top:12px;"><strong>⚠️ ' + result.errors.length + ' linha(s) com erro:</strong><ul style="margin-top:8px; padding-left:20px; font-size:0.85rem;">';
                result.errors.forEach(function (err) { errorsHtml += '<li>' + err + '</li>'; });
                errorsHtml += '</ul></div>';
            }

            showResult(
                '<div class="result-success"><div class="result-content">' +
                '<strong>✅ Importação concluída!</strong>' +
                '<div class="result-item"><span>Leituras importadas/atualizadas:</span><span><strong>' + result.imported + '</strong></span></div>' +
                '</div>' + errorsHtml + '</div>',
                'success'
            );

            uploadForm.reset();
            fileLabel.classList.remove('has-file');
            fileText.textContent = 'Clique para selecionar ou arraste um arquivo CSV';
            fileName.textContent = '';

        } catch (err) {
            showResult('<div class="result-error"><strong>❌ Erro:</strong><br>' + err.message + '</div>', 'error');
        } finally {
            submitBtn.classList.remove('loading');
            btnSpinner.style.display = 'none';
            btnText.style.opacity = '1';
            submitBtn.disabled = false;
        }
    });

    function showResult(message, type) {
        resultDiv.innerHTML = message;
        resultDiv.style.display = 'block';
        resultDiv.className = 'result-container';
        if (type === 'success') resultDiv.classList.add('result-success');
        if (type === 'error')   resultDiv.classList.add('result-error');
        resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
</script>
</body>
</html>
