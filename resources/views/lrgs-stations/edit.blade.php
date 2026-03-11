<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editar Estação LRGS | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap");

        * {
            font-family: "Inter", sans-serif;
            text-decoration: none;
            color: inherit;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body { height: 100vh; overflow: hidden; }

        .edit-container {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        /* ===== LADO ESQUERDO ===== */
        .edit-form-section {
            flex: 1;
            min-width: 400px;
            max-width: 55%;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .form-scrollable-wrapper {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .form-container {
            width: 100%;
            max-width: 560px;
            margin: auto;
        }

        .back-btn-container {
            position: absolute;
            top: 30px;
            right: 30px;
            z-index: 10;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: transparent;
            color: #165b9c;
            border: 2px solid #165b9c;
            border-radius: 25px;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #165b9c;
            color: white;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
            margin-top: 20px;
        }

        .form-header h1 {
            font-size: 2rem;
            color: #333;
            font-weight: 700;
        }

        .form-header p {
            color: #666;
            font-size: 0.95rem;
            margin-top: 6px;
        }

        .form-section-header {
            margin: 25px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-section-header h3 {
            font-size: 1.1rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-section-header h3 i { color: #165b9c; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 6px;
        }

        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
            margin-bottom: 6px;
        }

        .form-group { margin-bottom: 16px; }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 500;
            color: #444;
            font-size: 0.9rem;
        }

        .input-with-icon { position: relative; }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #165b9c;
            font-size: 1rem;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            padding: 13px 13px 13px 42px;
            border: 1px solid #ccc;
            border-radius: 25px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #ffffff;
            color: #000;
        }

        .form-control:focus {
            outline: none;
            border-color: #165b9c;
            box-shadow: 0 0 0 3px rgba(22, 91, 156, 0.1);
        }

        .form-control[readonly] {
            background: #f5f5f5;
            cursor: not-allowed;
            color: #888;
        }

        .form-control-select {
            width: 100%;
            padding: 13px 13px 13px 42px;
            border: 1px solid #ccc;
            border-radius: 25px;
            font-size: 0.95rem;
            background: #fff;
            color: #000;
            cursor: pointer;
            appearance: none;
            transition: all 0.3s ease;
        }

        .form-control-select:focus {
            outline: none;
            border-color: #165b9c;
            box-shadow: 0 0 0 3px rgba(22, 91, 156, 0.1);
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper i.select-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #165b9c;
            font-size: 1rem;
            pointer-events: none;
            z-index: 2;
        }

        .select-wrapper i.chevron {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            font-size: 0.85rem;
            pointer-events: none;
        }

        /* Campos condicionais */
        .fields-curva { display: none; }
        .fields-curva.active { display: contents; }

        .alert-errors {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f1b0b7;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .submit-btn {
            flex: 1;
            padding: 15px;
            background: #007952;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-btn:hover {
            background: #006b47;
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0, 121, 82, 0.35);
        }

        .cancel-btn {
            padding: 15px 28px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .cancel-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* ===== LADO DIREITO ===== */
        .edit-info-section {
            flex: 1;
            background: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.65)),
                url("{{ asset('images/backgraund-loginpng.png') }}");
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: white;
        }

        .info-container { max-width: 480px; text-align: center; }

        .info-container h2 { font-size: 2rem; margin-bottom: 16px; }

        .info-container p {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 24px;
            opacity: 0.9;
        }

        .info-box {
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 20px 24px;
            text-align: left;
            border-left: 4px solid #007952;
            margin-top: 20px;
        }

        .info-box h3 { font-size: 1rem; margin-bottom: 12px; color: white; }

        .info-box ul { list-style: none; padding: 0; }

        .info-box li {
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .info-box li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #007952;
            font-weight: bold;
        }

        @media (max-width: 900px) {
            .edit-container { flex-direction: column; }
            .edit-form-section { max-width: 100%; overflow-y: auto; }
            .edit-info-section { display: none; }
            body { overflow: auto; height: auto; }
        }

        @media (max-width: 600px) {
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
            .form-scrollable-wrapper { padding: 24px 16px; }
        }
    </style>
</head>

<body>
    <div class="edit-container">

        <!-- Lado esquerdo - Formulário -->
        <div class="edit-form-section">
            <div class="form-scrollable-wrapper" style="position:relative;">

                <div class="back-btn-container">
                    <a href="{{ route('lrgs-stations.index') }}" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>

                <div class="form-container">

                    <div class="form-header">
                        <h1><i class="fas fa-satellite-dish" style="color:#165b9c;font-size:1.6rem;"></i> Editar Estação</h1>
                        <p>{{ $station->dcp_address }} — {{ $station->station_label }}</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert-errors">
                            <i class="fas fa-exclamation-triangle"></i>
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('lrgs-stations.update', $station->id) }}">
                        @csrf

                        <!-- Informações da Estação -->
                        <div class="form-section-header">
                            <h3><i class="fas fa-info-circle"></i> Informações da Estação</h3>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>DCP Address *</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-broadcast-tower"></i>
                                    <input type="text" name="dcp_address" class="form-control"
                                        value="{{ old('dcp_address', $station->dcp_address) }}"
                                        required maxlength="20">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Nome *</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-tag"></i>
                                    <input type="text" name="station_label" class="form-control"
                                        value="{{ old('station_label', $station->station_label) }}"
                                        required maxlength="255">
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Latitude</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <input type="text" class="form-control" value="{{ $station->latitude ?? '-' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Longitude</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <input type="text" class="form-control" value="{{ $station->longitude ?? '-' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Curva Chave -->
                        <div class="form-section-header">
                            <h3><i class="fas fa-chart-line"></i> Curva Chave</h3>
                        </div>

                        <div class="form-group">
                            <label>Selecionar Curva Chave</label>
                            <div class="select-wrapper">
                                <i class="fas fa-water select-icon"></i>
                                <select name="curva_chave" id="curva_chave" class="form-control-select" required>
                                    <option value="">-- Selecione --</option>
                                    <option value="1" {{ old('curva_chave', $station->curva_chave) == 1 ? 'selected' : '' }}>1ª Curva Chave</option>
                                    <option value="2" {{ old('curva_chave', $station->curva_chave) == 2 ? 'selected' : '' }}>2ª Curva Chave</option>
                                </select>
                                <i class="fas fa-chevron-down chevron"></i>
                            </div>
                        </div>

                        <!-- Campos 1ª Curva (A, B, H0) -->
                        <div id="fields-curva1" class="form-row fields-curva">
                            <div class="form-group">
                                <label>A</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-superscript"></i>
                                    <input type="number" name="a" id="a_curva1" class="form-control"
                                        value="{{ old('a', $station->a !== null ? rtrim(rtrim($station->a, '0'), '.') : '') }}"
                                        step="0.000000000000001" placeholder="0,000000000000000">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>B</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-superscript"></i>
                                    <input type="number" name="b" id="b_curva1" class="form-control"
                                        value="{{ old('b', $station->b !== null ? rtrim(rtrim($station->b, '0'), '.') : '') }}"
                                        step="0.000000000000001" placeholder="0,000000000000000">
                                </div>
                            </div>
                            <div class="form-group" style="grid-column: span 2;">
                                <label>H0</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-ruler-vertical"></i>
                                    <input type="number" name="h0" id="h0_curva1" class="form-control"
                                        value="{{ old('h0', $station->h0 !== null ? rtrim(rtrim($station->h0, '0'), '.') : '') }}"
                                        step="0.000000000000001" placeholder="0,000000000000000">
                                </div>
                            </div>
                        </div>

                        <!-- Campos 2ª Curva (A, B, C, H0) -->
                        <div id="fields-curva2" class="form-row-3 fields-curva">
                            <div class="form-group">
                                <label>A</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-superscript"></i>
                                    <input type="number" name="a" id="a_curva2" class="form-control"
                                        value="{{ old('a', $station->a !== null ? rtrim(rtrim($station->a, '0'), '.') : '') }}"
                                        step="0.000000000000001" placeholder="0,000000000000000">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>B</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-superscript"></i>
                                    <input type="number" name="b" id="b_curva2" class="form-control"
                                        value="{{ old('b', $station->b !== null ? rtrim(rtrim($station->b, '0'), '.') : '') }}"
                                        step="0.000000000000001" placeholder="0,000000000000000">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>C</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-superscript"></i>
                                    <input type="number" name="c" id="c_curva2" class="form-control"
                                        value="{{ old('c', $station->c !== null ? rtrim(rtrim($station->c, '0'), '.') : '') }}"
                                        step="0.000000000000001" placeholder="0,000000000000000">
                                </div>
                            </div>
                            <div class="form-group" style="grid-column: span 3;">
                                <label>H0</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-ruler-vertical"></i>
                                    <input type="number" name="h0" id="h0_curva2" class="form-control"
                                        value="{{ old('h0', $station->h0 !== null ? rtrim(rtrim($station->h0, '0'), '.') : '') }}"
                                        step="0.000000000000001" placeholder="0,000000000000000">
                                </div>
                            </div>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                            <a href="{{ route('lrgs-stations.index') }}" class="cancel-btn">Cancelar</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Lado direito - Info -->
        <div class="edit-info-section">
            <div class="info-container">
                <h2>Curva Chave</h2>
                <p>Configure os coeficientes da curva chave para cálculo da vazão a partir da cota observada.</p>

                <div class="info-box">
                    <h3>1ª Curva Chave</h3>
                    <ul>
                        <li>Q = a(h - h₀)^b</li>
                        <li>Coeficientes: A, B e H0</li>
                    </ul>
                </div>

                <div class="info-box" style="margin-top:16px;">
                    <h3>2ª Curva Chave</h3>
                    <ul>
                        <li>Q = a + (b × h) + (c × h²)</li>
                        <li>Coeficientes: A, B, C e H0</li>
                    </ul>
                </div>

            </div>
        </div>

    </div>

    <script>
        const curvaSelect = document.getElementById('curva_chave');
        const fields1 = document.getElementById('fields-curva1');
        const fields2 = document.getElementById('fields-curva2');

        function toggleFields() {
            const val = curvaSelect.value;

            fields1.style.display = 'none';
            fields2.style.display = 'none';

            // Disable inputs do bloco oculto para não enviar dados duplicados
            fields1.querySelectorAll('input').forEach(i => i.disabled = true);
            fields2.querySelectorAll('input').forEach(i => i.disabled = true);

            if (val === '1') {
                fields1.style.display = 'grid';
                fields1.querySelectorAll('input').forEach(i => i.disabled = false);
            } else if (val === '2') {
                fields2.style.display = 'grid';
                fields2.querySelectorAll('input').forEach(i => i.disabled = false);
            }
        }

        curvaSelect.addEventListener('change', toggleFields);
        toggleFields(); // Inicializar
    </script>
</body>

</html>
