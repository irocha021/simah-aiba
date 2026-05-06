<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Poços SIMAH | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap");
        * { font-family: "Inter", sans-serif; text-decoration: none; color: inherit; margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f7ff; min-height: 100vh; }
        .page-container { max-width: 1300px; margin: 0 auto; padding: 40px 30px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; }
        .page-header h1 { font-size: 1.8rem; color: #165b9c; font-weight: 700; }
        .page-header p { color: #666; font-size: 0.95rem; margin-top: 4px; }
        .back-btn { display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: transparent; color: #165b9c; border: 2px solid #165b9c; border-radius: 25px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; }
        .back-btn:hover { background: #165b9c; color: white; }
        .btn-primary { display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: #165b9c; color: white; border: 2px solid #165b9c; border-radius: 25px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; }
        .btn-primary:hover { background: #0e4278; border-color: #0e4278; color: white; }
        .alert { padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; font-size: 0.95rem; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #b1dfbb; }
        .filter-bar { display: flex; gap: 12px; margin-bottom: 16px; }
        .filter-input { padding: 10px 16px; border: 1.5px solid #cde3f7; border-radius: 25px; font-size: 0.9rem; color: #333; background: white; width: 260px; outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
        .filter-input:focus { border-color: #165b9c; box-shadow: 0 0 0 3px rgba(22,91,156,0.1); }
        .filter-input::placeholder { color: #aaa; }
        .table-card { background: white; border-radius: 16px; box-shadow: 0 2px 12px rgba(22,91,156,0.08); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #165b9c; color: white; }
        thead th { padding: 14px 16px; text-align: left; font-size: 0.85rem; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }
        tbody tr { border-bottom: 1px solid #f0f0f0; transition: background 0.2s; }
        tbody tr:hover { background: #f8fbff; }
        tbody td { padding: 13px 16px; font-size: 0.9rem; color: #333; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
        .badge-ativa { background: #e6f7f0; color: #007952; }
        .badge-inativa { background: #f0f0f0; color: #999; }
        .btn-edit { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; background: #165b9c; color: white; border-radius: 20px; font-size: 0.83rem; font-weight: 600; transition: all 0.2s; text-decoration: none; }
        .btn-edit:hover { background: #0e4278; transform: translateY(-1px); color: white; }
        .btn-import { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; background: #007952; color: white; border-radius: 20px; font-size: 0.83rem; font-weight: 600; transition: all 0.2s; text-decoration: none; }
        .btn-import:hover { background: #006b47; transform: translateY(-1px); color: white; }
        .btn-delete { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; background: #c0392b; color: white; border: none; border-radius: 20px; font-size: 0.83rem; font-weight: 600; transition: all 0.2s; cursor: pointer; }
        .btn-delete:hover { background: #a93226; transform: translateY(-1px); }
        .pagination-wrapper { padding: 20px; display: flex; justify-content: center; gap: 6px; align-items: center; }
        .page-btn { padding: 7px 13px; border-radius: 8px; font-size: 0.88rem; border: 1px solid #ddd; color: #165b9c; text-decoration: none; transition: all 0.2s; }
        .page-btn:hover, .page-btn.active { background: #165b9c; color: white; border-color: #165b9c; }
    </style>
</head>
<body>
    <div class="page-container">

        <div class="page-header">
            <div>
                <h1><i class="fas fa-tint"></i> Poços SIMAH</h1>
                <p>Gerenciamento de estações e importação de leituras</p>
            </div>
            <div style="display:flex; gap:12px; align-items:center;">
                <a href="{{ route('poco-simah.stations.create') }}" class="btn-primary">
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
            <input type="text" id="filter-name" class="filter-input" placeholder="Filtrar por nome...">
            <input type="text" id="filter-code" class="filter-input" placeholder="Filtrar por código...">
        </div>

        <div class="table-card">
            <table id="stations-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Profundidade (m)</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $station)
                        <tr>
                            <td><strong>{{ $station->station_code }}</strong></td>
                            <td>{{ $station->name }}</td>
                            <td>{{ $station->latitude ?? '-' }}</td>
                            <td>{{ $station->longitude ?? '-' }}</td>
                            <td>{{ $station->depth ? number_format($station->depth, 2, ',', '.') . ' m' : '-' }}</td>
                            <td>
                                @if ($station->ativa)
                                    <span class="badge badge-ativa">Ativa</span>
                                @else
                                    <span class="badge badge-inativa">Inativa</span>
                                @endif
                            </td>
                            <td style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                <a href="{{ route('poco-simah.stations.import', $station->id) }}" class="btn-import">
                                    <i class="fas fa-upload"></i> Importar
                                </a>
                                <a href="{{ route('poco-simah.stations.edit', $station->id) }}" class="btn-edit">
                                    <i class="fas fa-pen"></i> Editar
                                </a>
                                <form method="POST" action="{{ route('poco-simah.stations.destroy', $station->id) }}" style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete"
                                        onclick="return confirm('Remover a estação {{ addslashes($station->name) }}?')">
                                        <i class="fas fa-trash"></i> Remover
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:40px; color:#999;">
                                <i class="fas fa-tint" style="font-size:2rem; margin-bottom:10px; display:block;"></i>
                                Nenhuma estação cadastrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($last_page > 1)
                <div class="pagination-wrapper">
                    @for ($i = 1; $i <= $last_page; $i++)
                        <a href="?page={{ $i }}&name={{ request('name') }}&station_code={{ request('station_code') }}"
                           class="page-btn {{ $current_page == $i ? 'active' : '' }}">
                            {{ $i }}
                        </a>
                    @endfor
                </div>
            @endif
        </div>
    </div>

    <script>
        function filterTable() {
            const name = document.getElementById('filter-name').value.toLowerCase();
            const code = document.getElementById('filter-code').value.toLowerCase();
            document.querySelectorAll('#stations-table tbody tr').forEach(function(row) {
                const cells = row.querySelectorAll('td');
                if (!cells.length) return;
                const matchCode = cells[0].textContent.trim().toLowerCase().includes(code);
                const matchName = cells[1].textContent.trim().toLowerCase().includes(name);
                row.style.display = (matchCode && matchName) ? '' : 'none';
            });
        }
        document.getElementById('filter-name').addEventListener('input', filterTable);
        document.getElementById('filter-code').addEventListener('input', filterTable);
    </script>
</body>
</html>
