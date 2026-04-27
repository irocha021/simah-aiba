<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Importar SIMAH | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dbf-upload.css') }}">
    <style>
        .station-search-wrap { position: relative; }
        .station-search-wrap i.search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #165b9c; font-size: 1.1rem; z-index: 2; pointer-events: none; }
        .station-search-input { width: 100%; padding: 15px 15px 15px 45px; border: 1px solid #000; border-radius: 25px; font-size: 1rem; background: #fff; color: #000; outline: none; transition: all 0.3s; }
        .station-search-input:focus { border-color: #165b9c; box-shadow: 0 0 0 3px rgba(22,91,156,0.1); }
        .station-dropdown { display: none; position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); z-index: 100; max-height: 260px; overflow-y: auto; }
        .station-dropdown.open { display: block; }
        .station-option { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; cursor: pointer; transition: background 0.15s; border-bottom: 1px solid #f0f0f0; }
        .station-option:last-child { border-bottom: none; }
        .station-option:hover { background: #f0f7ff; }
        .station-option strong { display: block; font-size: 0.92rem; color: #222; }
        .station-option span { font-size: 0.8rem; color: #888; }
        .badge-ativa { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; background: #e6f7f0; color: #007952; }
        .badge-inativa { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; background: #f0f0f0; color: #999; }
        .station-selected-display { display: none; margin-top: 10px; padding: 12px 18px; background: #f0f7ff; border-radius: 12px; border-left: 4px solid #165b9c; font-size: 0.9rem; color: #165b9c; font-weight: 600; }
        .no-option { padding: 14px 18px; color: #aaa; font-size: 0.9rem; text-align: center; }
        .new-station-link { display: block; margin-top: 10px; font-size: 0.85rem; color: #007952; font-weight: 600; text-align: right; text-decoration: none; }
        .new-station-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="upload-container">

    <!-- Lado esquerdo - Formulário -->
    <div class="upload-form-section">
        <div class="top-logo">
            <img src="{{ asset('images/Logo-login-SIGMAH.png') }}" alt="SIMAH">
        </div>

        <div class="back-btn-container">
            <a href="{{ url('/') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar ao Início</span>
            </a>
        </div>

        <div class="form-container">
            <div class="form-header">
                <h1>Importar SIMAH</h1>
                <p>Selecione a estação e faça o upload do arquivo CSV</p>
            </div>

            <form id="uploadForm" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Campo: Estação -->
                <div class="form-group">
                    <label>Estação</label>
                    <div class="station-search-wrap" id="station-wrap">
                        <i class="fas fa-tint search-icon"></i>
                        <input type="text" id="station-search" class="station-search-input"
                            placeholder="Pesquisar por nome ou código..." autocomplete="off">
                        <input type="hidden" name="station_id" id="station_id">

                        <div class="station-dropdown" id="station-dropdown">
                            @forelse ($stations as $station)
                                <div class="station-option"
                                    data-id="{{ $station['id'] }}"
                                    data-name="{{ $station['name'] }}"
                                    data-code="{{ $station['code'] }}"
                                    data-ativa="{{ $station['ativa'] ? '1' : '0' }}">
                                    <div>
                                        <strong>{{ $station['name'] }}</strong>
                                        <span>{{ $station['code'] }}</span>
                                    </div>
                                    @if ($station['ativa'])
                                        <span class="badge-ativa">Ativa</span>
                                    @else
                                        <span class="badge-inativa">Inativa</span>
                                    @endif
                                </div>
                            @empty
                                <div class="no-option">Nenhuma estação cadastrada.</div>
                            @endforelse
                            <div class="no-option" id="no-results" style="display:none;">Nenhuma estação encontrada.</div>
                        </div>
                    </div>
                    <div class="station-selected-display" id="station-selected-display"></div>
                    <a href="{{ route('poco-simah.stations.create') }}" class="new-station-link">
                        <i class="fas fa-plus"></i> Cadastrar nova estação
                    </a>
                </div>

                <!-- Campo: Arquivo CSV -->
                <div class="form-group">
                    <label for="file">Arquivo CSV</label>
                    <div class="file-input-container">
                        <div class="file-input-wrapper">
                            <i class="fas fa-file-csv"></i>
                            <label for="file" class="file-input-label" id="fileLabel" style="margin-bottom:0">
                                <span id="fileText">Clique para selecionar ou arraste o arquivo CSV</span>
                            </label>
                            <input type="file" name="csv_file" id="file" class="file-input" accept=".csv,.txt" required>
                        </div>
                        <span id="fileName" class="file-name"></span>
                        <small style="display:block; margin-top:5px; color:#666; padding-left:45px;">
                            Apenas arquivos .csv são aceitos
                        </small>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn" disabled>
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
                    <li>Arquivo no formato CSV com separador <strong>;</strong></li>
                    <li>Colunas de data/hora local, UTC, pressão e temperatura</li>
                    <li>Tamanho máximo: 20MB</li>
                </ul>
            </div>

            <!-- <div class="info-list">
                <h3>Processamento:</h3>
                <ul>
                    <li>Validação automática do formato</li>
                    <li>Leituras duplicadas são atualizadas</li>
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
    var stations = @json($stations);
    var searchInput  = document.getElementById('station-search');
    var dropdown     = document.getElementById('station-dropdown');
    var stationId    = document.getElementById('station_id');
    var selectedDisp = document.getElementById('station-selected-display');
    var noResults    = document.getElementById('no-results');
    var submitBtn    = document.getElementById('submitBtn');
    var fileInput    = document.getElementById('file');
    var fileLabel    = document.getElementById('fileLabel');
    var fileName     = document.getElementById('fileName');
    var fileText     = document.getElementById('fileText');
    var btnText      = document.getElementById('btnText');
    var btnSpinner   = document.getElementById('btnSpinner');
    var resultDiv    = document.getElementById('result');
    var uploadForm   = document.getElementById('uploadForm');

    var selectedStation = null;

    function checkReady() {
        submitBtn.disabled = !(selectedStation && fileInput.files.length > 0);
    }

    // Abrir dropdown ao focar
    searchInput.addEventListener('focus', function () {
        filterOptions('');
        dropdown.classList.add('open');
    });

    searchInput.addEventListener('input', function () {
        selectedStation = null;
        stationId.value = '';
        selectedDisp.style.display = 'none';
        filterOptions(this.value.toLowerCase().trim());
        dropdown.classList.add('open');
        checkReady();
    });

    document.addEventListener('click', function (e) {
        if (!document.getElementById('station-wrap').contains(e.target)) {
            dropdown.classList.remove('open');
        }
    });

    function filterOptions(term) {
        var options = dropdown.querySelectorAll('.station-option');
        var visible = 0;
        options.forEach(function (opt) {
            var match = opt.dataset.name.toLowerCase().includes(term) || opt.dataset.code.toLowerCase().includes(term);
            opt.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        noResults.style.display = visible === 0 ? 'block' : 'none';
    }

    dropdown.querySelectorAll('.station-option').forEach(function (opt) {
        opt.addEventListener('click', function () {
            selectedStation = { id: this.dataset.id, name: this.dataset.name, code: this.dataset.code };
            stationId.value = this.dataset.id;
            searchInput.value = this.dataset.name + ' — ' + this.dataset.code;
            selectedDisp.textContent = '✓ ' + this.dataset.name + ' (' + this.dataset.code + ')';
            selectedDisp.style.display = 'block';
            dropdown.classList.remove('open');
            checkReady();
        });
    });

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            fileLabel.classList.add('has-file');
            fileText.textContent = 'Arquivo selecionado';
            fileName.textContent = '📁 ' + this.files[0].name;
        } else {
            fileLabel.classList.remove('has-file');
            fileText.textContent = 'Clique para selecionar ou arraste o arquivo CSV';
            fileName.textContent = '';
        }
        checkReady();
    });

    fileLabel.addEventListener('dragover', function (e) { e.preventDefault(); this.style.borderColor = '#165b9c'; this.style.background = '#f0f7ff'; });
    fileLabel.addEventListener('dragleave', function () { if (!fileInput.files[0]) { this.style.borderColor = '#ddd'; this.style.background = '#fafafa'; } });
    fileLabel.addEventListener('drop', function (e) {
        e.preventDefault();
        var files = e.dataTransfer.files;
        if (files.length > 0) { fileInput.files = files; fileInput.dispatchEvent(new Event('change')); }
    });

    uploadForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!selectedStation) { showResult('<div class="result-error"><strong>❌ Selecione uma estação.</strong></div>', 'error'); return; }
        if (!fileInput.files[0]) { showResult('<div class="result-error"><strong>❌ Selecione um arquivo CSV.</strong></div>', 'error'); return; }

        submitBtn.classList.add('loading');
        btnSpinner.style.display = 'block';
        btnText.style.opacity = '0';
        submitBtn.disabled = true;

        var formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('csv_file', fileInput.files[0]);

        try {
            var response = await fetch('/poco-simah/stations/' + selectedStation.id + '/import', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            var result = await response.json();

            var errorsHtml = '';
            if (result.errors && result.errors.length > 0) {
                errorsHtml = '<div style="margin-top:12px;"><strong>⚠️ ' + result.errors.length + ' linha(s) com erro:</strong><ul style="margin-top:8px;padding-left:20px;font-size:0.85rem;">';
                result.errors.forEach(function (e) { errorsHtml += '<li>' + e + '</li>'; });
                errorsHtml += '</ul></div>';
            }

            showResult('<div class="result-success"><div class="result-content"><strong>✅ Importação concluída!</strong><div class="result-item"><span>Estação:</span><span><strong>' + selectedStation.name + '</strong></span></div><div class="result-item"><span>Leituras importadas/atualizadas:</span><span><strong>' + result.imported + '</strong></span></div></div>' + errorsHtml + '</div>', 'success');

            uploadForm.reset();
            fileLabel.classList.remove('has-file');
            fileText.textContent = 'Clique para selecionar ou arraste o arquivo CSV';
            fileName.textContent = '';
            selectedStation = null;
            stationId.value = '';
            searchInput.value = '';
            selectedDisp.style.display = 'none';
            checkReady();

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
        if (type === 'error') resultDiv.classList.add('result-error');
        resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
</script>
</body>
</html>
