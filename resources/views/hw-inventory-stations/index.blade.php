<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Estações ANA/HidroWeb | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap");

        * { font-family: "Inter", sans-serif; text-decoration: none; color: inherit; margin: 0; padding: 0; box-sizing: border-box; }

        body { background: #f0f7ff; min-height: 100vh; }

        .page-container { max-width: 1300px; margin: 0 auto; padding: 40px 30px; }

        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; }

        .page-header h1 { font-size: 1.8rem; color: #165b9c; font-weight: 700; }

        .page-header p { color: #666; font-size: 0.95rem; margin-top: 4px; }

        .btn-primary { display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: #165b9c; color: white; border: 2px solid #165b9c; border-radius: 25px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; }

        .btn-primary:hover { background: #0e4278; }

        .back-btn { display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: transparent; color: #165b9c; border: 2px solid #165b9c; border-radius: 25px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; }

        .back-btn:hover { background: #165b9c; color: white; }

        .alert { padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; font-size: 0.95rem; display: flex; align-items: center; gap: 10px; }

        .alert-success { background: #d4edda; color: #155724; border: 1px solid #b1dfbb; }

        .filter-bar { display: flex; gap: 12px; margin-bottom: 16px; align-items: center; flex-wrap: wrap; }

        .filter-input { padding: 10px 16px; border: 1.5px solid #cde3f7; border-radius: 25px; font-size: 0.9rem; color: #333; background: white; width: 260px; outline: none; transition: border-color 0.2s; }

        .filter-input:focus { border-color: #165b9c; box-shadow: 0 0 0 3px rgba(22,91,156,0.1); }

        .filter-tabs { display: flex; gap: 8px; }

        .filter-tab { padding: 8px 18px; border-radius: 25px; font-size: 0.88rem; font-weight: 600; border: 2px solid #cde3f7; background: white; color: #165b9c; cursor: pointer; text-decoration: none; transition: all 0.2s; }

        .filter-tab:hover, .filter-tab.active { background: #165b9c; color: white; border-color: #165b9c; }

        .filter-tab.active-green { background: #007952; color: white; border-color: #007952; }

        .table-card { background: white; border-radius: 16px; box-shadow: 0 2px 12px rgba(22,91,156,0.08); overflow: hidden; }

        table { width: 100%; border-collapse: collapse; }

        thead { background: #165b9c; color: white; }

        thead th { padding: 14px 16px; text-align: left; font-size: 0.85rem; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }

        tbody tr { border-bottom: 1px solid #f0f0f0; transition: background 0.2s; }

        tbody tr:hover { background: #f8fbff; }

        tbody td { padding: 13px 16px; font-size: 0.9rem; color: #333; }

        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; margin: 2px; }

        .badge-telemetry { background: #e6f7f0; color: #007952; }  /* verde */
        
        .badge-water     { background: #e3f0fb; color: #165b9c; }  /* azul  */

        .badge-operational { background: #d4edda; color: #155724; }

        .badge-inactive { background: #f0f0f0; color: #999; }

        .btn-edit { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; background: #165b9c; color: white; border-radius: 20px; font-size: 0.83rem; font-weight: 600; transition: all 0.2s; text-decoration: none; }

        .btn-edit:hover { background: #0e4278; color: white; }

        .btn-delete { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; background: #c0392b; color: white; border: none; border-radius: 20px; font-size: 0.83rem; font-weight: 600; transition: all 0.2s; cursor: pointer; }

        .btn-delete:hover { background: #a93226; }
    </style>
</head>

<body>
    <div class="page-container">

        <div class="page-header">
            <div>
                <h1><i class="fas fa-water"></i> Estações ANA/HidroWeb</h1>
                <p>Gerenciamento de estações cadastradas para importação</p>
            </div>
            <div style="display:flex; gap:12px; align-items:center;">
                <a href="{{ route('hw-inventory-stations.create') }}" class="btn-primary">
                    <i class="fas fa-plus"></i> Nova Estação
                </a>
                <a href="{{ url('/') }}" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="filter-bar">
            <input type="text" id="filter-search" class="filter-input" placeholder="Filtrar por código ou nome...">
            <div class="filter-tabs">
                <a href="{{ route('hw-inventory-stations.index') }}"
                class="filter-tab {{ $type === 'all' ? 'active' : '' }}">
                    Todas
                </a>
                <a href="{{ route('hw-inventory-stations.index', ['type' => 'telemetry']) }}"
                class="filter-tab {{ $type === 'telemetry' ? 'active' : '' }}" style="{{ $type === 'telemetry' ? 'background:#007952;border-color:#007952;' : '' }}">
                    <i class="fas fa-satellite-dish"></i> Telemetricas
                </a>
                <a href="{{ route('hw-inventory-stations.index', ['type' => 'water_quality']) }}"
                class="filter-tab {{ $type === 'water_quality' ? 'active' : '' }}">
                    <i class="fas fa-flask"></i> Qualidade de Água
                </a>
            </div>
        </div>

        <div class="table-card">
            <table id="stations-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>UF</th>
                        <th>Tipo(s)</th>
                        <th>Operando</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stations as $station)
                        <tr>
                            <td><strong>{{ $station->station_code }}</strong></td>
                            <td>{{ $station->station_name ?? '-' }}</td>
                            <td>{{ $station->station_uf ?? '-' }}</td>
                            <td>
                                @if ($station->telemetry_station_type)
                                    <span class="badge badge-telemetry"><i class="fas fa-satellite-dish"></i> Telemétrica</span>
                                @endif
                                @if ($station->water_quality_station_type)
                                    <span class="badge badge-water"><i class="fas fa-flask"></i> Qualidade de Água</span>
                                @endif
                                @if (!$station->telemetry_station_type && !$station->water_quality_station_type)
                                    <span class="badge badge-inactive">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($station->is_operational)
                                    <span class="badge badge-operational">Sim</span>
                                @else
                                    <span class="badge badge-inactive">Não</span>
                                @endif
                            </td>
                            <td style="display:flex; gap:8px; align-items:center;">
                                <a href="{{ route('hw-inventory-stations.edit', $station->station_code) }}" class="btn-edit">
                                    <i class="fas fa-pen"></i> Editar
                                </a>
                                <form method="POST" action="{{ route('hw-inventory-stations.destroy', $station->station_code) }}" style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete"
                                        onclick="return confirm('Tem certeza que deseja remover a estação {{ addslashes($station->station_name ?? $station->station_code) }}?')">
                                        <i class="fas fa-trash"></i> Remover
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 40px; color: #999;">
                                <i class="fas fa-water" style="font-size:2rem; margin-bottom:10px; display:block;"></i>
                                Nenhuma estação cadastrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <script>
        document.getElementById('filter-search').addEventListener('input', function () {
            const term = this.value.toLowerCase();
            document.querySelectorAll('#stations-table tbody tr').forEach(function (row) {
                const cells = row.querySelectorAll('td');
                if (!cells.length) return;
                const text = cells[0].textContent + ' ' + cells[1].textContent;
                row.style.display = text.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    </script>

    <a href="{{ url('/') }}" style="position:fixed;bottom:30px;right:30px;z-index:1000;display:flex;flex-direction:column;align-items:center;gap:5px;text-decoration:none;">
        <div style="width:60px;height:60px;border-radius:50%;background:#165b9c;color:white;display:flex;align-items:center;justify-content:center;font-size:1.5rem;box-shadow:0 4px 15px rgba(22,91,156,0.3);">
            <i class="fas fa-home"></i>
        </div>
    </a>
</body>

</html>
