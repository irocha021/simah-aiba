<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Equipamentos LRGS | SIMAH</title>
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

        body {
            background: #f0f7ff;
            min-height: 100vh;
        }

        .page-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 1.8rem;
            color: #165b9c;
            font-weight: 700;
        }

        .page-header p {
            color: #666;
            font-size: 0.95rem;
            margin-top: 4px;
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
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .back-btn:hover {
            background: #165b9c;
            color: white;
        }

        .alert {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #b1dfbb;
        }

        .table-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(22, 91, 156, 0.08);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #165b9c;
            color: white;
        }

        thead th {
            padding: 14px 16px;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }

        tbody tr:hover {
            background: #f8fbff;
        }

        tbody td {
            padding: 13px 16px;
            font-size: 0.9rem;
            color: #333;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .badge-curve1 {
            background: #e3f0fb;
            color: #165b9c;
        }

        .badge-curve2 {
            background: #e6f7f0;
            color: #007952;
        }

        .badge-none {
            background: #f0f0f0;
            color: #999;
        }

        .btn-edit {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            background: #165b9c;
            color: white;
            border-radius: 20px;
            font-size: 0.83rem;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-edit:hover {
            background: #0e4278;
            transform: translateY(-1px);
            color: white;
        }

        .btn-delete {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            background: #c0392b;
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 0.83rem;
            font-weight: 600;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-delete:hover {
            background: #a93226;
            transform: translateY(-1px);
        }

        .pagination-wrapper {
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .pagination-wrapper nav {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .pagination-wrapper a,
        .pagination-wrapper span {
            padding: 7px 13px;
            border-radius: 8px;
            font-size: 0.88rem;
            border: 1px solid #ddd;
            color: #165b9c;
            text-decoration: none;
            transition: all 0.2s;
        }

        .pagination-wrapper a:hover {
            background: #165b9c;
            color: white;
            border-color: #165b9c;
        }

        .pagination-wrapper span[aria-current] {
            background: #165b9c;
            color: white;
            border-color: #165b9c;
        }

        .filter-input {
            padding: 10px 16px;
            border: 1.5px solid #cde3f7;
            border-radius: 25px;
            font-size: 0.9rem;
            color: #333;
            background: white;
            width: 260px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .filter-input:focus {
            border-color: #165b9c;
            box-shadow: 0 0 0 3px rgba(22, 91, 156, 0.1);
        }

        .filter-input::placeholder {
            color: #aaa;
        }

        .text-muted {
            color: #aaa;
            font-style: italic;
        }

        .logo-header {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-header img {
            height: 40px;
        }
    </style>
</head>

<body>
    <div class="page-container">

        <div class="page-header">
            <div class="logo-header">
                <div>
                    <h1><i class="fas fa-satellite-dish"></i> Equipamentos LRGS</h1>
                    <p>Gerenciamento de estações DCP e curvas chave</p>
                </div>
            </div>
            <div style="display:flex; gap:12px; align-items:center;">
                <a href="{{ route('lrgs-stations.create') }}" class="back-btn" style="background:#165b9c; color:white; border-color:#165b9c;">
                    <i class="fas fa-plus"></i>
                    <span>Nova Estação</span>
                </a>
                <a href="{{ url('/') }}" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="filter-bar" style="display:flex; gap:12px; margin-bottom:16px;">
            <input type="text" id="filter-dcp-address" class="filter-input" placeholder="Filtrar por DCP Address...">
            <input type="text" id="filter-nome" class="filter-input" placeholder="Filtrar por Nome...">
        </div>

        <div class="table-card">
            <table id="stations-table">
                <thead>
                    <tr>
                        <th>DCP Address</th>
                        <th>NOME</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Curva Chave</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stations as $station)
                        <tr>
                            <td><strong>{{ $station->dcp_address }}</strong></td>
                            <td>{{ $station->station_label ?? '-' }}</td>
                            <td>{{ $station->latitude ?? '-' }}</td>
                            <td>{{ $station->longitude ?? '-' }}</td>
                            <td>
                                @if ($station->curva_chave == 1)
                                    <span class="badge badge-curve1">1ª Curva Chave</span>
                                @elseif ($station->curva_chave == 2)
                                    <span class="badge badge-curve2">2ª Curva Chave</span>
                                @else
                                    <span class="badge badge-none">Não definida</span>
                                @endif
                            </td>
                            <td style="display:flex; gap:8px; align-items:center;">
                                <a href="{{ route('lrgs-stations.edit', $station->id) }}" class="btn-edit">
                                    <i class="fas fa-pen"></i> Editar
                                </a>
                                <form method="POST" action="{{ route('lrgs-stations.destroy', $station->id) }}" style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete"
                                        onclick="return confirm('Tem certeza que deseja remover a estação {{ addslashes($station->station_label ?? $station->dcp_address) }}?')">
                                        <i class="fas fa-trash"></i> Remover
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 40px; color: #999;">
                                <i class="fas fa-satellite-dish" style="font-size:2rem; margin-bottom:10px; display:block;"></i>
                                Nenhuma estação cadastrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($stations->hasPages())
                <div class="pagination-wrapper">
                    {{ $stations->links() }}
                </div>
            @endif
        </div>

    </div>

    <script>
        function filterTable() {
            const addr = document.getElementById('filter-dcp-address').value.toLowerCase();
            const nome = document.getElementById('filter-nome').value.toLowerCase();
            document.querySelectorAll('#stations-table tbody tr').forEach(function(row) {
                const cells = row.querySelectorAll('td');
                if (!cells.length) return;
                const matchAddr = cells[0].textContent.trim().toLowerCase().includes(addr);
                const matchNome = cells[1].textContent.trim().toLowerCase().includes(nome);
                row.style.display = (matchAddr && matchNome) ? '' : 'none';
            });
        }
        document.getElementById('filter-dcp-address').addEventListener('input', filterTable);
        document.getElementById('filter-nome').addEventListener('input', filterTable);
    </script>

    <!-- Botão flutuante para mobile -->
    <a href="{{ url('/') }}" class="floating-back-btn" style="position:fixed;bottom:30px;right:30px;z-index:1000;display:flex;flex-direction:column;align-items:center;gap:5px;text-decoration:none;">
        <div style="width:60px;height:60px;border-radius:50%;background:#165b9c;color:white;display:flex;align-items:center;justify-content:center;font-size:1.5rem;box-shadow:0 4px 15px rgba(22,91,156,0.3);">
            <i class="fas fa-home"></i>
        </div>
    </a>
</body>

</html>
