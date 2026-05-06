<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sync Log #{{ $log->id }} | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap");
        * { font-family: "Inter", sans-serif; box-sizing: border-box; margin: 0; padding: 0; color: inherit; text-decoration: none; }
        body { background: #f0f7ff; min-height: 100vh; }
        .page-container { max-width: 1300px; margin: 0 auto; padding: 30px 20px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .page-header h1 { font-size: 1.6rem; color: #165b9c; font-weight: 700; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 22px; font-size: .9rem; font-weight: 600; border: 2px solid #165b9c; background: transparent; color: #165b9c; cursor: pointer; transition: all .2s; }
        .btn:hover { background: #165b9c; color: #fff; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(22,91,156,.08); padding: 20px; margin-bottom: 20px; }
        .card h2 { color: #165b9c; font-size: 1.1rem; margin-bottom: 14px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap: 14px; }
        .field { font-size: .85rem; }
        .field label { color: #888; display: block; font-size: .72rem; text-transform: uppercase; font-weight: 600; margin-bottom: 3px; }
        .field span { color: #222; }
        .badge { display: inline-block; padding: 3px 9px; border-radius: 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; }
        .badge-pending  { background: #fff4d6; color: #8a6d00; }
        .badge-running  { background: #d6e9ff; color: #0c5fa6; }
        .badge-completed { background: #d6f4d8; color: #2a7032; }
        .badge-failed   { background: #ffd6d6; color: #a02525; }
        pre { background: #1e293b; color: #d1e3f5; padding: 14px; border-radius: 8px; overflow-x: auto; font-size: .8rem; max-height: 400px; }
        table { width: 100%; border-collapse: collapse; font-size: .82rem; }
        th { background: #165b9c; color: #fff; padding: 10px 8px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #eef3f9; }
        .pagination { padding: 16px; display: flex; justify-content: center; }
    </style>
</head>
<body>
<div class="page-container">
    <div class="page-header">
        <h1><i class="fa-solid fa-microscope"></i> Sync Log #{{ $log->id }}</h1>
        <a href="{{ route('lrgs-sync-logs.index') }}" class="btn"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
    </div>

    @php $skipped = max(0, ($log->total_messages ?? 0) - ($log->total_inserted ?? 0) - ($log->total_corrupted ?? 0)); @endphp

    <div class="card">
        <h2>Resumo</h2>
        <div class="grid">
            <div class="field"><label>Estação</label><span>{{ $log->dcpStation?->station_label ?: $log->dcpStation?->station_name ?? '—' }} <small style="color:#888">({{ $log->dcpStation?->dcp_address }})</small></span></div>
            <div class="field"><label>Status</label><span class="badge badge-{{ $log->status }}">{{ $log->status }}</span></div>
            <div class="field"><label>Tentativas</label><span>{{ $log->attempts }}</span></div>
            <div class="field"><label>Início período</label><span>{{ $log->start_time?->format('d/m/Y H:i:s') }}</span></div>
            <div class="field"><label>Fim período</label><span>{{ $log->end_time?->format('d/m/Y H:i:s') }}</span></div>
            <div class="field"><label>Iniciado em</label><span>{{ $log->started_at?->format('d/m/Y H:i:s') ?? '—' }}</span></div>
            <div class="field"><label>Concluído em</label><span>{{ $log->completed_at?->format('d/m/Y H:i:s') ?? '—' }}</span></div>
            <div class="field"><label>Duração</label><span>{{ ($log->started_at && $log->completed_at) ? $log->started_at->diffInSeconds($log->completed_at).'s' : '—' }}</span></div>
            <div class="field"><label>Mensagens</label><span>{{ $log->total_messages }}</span></div>
            <div class="field"><label>Inseridas</label><span><strong>{{ $log->total_inserted }}</strong></span></div>
            <div class="field"><label>Puladas (dup)</label><span>{{ $skipped }}</span></div>
            <div class="field"><label>Corrompidas</label><span>{{ $log->total_corrupted }}</span></div>
        </div>
    </div>

    @if($log->error_message)
        <div class="card" style="border-left:4px solid #a02525">
            <h2 style="color:#a02525">Erro</h2>
            <pre style="background:#fff5f5;color:#a02525">{{ $log->error_message }}</pre>
        </div>
    @endif

    @if(!empty($log->corrupted_headers))
        <div class="card">
            <h2>Headers corrompidos ({{ count($log->corrupted_headers) }})</h2>
            <pre>{{ json_encode($log->corrupted_headers, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    @endif

    <div class="card">
        <h2>Leituras do período ({{ $readings->total() }})</h2>
        <small style="color:#888">Filtradas por estação + janela [start_time, end_time]</small>
        <table style="margin-top:12px">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Reading datetime</th>
                    <th>Address</th>
                    <th>Recovered</th>
                    <th>Soft del.</th>
                    <th>Criada em</th>
                </tr>
            </thead>
            <tbody>
            @forelse($readings as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->reading_datetime?->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $r->address }}</td>
                    <td>{{ $r->recovered_at ? '✓' : '—' }}</td>
                    <td>{{ $r->deleted_at ? '✓' : '—' }}</td>
                    <td>{{ $r->created_at?->format('d/m/Y H:i:s') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;padding:30px;color:#888">Nenhuma leitura nesse período</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $readings->links() }}</div>
    </div>
</div>
</body>
</html>
