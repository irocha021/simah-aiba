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

        .alert-errors {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f1b0b7;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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

        /* ===== TABELA CURVAS ===== */
        .rating-curves-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            margin-bottom: 16px;
        }

        .rating-curves-table th {
            background: #f0f4f8;
            color: #444;
            font-weight: 600;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 2px solid #dde3ea;
        }

        .rating-curves-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .rating-curves-table tr:last-child td { border-bottom: none; }

        .badge-curva {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .badge-curva-1 { background: #dbeafe; color: #1e40af; }
        .badge-curva-2 { background: #dcfce7; color: #166534; }

        .badge-aberto {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            background: #fef9c3;
            color: #854d0e;
        }

        .btn-add-curve {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            background: #165b9c;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 8px;
        }

        .btn-add-curve:hover {
            background: #124f8a;
            transform: translateY(-1px);
        }

        .btn-delete-curve {
            background: transparent;
            border: 1px solid #dc3545;
            color: #dc3545;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-delete-curve:hover {
            background: #dc3545;
            color: white;
        }

        .btn-edit-curve {
            background: transparent;
            border: 1px solid #165b9c;
            color: #165b9c;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-edit-curve:hover {
            background: #165b9c;
            color: white;
        }

        .empty-curves {
            text-align: center;
            color: #999;
            padding: 20px;
            font-size: 0.9rem;
        }

        /* ===== MODAL NOVA CURVA ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active { display: flex; }

        .modal-box {
            background: white;
            border-radius: 16px;
            padding: 32px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .modal-box h3 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-box h3 i { color: #165b9c; }

        .modal-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .modal-form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 14px;
        }

        .modal-form-group { margin-bottom: 14px; }

        .modal-form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #444;
            font-size: 0.85rem;
        }

        .modal-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }

        .modal-input:focus {
            outline: none;
            border-color: #165b9c;
            box-shadow: 0 0 0 3px rgba(22, 91, 156, 0.1);
        }

        .modal-select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 0.9rem;
            background: white;
            cursor: pointer;
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .modal-btn-submit {
            flex: 1;
            padding: 12px;
            background: #007952;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .modal-btn-submit:hover { background: #006b47; }

        .modal-btn-cancel {
            padding: 12px 24px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .modal-btn-cancel:hover { background: #5a6268; }

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
            .form-row, .modal-form-row, .modal-form-row-3 { grid-template-columns: 1fr; }
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

                    @if ($errors->hasAny(['dcp_address', 'station_label', 'latitude', 'longitude']))
                        <div class="alert-errors">
                            <i class="fas fa-exclamation-triangle"></i>
                            @foreach (['dcp_address', 'station_label', 'latitude', 'longitude'] as $field)
                                @foreach ($errors->get($field) as $error)
                                    {{ $error }}<br>
                                @endforeach
                            @endforeach
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert-success">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif

                    <!-- Formulário dados da estação -->
                    <form method="POST" action="{{ route('lrgs-stations.update', $station->id) }}">
                        @csrf

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

                        <div class="btn-container">
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                            <a href="{{ route('lrgs-stations.index') }}" class="cancel-btn">Cancelar</a>
                        </div>
                    </form>

                    <!-- Seção Curva Chave -->
                    <div class="form-section-header" style="margin-top:36px;">
                        <h3><i class="fas fa-chart-line"></i> Curvas Chave</h3>
                    </div>

                    @if ($ratingCurves->isEmpty())
                        <div class="empty-curves">
                            <i class="fas fa-chart-line" style="font-size:2rem;color:#ccc;display:block;margin-bottom:8px;"></i>
                            Nenhuma curva chave cadastrada.
                        </div>
                    @else
                        <table class="rating-curves-table">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>A</th>
                                    <th>B</th>
                                    <th>C</th>
                                    <th>H₀</th>
                                    <th>Início</th>
                                    <th>Fim</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ratingCurves as $curve)
                                    @php
                                        $isFuture  = $curve->starts_at->isFuture();
                                        $isCurrent = !$curve->starts_at->isFuture() && ($curve->ends_at === null || !$curve->ends_at->isPast());
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge-curva badge-curva-{{ $curve->curva_chave }}">
                                                {{ $curve->curva_chave }}ª Curva
                                            </span>
                                        </td>
                                        <td>{{ rtrim(rtrim($curve->a, '0'), '.') }}</td>
                                        <td>{{ rtrim(rtrim($curve->b, '0'), '.') }}</td>
                                        <td>{{ $curve->c !== null ? rtrim(rtrim($curve->c, '0'), '.') : '—' }}</td>
                                        <td>{{ $curve->h0 !== null ? rtrim(rtrim($curve->h0, '0'), '.') : '—' }}</td>
                                        <td>{{ $curve->starts_at->format('d/m/Y') }}</td>
                                        <td>
                                            @if ($curve->ends_at)
                                                {{ $curve->ends_at->format('d/m/Y') }}
                                            @else
                                                <span class="badge-aberto">em aberto</span>
                                            @endif
                                        </td>
                                        <td style="display:flex;gap:6px;align-items:center;">
                                            @if ($isFuture || $isCurrent)
                                                <button type="button" class="btn-edit-curve"
                                                    data-id="{{ $curve->id }}"
                                                    data-curva="{{ $curve->curva_chave }}"
                                                    data-a="{{ $curve->a }}"
                                                    data-b="{{ $curve->b }}"
                                                    data-c="{{ $curve->c ?? '' }}"
                                                    data-h0="{{ $curve->h0 ?? '' }}"
                                                    data-starts="{{ $curve->starts_at->format('Y-m-d') }}"
                                                    data-ends="{{ $curve->ends_at?->format('Y-m-d') ?? '' }}"
                                                    data-has-ends="{{ $curve->ends_at ? '1' : '0' }}"
                                                    data-is-current="{{ $isCurrent ? '1' : '0' }}"
                                                    onclick="openEditModal(this)">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            @endif
                                            @if ($isFuture)
                                                <form method="POST"
                                                    action="{{ route('lrgs-stations.rating-curves.destroy', [$station->id, $curve->id]) }}"
                                                    onsubmit="return confirm('Excluir esta curva chave?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-delete-curve">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <button class="btn-add-curve" onclick="openModal()">
                        <i class="fas fa-plus"></i> Nova Curva Chave
                    </button>

                </div>
            </div>
        </div>

        <!-- Lado direito - Info -->
        <div class="edit-info-section">
            <div class="info-container">
                <h2>Curva Chave</h2>
                <p>Gerencie os períodos de vigência das curvas chave para cálculo da vazão a partir da cota observada.</p>

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
                        <li>Coeficientes: A, B e C</li>
                    </ul>
                </div>

                <div class="info-box" style="margin-top:16px;">
                    <h3>Regras de período</h3>
                    <ul>
                        <li>Data início não pode ser no passado</li>
                        <li>Data fim em branco = período em aberto</li>
                        <li>Registros passados são somente leitura</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Editar Curva Chave -->
    <div class="modal-overlay" id="modalEditCurva">
        <div class="modal-box">
            <h3><i class="fas fa-pen"></i> Editar Curva Chave</h3>

            <form method="POST" id="editCurvaForm">
                @csrf

                <div id="edit_errors" style="display:none;background:#f8d7da;color:#721c24;border:1px solid #f1b0b7;padding:12px 16px;border-radius:10px;margin-bottom:14px;font-size:0.85rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="edit_errors_text"></span>
                </div>

                <div id="edit_current_notice" style="display:none;background:#dbeafe;border:1px solid #93c5fd;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:0.85rem;color:#1e40af;">
                    <i class="fas fa-info-circle"></i>
                    Como o período está corrente, não é possível editar os valores da curva chave. Caso queira antecipar o fim deste período, altere a data de fim do período.
                </div>

                <div class="modal-form-group">
                    <label>Tipo de Curva *</label>
                    <select name="curva_chave" id="edit_curva_chave" class="modal-select" required>
                        <option value="1">1ª Curva Chave — Q = a(h - h₀)^b</option>
                        <option value="2">2ª Curva Chave — Q = a + (b × h) + (c × h²)</option>
                    </select>
                </div>

                <!-- Campos 1ª Curva -->
                <div id="edit_fields_1" style="display:none;">
                    <div class="modal-form-row">
                        <div class="modal-form-group">
                            <label>A *</label>
                            <input type="number" name="a" id="edit_a1" class="modal-input"
                                step="0.000000000000001" placeholder="0.000000000000000">
                        </div>
                        <div class="modal-form-group">
                            <label>B *</label>
                            <input type="number" name="b" id="edit_b1" class="modal-input"
                                step="0.000000000000001" placeholder="0.000000000000000">
                        </div>
                    </div>
                    <div class="modal-form-group">
                        <label>H₀ *</label>
                        <input type="number" name="h0" id="edit_h0_1" class="modal-input"
                            step="0.000000000000001" placeholder="0.000000000000000">
                    </div>
                </div>

                <!-- Campos 2ª Curva -->
                <div id="edit_fields_2" style="display:none;">
                    <div class="modal-form-row-3">
                        <div class="modal-form-group">
                            <label>A *</label>
                            <input type="number" name="a" id="edit_a2" class="modal-input"
                                step="0.000000000000001" placeholder="0.000000000000000">
                        </div>
                        <div class="modal-form-group">
                            <label>B *</label>
                            <input type="number" name="b" id="edit_b2" class="modal-input"
                                step="0.000000000000001" placeholder="0.000000000000000">
                        </div>
                        <div class="modal-form-group">
                            <label>C *</label>
                            <input type="number" name="c" id="edit_c2" class="modal-input"
                                step="0.000000000000001" placeholder="0.000000000000000">
                        </div>
                    </div>
                </div>

                <div class="modal-form-row">
                    <div class="modal-form-group">
                        <label>Data Início *</label>
                        <input type="date" name="starts_at" id="edit_starts_at" class="modal-input"
                            min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="modal-form-group">
                        <label>Data Fim <span style="color:#999;font-weight:400;">(opcional)</span></label>
                        <input type="date" name="ends_at" id="edit_ends_at" class="modal-input">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="modal-btn-submit">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                    <button type="button" class="modal-btn-cancel" onclick="closeEditModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Nova Curva Chave -->
    <div class="modal-overlay" id="modalNovaCurva">
        <div class="modal-box">
            <h3><i class="fas fa-chart-line"></i> Nova Curva Chave</h3>

            <form method="POST" action="{{ route('lrgs-stations.rating-curves.store', $station->id) }}">
                @csrf

                @if ($errors->hasAny(['curva_chave', 'a', 'b', 'c', 'h0', 'starts_at', 'ends_at', 'close_previous_ends_at']))
                <div style="background:#f8d7da;color:#721c24;border:1px solid #f1b0b7;padding:12px 16px;border-radius:10px;margin-bottom:14px;font-size:0.85rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                    @foreach (['curva_chave', 'a', 'b', 'c', 'h0', 'starts_at', 'ends_at', 'close_previous_ends_at'] as $field)
                        @foreach ($errors->get($field) as $error)
                            {{ $error }}<br>
                        @endforeach
                    @endforeach
                </div>
                @endif

                @if ($openCurve)
                <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:0.83rem;color:#92400e;">
                    <i class="fas fa-info-circle"></i>
                    A curva {{ $openCurve->curva_chave }}ª em aberto (desde {{ $openCurve->starts_at->format('d/m/Y') }}) será encerrada automaticamente no dia anterior à data de início informada.
                </div>
                @endif

                <div class="modal-form-group">
                    <label>Tipo de Curva *</label>
                    <select name="curva_chave" id="modal_curva_chave" class="modal-select" required onchange="toggleModalFields()">
                        <option value="">-- Selecione --</option>
                        <option value="1" {{ old('curva_chave') == '1' ? 'selected' : '' }}>1ª Curva Chave — Q = a(h - h₀)^b</option>
                        <option value="2" {{ old('curva_chave') == '2' ? 'selected' : '' }}>2ª Curva Chave — Q = a + (b × h) + (c × h²)</option>
                    </select>
                </div>

                <!-- Campos 1ª Curva -->
                <div id="modal_fields_1" style="display:none;">
                    <div class="modal-form-row">
                        <div class="modal-form-group">
                            <label>A *</label>
                            <input type="number" name="a" id="modal_a1" class="modal-input"
                                value="{{ old('a') }}" step="0.000000000000001" placeholder="0.000000000000000">
                        </div>
                        <div class="modal-form-group">
                            <label>B *</label>
                            <input type="number" name="b" id="modal_b1" class="modal-input"
                                value="{{ old('b') }}" step="0.000000000000001" placeholder="0.000000000000000">
                        </div>
                    </div>
                    <div class="modal-form-group">
                        <label>H₀ *</label>
                        <input type="number" name="h0" id="modal_h0_1" class="modal-input"
                            value="{{ old('h0') }}" step="0.000000000000001" placeholder="0.000000000000000">
                    </div>
                </div>

                <!-- Campos 2ª Curva -->
                <div id="modal_fields_2" style="display:none;">
                    <div class="modal-form-row-3">
                        <div class="modal-form-group">
                            <label>A *</label>
                            <input type="number" name="a" id="modal_a2" class="modal-input"
                                value="{{ old('a') }}" step="0.000000000000001" placeholder="0.000000000000000">
                        </div>
                        <div class="modal-form-group">
                            <label>B *</label>
                            <input type="number" name="b" id="modal_b2" class="modal-input"
                                value="{{ old('b') }}" step="0.000000000000001" placeholder="0.000000000000000">
                        </div>
                        <div class="modal-form-group">
                            <label>C *</label>
                            <input type="number" name="c" id="modal_c2" class="modal-input"
                                value="{{ old('c') }}" step="0.000000000000001" placeholder="0.000000000000000">
                        </div>
                    </div>
                </div>

                <div class="modal-form-row">
                    <div class="modal-form-group">
                        <label>Data Início *</label>
                        <input type="date" name="starts_at" id="modal_starts_at" class="modal-input"
                            value="{{ old('starts_at') }}" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="modal-form-group">
                        <label>Data Fim <span style="color:#999;font-weight:400;">(opcional)</span></label>
                        <input type="date" name="ends_at" id="modal_ends_at" class="modal-input"
                            value="{{ old('ends_at') }}">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="modal-btn-submit">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                    <button type="button" class="modal-btn-cancel" onclick="closeModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modalNovaCurva').classList.add('active');
        }

        function closeModal() {
            document.getElementById('modalNovaCurva').classList.remove('active');
        }

        function toggleModalFields() {
            const val = document.getElementById('modal_curva_chave').value;
            const f1 = document.getElementById('modal_fields_1');
            const f2 = document.getElementById('modal_fields_2');

            f1.style.display = 'none';
            f2.style.display = 'none';

            // Desabilita todos os inputs dos blocos ocultos
            f1.querySelectorAll('input').forEach(i => i.disabled = true);
            f2.querySelectorAll('input').forEach(i => i.disabled = true);

            if (val === '1') {
                f1.style.display = 'block';
                f1.querySelectorAll('input').forEach(i => i.disabled = false);
            } else if (val === '2') {
                f2.style.display = 'block';
                f2.querySelectorAll('input').forEach(i => i.disabled = false);
            }
        }

        // Fecha modal clicando fora
        document.getElementById('modalNovaCurva').addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });

        // Garante ends_at >= starts_at
        document.getElementById('modal_starts_at').addEventListener('change', function () {
            document.getElementById('modal_ends_at').min = this.value;
        });

        // Fecha modal de edição clicando fora
        document.getElementById('modalEditCurva').addEventListener('click', function (e) {
            if (e.target === this) closeEditModal();
        });

        let currentEditIsCurrent = false;

        function openEditModal(btn) {
            const id        = btn.dataset.id;
            const curva     = btn.dataset.curva;
            const a         = btn.dataset.a;
            const b         = btn.dataset.b;
            const c         = btn.dataset.c;
            const h0        = btn.dataset.h0;
            const starts    = btn.dataset.starts;
            const ends      = btn.dataset.ends;
            const hasEnds   = btn.dataset.hasEnds === '1';
            const isCurrent = btn.dataset.isCurrent === '1';
            currentEditIsCurrent = isCurrent;

            const form = document.getElementById('editCurvaForm');
            form.action = `/lrgs-stations/{{ $station->id }}/rating-curves/${id}`;

            document.getElementById('edit_curva_chave').value = curva;
            document.getElementById('edit_starts_at').value   = starts;
            document.getElementById('edit_ends_at').value     = ends;

            // Período corrente: bloqueia tudo exceto ends_at
            const notice = document.getElementById('edit_current_notice');
            if (isCurrent) {
                notice.style.display = 'block';
                document.getElementById('edit_curva_chave').disabled = true;
                document.getElementById('edit_starts_at').disabled   = true;
                document.getElementById('edit_ends_at').disabled     = false;
                document.getElementById('edit_ends_at').min          = '{{ date('Y-m-d') }}';
                // Desabilita campos de coeficientes
                document.querySelectorAll('#edit_fields_1 input, #edit_fields_2 input').forEach(i => i.disabled = true);
                document.getElementById('edit_fields_1').style.display = 'none';
                document.getElementById('edit_fields_2').style.display = 'none';
            } else {
                notice.style.display = 'none';
                document.getElementById('edit_curva_chave').disabled = false;
                document.getElementById('edit_starts_at').disabled   = hasEnds;
                document.getElementById('edit_ends_at').disabled     = hasEnds;
                toggleEditFields(curva, a, b, c, h0);
            }

            // Reset error div
            const errEl = document.getElementById('edit_errors');
            errEl.style.display = 'none';
            document.getElementById('edit_errors_text').textContent = '';

            document.getElementById('modalEditCurva').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('modalEditCurva').classList.remove('active');
        }

        function toggleEditFields(curva, a, b, c, h0) {
            const f1 = document.getElementById('edit_fields_1');
            const f2 = document.getElementById('edit_fields_2');

            f1.style.display = 'none';
            f2.style.display = 'none';
            f1.querySelectorAll('input').forEach(i => i.disabled = true);
            f2.querySelectorAll('input').forEach(i => i.disabled = true);

            if (curva === '1') {
                f1.style.display = 'block';
                f1.querySelectorAll('input').forEach(i => i.disabled = false);
                document.getElementById('edit_a1').value  = a;
                document.getElementById('edit_b1').value  = b;
                document.getElementById('edit_h0_1').value = h0;
            } else if (curva === '2') {
                f2.style.display = 'block';
                f2.querySelectorAll('input').forEach(i => i.disabled = false);
                document.getElementById('edit_a2').value = a;
                document.getElementById('edit_b2').value = b;
                document.getElementById('edit_c2').value = c;
            }
        }

        document.getElementById('edit_curva_chave').addEventListener('change', function () {
            toggleEditFields(this.value, '', '', '', '');
        });

        document.getElementById('edit_starts_at').addEventListener('change', function () {
            document.getElementById('edit_ends_at').min = this.value;
        });

        // Submit do modal de edição — AJAX quando período corrente
        document.getElementById('editCurvaForm').addEventListener('submit', function (e) {
            if (!currentEditIsCurrent) return; // período futuro: submit normal

            e.preventDefault();

            const form    = this;
            const endsAt  = document.getElementById('edit_ends_at').value;
            const errorEl = document.getElementById('edit_errors');

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ends_at: endsAt }),
            })
            .then(res => res.json().then(data => ({ status: res.status, data })))
            .then(({ status, data }) => {
                if (data.success) {
                    window.location.reload();
                } else {
                    errorEl.style.display = 'block';
                    document.getElementById('edit_errors_text').textContent = data.error ?? 'Erro ao salvar.';
                }
            })
            .catch(() => {
                errorEl.style.display = 'block';
                document.getElementById('edit_errors_text').textContent = 'Erro ao salvar.';
            });
        });

        // Abre modal de nova curva automaticamente se houver erros
        @if ($errors->hasAny(['curva_chave', 'a', 'b', 'c', 'h0', 'starts_at', 'ends_at']))
            openModal();
            toggleModalFields();
        @endif
    </script>
</body>

</html>
