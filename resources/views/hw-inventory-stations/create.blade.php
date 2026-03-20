<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nova Estação ANA/HidroWeb | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap");

        * { font-family: "Inter", sans-serif; text-decoration: none; color: inherit; margin: 0; padding: 0; box-sizing: border-box; }

        body { height: 100vh; overflow: hidden; }

        .edit-container { display: flex; width: 100%; height: 100vh; }

        /* ===== LADO ESQUERDO ===== */
        .edit-form-section { flex: 1; min-width: 400px; max-width: 55%; background: #ffffff; display: flex; flex-direction: column; overflow-y: auto; }

        .form-scrollable-wrapper { flex: 1; padding: 40px; overflow-y: auto; }

        .form-container { width: 100%; max-width: 560px; margin: auto; }

        .back-btn-container { position: absolute; top: 30px; right: 30px; z-index: 10; }

        .back-btn { display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: transparent; color: #165b9c; border: 2px solid #165b9c; border-radius: 25px; font-size: 0.95rem; font-weight: 600; transition: all 0.3s ease; }

        .back-btn:hover { background: #165b9c; color: white; }

        .form-header { text-align: center; margin-bottom: 30px; margin-top: 20px; }

        .form-header h1 { font-size: 2rem; color: #333; font-weight: 700; }

        .form-header p { color: #666; font-size: 0.95rem; margin-top: 6px; }

        .form-section-header { margin: 25px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }

        .form-section-header h3 { font-size: 1.1rem; color: #333; display: flex; align-items: center; gap: 8px; }

        .form-section-header h3 i { color: #165b9c; }

        .form-group { margin-bottom: 16px; }

        .form-group label { display: block; margin-bottom: 7px; font-weight: 500; color: #444; font-size: 0.9rem; }

        .input-with-icon { position: relative; }

        .input-with-icon i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #165b9c; font-size: 1rem; z-index: 2; }

        .form-control { width: 100%; padding: 13px 13px 13px 42px; border: 1px solid #ccc; border-radius: 25px; font-size: 0.95rem; transition: all 0.3s ease; background: #ffffff; color: #000; }

        .form-control:focus { outline: none; border-color: #165b9c; box-shadow: 0 0 0 3px rgba(22,91,156,0.1); }

        .form-control.is-invalid { border-color: #c0392b; }

        .invalid-feedback { color: #c0392b; font-size: 0.83rem; margin-top: 5px; padding-left: 14px; }

        .alert-errors { background: #f8d7da; color: #721c24; border: 1px solid #f1b0b7; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem; }

        /* Checkboxes */
        .checkbox-group { display: flex; gap: 16px; flex-wrap: wrap; }

        .checkbox-label { display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.95rem; font-weight: 500; padding: 12px 20px; border: 2px solid #ddd; border-radius: 25px; transition: all 0.2s; user-select: none; flex: 1; justify-content: center; }

        .checkbox-label input[type="checkbox"] { width: 17px; height: 17px; cursor: pointer; }

        .checkbox-label.telemetry-check:hover, .checkbox-label.telemetry-check.checked { border-color: #007952; background: #f0faf5; color: #007952; }

        .checkbox-label.water-check:hover, .checkbox-label.water-check.checked { border-color: #165b9c; background: #f0f7ff; color: #165b9c; }

        /* Campos opcionais */
        .optional-fields { margin-top: 8px; padding: 20px; background: #f8fbff; border-radius: 16px; border: 1.5px dashed #cde3f7; display: none; }

        .optional-fields-title { font-size: 0.88rem; color: #165b9c; font-weight: 600; margin-bottom: 14px; display: flex; align-items: center; gap: 6px; }

        .fields-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }

        .fields-row .form-control { padding: 11px 14px; border-radius: 12px; }

        .fields-row label { font-size: 0.82rem; }

        .btn-container { display: flex; gap: 15px; margin-top: 30px; }

        .submit-btn { flex: 1; padding: 15px; background: #165b9c; color: white; border: none; border-radius: 25px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px; }

        .submit-btn:hover { background: #0e4278; transform: translateY(-2px); box-shadow: 0 7px 20px rgba(22,91,156,0.3); }

        .cancel-btn { padding: 15px 28px; background: #6c757d; color: white; border: none; border-radius: 25px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; text-align: center; }

        .cancel-btn:hover { background: #5a6268; transform: translateY(-2px); }

        /* ===== LADO DIREITO ===== */
        .edit-info-section { flex: 1; background: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.65)), url("{{ asset('images/backgraund-loginpng.png') }}"); background-size: cover; background-position: center; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px; color: white; }

        .info-container { max-width: 480px; text-align: center; }

        .info-container h2 { font-size: 2rem; margin-bottom: 16px; }

        .info-container p { font-size: 1rem; line-height: 1.6; margin-bottom: 24px; opacity: 0.9; }

        .info-box { background: rgba(255,255,255,0.1); border-radius: 12px; padding: 20px 24px; text-align: left; border-left: 4px solid #165b9c; margin-top: 20px; }

        .info-box h3 { font-size: 1rem; margin-bottom: 12px; color: white; }

        .info-box ul { list-style: none; padding: 0; }

        .info-box li { margin-bottom: 8px; padding-left: 20px; position: relative; font-size: 0.9rem; opacity: 0.9; }

        .info-box li:before { content: "✓"; position: absolute; left: 0; color: #4fc3f7; font-weight: bold; }

        .info-box.green { border-left-color: #007952; }
        .info-box.green li:before { color: #69f0ae; }

        .loading-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 9999; flex-direction: column; align-items: center; justify-content: center; gap: 20px; }

        .loading-overlay.active { display: flex; }

        .loading-spinner { width: 52px; height: 52px; border: 5px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite; }

        .loading-text { color: white; font-size: 1rem; font-weight: 600; opacity: 0.9; }

        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 900px) { .edit-container { flex-direction: column; } .edit-form-section { max-width: 100%; overflow-y: auto; } .edit-info-section { display: none; } body { overflow: auto; height: auto; } }
        @media (max-width: 600px) { .form-scrollable-wrapper { padding: 24px 16px; } }
    </style>
</head>

<body>
    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-spinner"></div>
        <div class="loading-text">Consultando API HidroWeb da ANA...</div>
    </div>

    <div class="edit-container">

        <!-- Lado esquerdo - Formulário -->
        <div class="edit-form-section">
            <div class="form-scrollable-wrapper" style="position:relative;">

                <div class="back-btn-container">
                    <a href="{{ route('hw-inventory-stations.index') }}" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>

                <div class="form-container">

                    <div class="form-header">
                        <h1><i class="fas fa-water" style="color:#165b9c;font-size:1.6rem;"></i> Nova Estação</h1>
                        <p>Cadastre uma estação ANA/HidroWeb pelo código</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert-errors">
                            <i class="fas fa-exclamation-triangle"></i>
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('hw-inventory-stations.store') }}">
                        @csrf

                        <div class="form-section-header">
                            <h3><i class="fas fa-hashtag"></i> Identificação</h3>
                        </div>

                        <div class="form-group">
                            <label>Código da Estação *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-hashtag"></i>
                                <input type="number" name="station_code"
                                    class="form-control {{ $errors->has('station_code') ? 'is-invalid' : '' }}"
                                    value="{{ old('station_code') }}"
                                    placeholder="Ex: 46409990" required>
                            </div>
                            @error('station_code')
                                <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-section-header">
                            <h3><i class="fas fa-tag"></i> Tipo da Estação</h3>
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <label class="checkbox-label telemetry-check" id="label-telemetry">
                                    <input type="checkbox" name="telemetry_station_type" value="1" id="cb-telemetry"
                                        {{ old('telemetry_station_type') ? 'checked' : '' }}>
                                    <i class="fas fa-satellite-dish"></i> Telemétrica
                                </label>
                                <label class="checkbox-label water-check" id="label-water">
                                    <input type="checkbox" name="water_quality_station_type" value="1" id="cb-water"
                                        {{ old('water_quality_station_type') ? 'checked' : '' }}>
                                    <i class="fas fa-flask"></i> Qualidade de Água
                                </label>
                            </div>
                        </div>

                        <div class="optional-fields" id="telemetry-fields">
                            <div class="optional-fields-title">
                                <i class="fas fa-sliders-h"></i> Campos opcionais — Telemétrica
                            </div>
                            <div class="fields-row">
                                <div class="form-group" style="margin-bottom:0">
                                    <label>Alfa Pond</label>
                                    <input type="number" step="any" name="alfa_pond"
                                        class="form-control" value="{{ old('alfa_pond') }}" placeholder="0.00">
                                </div>
                                <div class="form-group" style="margin-bottom:0">
                                    <label>Q90</label>
                                    <input type="number" step="any" name="q_noventa"
                                        class="form-control" value="{{ old('q_noventa') }}" placeholder="0.00">
                                </div>
                                <div class="form-group" style="margin-bottom:0">
                                    <label>Vsup</label>
                                    <input type="number" step="any" name="vsup"
                                        class="form-control" value="{{ old('vsup') }}" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-search"></i> Buscar e Cadastrar
                            </button>
                            <a href="{{ route('hw-inventory-stations.index') }}" class="cancel-btn">Cancelar</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Lado direito -->
        <div class="edit-info-section">
            <div class="info-container">
                <h2>Nova Estação ANA/HidroWeb</h2>
                <p>Informe o código da estação. Os dados serão buscados automaticamente na API HidroWeb da ANA.</p>

                <div class="info-box">
                    <h3>Preenchimento Automático via API</h3>
                    <ul>
                        <li>Nome e localização</li>
                        <li>Bacia hidrográfica</li>
                        <li>Altitude, latitude e longitude</li>
                        <li>Responsável e operador</li>
                        <li>Status operacional</li>
                    </ul>
                </div>

                <div class="info-box green" style="margin-top:16px;">
                    <h3>Tipos de Estação</h3>
                    <ul>
                        <li>Telemétrica: importa séries de vazão/chuva</li>
                        <li>Qualidade de Água: importa parâmetros QA</li>
                        <li>A estação pode ser dos dois tipos</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

    <script>
        const cbTelemetry = document.getElementById('cb-telemetry');
        const cbWater     = document.getElementById('cb-water');
        const labelTel    = document.getElementById('label-telemetry');
        const labelWater  = document.getElementById('label-water');
        const telFields   = document.getElementById('telemetry-fields');

        function updateUI() {
            labelTel.classList.toggle('checked', cbTelemetry.checked);
            labelWater.classList.toggle('checked', cbWater.checked);
            telFields.style.display = cbTelemetry.checked ? 'block' : 'none';
        }

        cbTelemetry.addEventListener('change', updateUI);
        cbWater.addEventListener('change', updateUI);
        updateUI();

        document.querySelector('form').addEventListener('submit', function () {
            document.getElementById('loading-overlay').classList.add('active');
        });
    </script>
</body>

</html>
