<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Logs de Sincronização DCP | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap");
        * { font-family: "Inter", sans-serif; box-sizing: border-box; margin: 0; padding: 0; color: inherit; text-decoration: none; }
        body { background: #f0f7ff; min-height: 100vh; }
        .page-container { max-width: 1500px; margin: 0 auto; padding: 30px 20px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
        .page-header h1 { font-size: 1.6rem; color: #165b9c; font-weight: 700; }
        .actions { display: flex; gap: 10px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 22px; font-size: .9rem; font-weight: 600; border: 2px solid #165b9c; background: transparent; color: #165b9c; cursor: pointer; transition: all .2s; }
        .btn:hover { background: #165b9c; color: #fff; }
        .btn-primary { background: #165b9c; color: #fff; }
        .btn-primary:hover { background: #0f4577; }

        .filters { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 2px 8px rgba(22,91,156,.06); margin-bottom: 20px; display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
        .filters label { font-size: .8rem; color: #666; display: block; margin-bottom: 4px; font-weight: 600; }
        .filters select, .filters input { width: 100%; padding: 8px 10px; border: 1px solid #d6e3f0; border-radius: 6px; font-size: .9rem; }
        .filters .submit-row { grid-column: 1 / -1; display: flex; gap: 8px; justify-content: flex-end; }

        .table-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(22,91,156,.08); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; font-size: .85rem; }
        th { background: #165b9c; color: #fff; text-align: left; padding: 12px 10px; font-weight: 600; }
        td { padding: 10px; border-bottom: 1px solid #eef3f9; }
        tr:hover { background: #f8fbff; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }

        .badge { display: inline-block; padding: 3px 9px; border-radius: 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; }
        .badge-pending  { background: #fff4d6; color: #8a6d00; }
        .badge-running  { background: #d6e9ff; color: #0c5fa6; }
        .badge-completed { background: #d6f4d8; color: #2a7032; }
        .badge-failed   { background: #ffd6d6; color: #a02525; }

        .err { color: #a02525; font-size: .78rem; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .empty { padding: 60px 20px; text-align: center; color: #888; }
        .pagination { padding: 16px; display: flex; justify-content: center; }
        .pagination a, .pagination span { padding: 6px 12px; border-radius: 6px; margin: 0 2px; color: #165b9c; }
        .pagination .active span { background: #165b9c; color: #fff; }
    </style>
</head>
<body>
<div class="page-container">
    <div class="page-header">
        <div>
            <h1><i class="fa-solid fa-clock-rotate-left"></i> Logs de Sincronização DCP</h1>
            <p style="color:#666;font-size:.9rem;margin-top:4px;">Histórico de execuções do job de leitura LRGS</p>
        </div>
        <div class="actions">
            <a href="{{ route('lrgs-sync-logs.log-file') }}" class="btn"><i class="fa-solid fa-file-lines"></i> Ver arquivo de log</a>
            <a href="/dashboard" class="btn"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
        </div>
    </div>

    <form method="GET" class="filters">
        <div>
            <label>Estação</label>
            <select name="station_id">
                <option value="">Todas</option>
                @foreach($stations as $s)
                    <option value="{{ $s->id }}" @selected(request('station_id') == $s->id)>{{ $s->station_label ?: $s->station_name }} ({{ $s->dcp_address }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Status</label>
            <select name="status">
                <option value="">Todos</option>
                @foreach(['pending','running','completed','failed'] as $st)
                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>De</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}">
        </div>
        <div>
            <label>Até</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}">
        </div>
        <div class="submit-row">
            <a href="{{ route('lrgs-sync-logs.index') }}" class="btn">Limpar</a>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filtrar</button>
        </div>
    </form>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Estação</th>
                    <th>Período</th>
                    <th>Status</th>
                    <th class="num">Tent.</th>
                    <th class="num">Mensagens</th>
                    <th class="num">Inseridas</th>
                    <th class="num">Pulados (dup)</th>
                    <th class="num">Corrompidas</th>
                    <th>Início</th>
                    <th>Duração</th>
                    <th>Erro</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                @php
                    $skipped = max(0, ($log->total_messages ?? 0) - ($log->total_inserted ?? 0) - ($log->total_corrupted ?? 0));
                    $duration = ($log->started_at && $log->completed_at)
                        ? $log->started_at->diffInSeconds($log->completed_at) . 's'
                        : '—';
                @endphp
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>
                        @if($log->dcpStation)
                            {{ $log->dcpStation->station_label ?: $log->dcpStation->station_name }}<br>
                            <small style="color:#888">{{ $log->dcpStation->dcp_address }}</small>
                        @else
                            <em>removida</em>
                        @endif
                    </td>
                    <td style="font-size:.78rem">
                        {{ $log->start_time?->format('d/m H:i') }}<br>
                        → {{ $log->end_time?->format('d/m H:i') }}
                    </td>
                    <td><span class="badge badge-{{ $log->status }}">{{ $log->status }}</span></td>
                    <td class="num">{{ $log->attempts }}</td>
                    <td class="num">{{ $log->total_messages }}</td>
                    <td class="num"><strong>{{ $log->total_inserted }}</strong></td>
                    <td class="num">{{ $skipped }}</td>
                    <td class="num">{{ $log->total_corrupted }}</td>
                    <td style="font-size:.78rem">{{ $log->started_at?->format('d/m H:i:s') ?? '—' }}</td>
                    <td>{{ $duration }}</td>
                    <td><div class="err" title="{{ $log->error_message }}">{{ $log->error_message }}</div></td>
                    <td><a href="{{ route('lrgs-sync-logs.show', $log->id) }}" class="btn" style="padding:5px 10px;font-size:.75rem"><i class="fa-solid fa-eye"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="13" class="empty">Nenhum log encontrado</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $logs->links() }}</div>
    </div>
</div>
</body>
</html>
