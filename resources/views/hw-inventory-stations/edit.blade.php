<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editar Estação | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap");

        * { font-family: "Inter", sans-serif; text-decoration: none; color: inherit; margin: 0; padding: 0; box-sizing: border-box; }

        body { height: 100vh; overflow: hidden; }

        .edit-container { display: flex; width: 100%; height: 100vh; }

        /* ===== LADO ESQUERDO ===== */
        .edit-form-section { flex: 1; min-width: 400px; max-width: 50%; background: #ffffff; display: flex; flex-direction: column; overflow-y: auto; }

        .form-scrollable-wrapper { flex: 1; padding: 40px; overflow-y: auto; }

        .form-container { width: 100%; max-width: 520px; margin: auto; }

        .back-btn-container { position: absolute; top: 30px; right: 30px; z-index: 10; }

        .back-btn { display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: transparent; color: #165b9c; border: 2px solid #165b9c; border-radius: 25px; font-size: 0.95rem; font-weight: 600; transition: all 0.3s ease; }

        .back-btn:hover { background: #165b9c; color: white; }

        .form-header { text-align: center; margin-bottom: 30px; margin-top: 20px; }

        .form-header h1 { font-size: 1.8rem; color: #333; font-weight: 700; }

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

        .form-control-plain { width: 100%; padding: 11px 14px; border-radius: 12px; font-size: 0.95rem; }

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

        .fields-row .form-control-plain { border: 1px solid #ccc; }

        .fields-row .form-control-plain:focus { outline: none; border-color: #165b9c; box-shadow: 0 0 0 3px rgba(22,91,156,0.1); }

        .fields-row label { font-size: 0.82rem; }

        .btn-container { display: flex; gap: 15px; margin-top: 30px; }

        .submit-btn { flex: 1; padding: 15px; background: #165b9c; color: white; border: none; border-radius: 25px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px; }

        .submit-btn:hover { background: #0e4278; transform: translateY(-2px); box-shadow: 0 7px 20px rgba(22,91,156,0.3); }

        .cancel-btn { padding: 15px 28px; background: #6c757d; color: white; border: none; border-radius: 25px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; text-align: center; }

        .cancel-btn:hover { background: #5a6268; transform: translateY(-2px); }

        /* ===== LADO DIREITO ===== */
        .edit-info-section { flex: 1; background: #0d2d52; overflow-y: auto; display: flex; flex-direction: column; padding: 40px; color: white; }

        .info-header { margin-bottom: 28px; text-align: center; }

        .info-header h2 { font-size: 1.5rem; font-weight: 700; }

        .info-header p { font-size: 0.9rem; opacity: 0.7; margin-top: 6px; }

        .info-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; margin: 3px; }

        .info-badge.telemetry { background: rgba(0,121,82,0.3); border: 1px solid #007952; color: #69f0ae; }

        .info-badge.water { background: rgba(22,91,156,0.3); border: 1px solid #4fc3f7; color: #4fc3f7; }

        .info-badge.operational { background: rgba(0,200,83,0.2); border: 1px solid #00c853; color: #69f0ae; }

        .info-badge.inactive { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.5); }

        .info-section-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; opacity: 0.5; margin: 20px 0 10px 0; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        .info-item { background: rgba(255,255,255,0.07); border-radius: 10px; padding: 12px 14px; }

        .info-item .info-label { font-size: 0.75rem; opacity: 0.55; margin-bottom: 4px; }

        .info-item .info-value { font-size: 0.92rem; font-weight: 600; word-break: break-word; }

        .info-item.full { grid-column: 1 / -1; }

        .info-divider { border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 18px 0; }

        /* Drenagem */
        .drainage-status { padding: 12px 16px; border-radius: 12px; margin-bottom: 14px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }
        .drainage-status small { opacity: 0.7; font-weight: 400; }
        .drainage-status-pending    { background: #fff8e1; border: 1px solid #ffe082; color: #8a6d00; }
        .drainage-status-processing { background: #e3f2fd; border: 1px solid #90caf9; color: #0d47a1; }
        .drainage-status-ready      { background: #e8f5e9; border: 1px solid #81c784; color: #1b5e20; }
        .drainage-status-failed     { background: #ffebee; border: 1px solid #ef9a9a; color: #b71c1c; }
        .drainage-error { margin-top: 8px; font-size: 0.82rem; opacity: 0.85; }
        .drainage-file-input { display: block; width: 100%; padding: 10px 12px; border: 1.5px dashed #cde3f7; border-radius: 12px; background: #f8fbff; font-size: 0.9rem; cursor: pointer; }
        .drainage-file-input:hover { border-color: #165b9c; }
        .drainage-btn { padding: 12px 22px; border: none; border-radius: 25px; font-size: 0.95rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease; }
        .drainage-btn-primary { background: #165b9c; color: white; }
        .drainage-btn-primary:hover { background: #0e4278; transform: translateY(-2px); }
        .drainage-btn-secondary { background: #6c757d; color: white; margin-top: 8px; }
        .drainage-btn-secondary:hover { background: #5a6268; }
        .drainage-info-message { margin-top: -6px; margin-bottom: 16px; padding: 12px 14px; background: #f0f7ff; border: 1px solid #cde3f7; border-radius: 10px; font-size: 0.85rem; color: #165b9c; display: flex; gap: 10px; align-items: flex-start; line-height: 1.4; }
        .drainage-info-message i { margin-top: 2px; flex-shrink: 0; }

        @media (max-width: 900px) { .edit-container { flex-direction: column; } .edit-form-section { max-width: 100%; } .edit-info-section { display: none; } body { overflow: auto; height: auto; } }
        @media (max-width: 600px) { .form-scrollable-wrapper { padding: 24px 16px; } }
    </style>
</head>

<body>
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
                        <h1><i class="fas fa-pen" style="color:#165b9c;font-size:1.4rem;"></i> Editar Estação</h1>
                        <p>{{ $station->station_code }} — {{ $station->station_name ?? 'Sem nome' }}</p>
                    </div>

                    @if (session('success'))
                        <div class="alert-errors" style="background:#e8f5e9; color:#1b5e20; border-color:#a5d6a7;">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert-errors">
                            <i class="fas fa-exclamation-triangle"></i>
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('hw-inventory-stations.update', $station->station_code) }}">
                        @csrf
                        @method('POST')

                        <div class="form-section-header">
                            <h3><i class="fas fa-tag"></i> Tipo da Estação</h3>
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <label class="checkbox-label telemetry-check" id="label-telemetry">
                                    <input type="checkbox" name="telemetry_station_type" value="1" id="cb-telemetry"
                                        {{ $station->telemetry_station_type ? 'checked' : '' }}>
                                    <i class="fas fa-satellite-dish"></i> Telemétrica
                                </label>
                                <label class="checkbox-label water-check" id="label-water">
                                    <input type="checkbox" name="water_quality_station_type" value="1" id="cb-water"
                                        {{ $station->water_quality_station_type ? 'checked' : '' }}>
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
                                        class="form-control-plain" value="{{ old('alfa_pond', $stationData->alfa_pond ?? '') }}" placeholder="0.00">
                                </div>
                                <div class="form-group" style="margin-bottom:0">
                                    <label>Q90</label>
                                    <input type="number" step="any" name="q_noventa"
                                        class="form-control-plain" value="{{ old('q_noventa', $stationData->q_noventa ?? '') }}" placeholder="0.00">
                                </div>
                                <div class="form-group" style="margin-bottom:0">
                                    <label>Vsup</label>
                                    <input type="number" step="any" name="vsup"
                                        class="form-control-plain" value="{{ old('vsup', $stationData->vsup ?? '') }}" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                            <a href="{{ route('hw-inventory-stations.index') }}" class="cancel-btn">Cancelar</a>
                        </div>

                    </form>

                    {{-- ===== ÁREA DE DRENAGEM ===== --}}
                    <div class="form-section-header">
                        <h3><i class="fas fa-map"></i> Área de Drenagem</h3>
                    </div>

                    @if ($drainage)
                        @php
                            $statusIcons = [
                                'pending'    => 'fa-clock',
                                'processing' => 'fa-spinner fa-spin',
                                'ready'      => 'fa-check-circle',
                                'failed'     => 'fa-exclamation-circle',
                            ];
                            $statusLabels = [
                                'pending'    => 'Aguardando processamento',
                                'processing' => 'Gerando área de drenagem...',
                                'ready'      => 'Área de drenagem disponível',
                                'failed'     => 'Falha ao gerar área de drenagem',
                            ];
                        @endphp
                        <div class="drainage-status drainage-status-{{ $drainage->status }}">
                            <i class="fas {{ $statusIcons[$drainage->status] ?? 'fa-info-circle' }}"></i>
                            <div style="flex:1;">
                                <strong>{{ $statusLabels[$drainage->status] ?? ucfirst($drainage->status) }}</strong>
                                @if ($drainage->tiles_generated_at)
                                    <small> — gerado em {{ $drainage->tiles_generated_at->format('d/m/Y H:i') }}</small>
                                @endif
                                @if ($drainage->status === 'failed' && $drainage->status_message)
                                    <div class="drainage-error">{{ $drainage->status_message }}</div>
                                @endif
                            </div>
                        </div>

                        @if (in_array($drainage->status, ['pending', 'processing']))
                            <div class="drainage-info-message">
                                <i class="fas fa-info-circle"></i>
                                <span>
                                    A área de drenagem está sendo gerada e isso pode levar alguns minutos.
                                    Você pode sair desta página e voltar depois — atualizamos o status automaticamente enquanto estiver aqui.
                                </span>
                            </div>
                        @endif
                    @endif

                    <form method="POST"
                        action="{{ route('hw-inventory-stations.drainage.upload', $station->station_code) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Shapefile ZIP <small style="opacity:0.6">(.shp + .shx + .dbf + .prj)</small></label>
                            <input type="file" name="zip_file" accept=".zip" required class="drainage-file-input">
                        </div>
                        <button type="submit" class="drainage-btn drainage-btn-primary">
                            <i class="fas fa-upload"></i>
                            {{ $drainage ? 'Substituir shapefile' : 'Enviar shapefile' }}
                        </button>
                    </form>

                    @if ($drainage && $drainage->status === 'ready')
                        <form method="POST"
                            action="{{ route('hw-inventory-stations.drainage.regenerate', $station->station_code) }}">
                            @csrf
                            <button type="submit" class="drainage-btn drainage-btn-secondary">
                                <i class="fas fa-redo"></i> Regerar área de drenagem
                            </button>
                        </form>
                    @endif

                </div>
            </div>
        </div>

        <!-- Lado direito - Resumo da estação -->
        <div class="edit-info-section">

            <div class="info-header">
                <h2><i class="fas fa-water"></i> {{ $station->station_name ?? 'Estação ' . $station->station_code }}</h2>
                <p>Dados vindos da API HidroWeb — somente leitura</p>
                <div style="margin-top:12px;">
                    @if ($station->telemetry_station_type)
                        <span class="info-badge telemetry"><i class="fas fa-satellite-dish"></i> Telemétrica</span>
                    @endif
                    @if ($station->water_quality_station_type)
                        <span class="info-badge water"><i class="fas fa-flask"></i> Qualidade de Água</span>
                    @endif
                    @if ($station->is_operational)
                        <span class="info-badge operational"><i class="fas fa-circle"></i> Operando</span>
                    @else
                        <span class="info-badge inactive"><i class="fas fa-circle"></i> Inativa</span>
                    @endif
                </div>
            </div>

            <hr class="info-divider">

            <div class="info-section-title">Identificação</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Código</div>
                    <div class="info-value">{{ $station->station_code }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">UF</div>
                    <div class="info-value">{{ $station->station_uf ?? '-' }} — {{ $station->station_uf_name ?? '-' }}</div>
                </div>
                <div class="info-item full">
                    <div class="info-label">Nome</div>
                    <div class="info-value">{{ $station->station_name ?? '-' }}</div>
                </div>
            </div>

            <div class="info-section-title">Bacia Hidrográfica</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Código da Bacia</div>
                    <div class="info-value">{{ $station->basin_code ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nome da Bacia</div>
                    <div class="info-value">{{ $station->basin_name ?? '-' }}</div>
                </div>
            </div>

            <div class="info-section-title">Localização</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Latitude</div>
                    <div class="info-value">{{ $station->latitude ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Longitude</div>
                    <div class="info-value">{{ $station->longitude ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Altitude</div>
                    <div class="info-value">{{ $station->altitude ? $station->altitude . ' m' : '-' }}</div>
                </div>
            </div>

            <div class="info-section-title">Responsável / Operador</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Responsável</div>
                    <div class="info-value">{{ $station->responsible_acronym ?? '-' }} ({{ $station->responsible_unit_uf ?? '-' }})</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Operador</div>
                    <div class="info-value">{{ $station->operator_abbreviation ?? '-' }} ({{ $station->operator_sub_unit_state ?? '-' }})</div>
                </div>
            </div>

            @if ($stationData)
                <hr class="info-divider">
                <div class="info-section-title">Dados Cadastrados</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Alfa Pond</div>
                        <div class="info-value">{{ $stationData->alfa_pond ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Q90</div>
                        <div class="info-value">{{ $stationData->q_noventa ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Vsup</div>
                        <div class="info-value">{{ $stationData->vsup ?? '-' }}</div>
                    </div>
                </div>
            @endif

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

        // ===== Polling do status da área de drenagem =====
        (function () {
            const statusBox = document.querySelector('.drainage-status');
            if (!statusBox) return;

            const isProcessing = statusBox.classList.contains('drainage-status-pending')
                              || statusBox.classList.contains('drainage-status-processing');
            if (!isProcessing) return;

            const code = {{ $station->station_code }};
            const url = `/hw-inventory-stations/${code}/drainage/status`;

            const interval = setInterval(async () => {
                try {
                    const r = await fetch(url);
                    const d = await r.json();
                    if (d.status === 'ready' || d.status === 'failed') {
                        clearInterval(interval);
                        window.location.reload();
                    }
                } catch (err) {
                    console.warn('Falha ao consultar status da área de drenagem', err);
                }
            }, 3000);
        })();
    </script>
</body>

</html>
