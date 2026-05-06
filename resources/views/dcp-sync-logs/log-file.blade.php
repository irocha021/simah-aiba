<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(request('autorefresh'))
        <meta http-equiv="refresh" content="5">
    @endif
    <title>Log DCP — {{ $date }} | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap");
        * { font-family: "Inter", sans-serif; box-sizing: border-box; margin: 0; padding: 0; color: inherit; text-decoration: none; }
        body { background: #f0f7ff; min-height: 100vh; }
        .page-container { max-width: 1500px; margin: 0 auto; padding: 24px 20px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 12px; }
        .page-header h1 { font-size: 1.5rem; color: #165b9c; font-weight: 700; }
        .meta { color: #666; font-size: .82rem; margin-top: 4px; }
        .meta code { background: #e6effa; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: .8rem; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 18px; font-size: .82rem; font-weight: 600; border: 2px solid #165b9c; background: transparent; color: #165b9c; cursor: pointer; transition: all .2s; }
        .btn:hover { background: #165b9c; color: #fff; }
        .btn-primary { background: #165b9c; color: #fff; }
        .btn-danger { border-color: #a02525; color: #a02525; }
        .btn-danger:hover { background: #a02525; color: #fff; }
        .toolbar { background: #fff; border-radius: 10px; padding: 12px 16px; box-shadow: 0 2px 8px rgba(22,91,156,.06); margin-bottom: 14px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .toolbar select, .toolbar input[type=number] { padding: 6px 10px; border: 1px solid #d6e3f0; border-radius: 6px; font-size: .85rem; }
        .toolbar label { font-size: .8rem; color: #555; font-weight: 600; }

        .log-card { background: #0f172a; border-radius: 10px; box-shadow: 0 4px 14px rgba(15,23,42,.2); padding: 16px; max-height: 75vh; overflow: auto; }
        .log-card pre { font-family: 'JetBrains Mono','Fira Code',Menlo,Consolas,monospace; font-size: .78rem; line-height: 1.55; color: #cbd5e1; white-space: pre-wrap; word-break: break-word; }

        .evt-JOB-START { color: #34d399; font-weight: 600; }
        .evt-JOB-END   { color: #34d399; font-weight: 600; }
        .evt-JOB-ALREADY-RUNNING { color: #fbbf24; font-weight: 700; background:#3a2d05; padding:2px 4px; border-radius:3px; }
        .evt-STATION-START { color: #60a5fa; }
        .evt-STATION-DONE  { color: #60a5fa; }
        .evt-READING-INSERTED { color: #86efac; }
        .evt-READING-SKIPPED  { color: #fde68a; }
        .evt-REPROCESS-START, .evt-REPROCESS-DONE { color: #c4b5fd; }
        .evt-ERROR { color: #fca5a5; font-weight: 700; background:#3a0a0a; padding:2px 4px; border-radius:3px; }
        .inv-tag { color: #94a3b8; }

        .empty { padding: 40px; text-align: center; color: #94a3b8; }
        .badge-info { display: inline-block; padding: 2px 9px; background: #d6e9ff; color: #0c5fa6; border-radius: 10px; font-size: .72rem; font-weight: 600; }
    </style>
</head>
<body>
<div class="page-container">
    <div class="page-header">
        <div>
            <h1><i class="fa-solid fa-file-lines"></i> Log de processamento DCP</h1>
            <div class="meta">
                Arquivo: <code>storage/logs/dcp/dcp-{{ $date }}.log</code>
                @if($exists)
                    · {{ number_format($sizeBytes/1024, 1) }} KB
                    · {{ $totalLines }} linhas no total
                    @if($stage) · <span class="badge-info">{{ $matchedLines }} com [{{ $stage }}]</span> @endif
                    @if(!$full && !$stage) · mostrando últimas {{ $lines }} @endif
                    @if(!$full && $stage) · mostrando últimas {{ $lines }} dessas @endif
                @else
                    · <span class="badge-info">arquivo não existe</span>
                @endif
            </div>
        </div>
        <div style="display:flex;gap:8px">
            <a href="{{ route('lrgs-sync-logs.index') }}" class="btn"><i class="fa-solid fa-arrow-left"></i> Sync logs</a>
            <button type="button" onclick="cleanOldLogs()" class="btn btn-danger"><i class="fa-solid fa-broom"></i> Limpar > 7 dias</button>
        </div>
    </div>

    <form method="GET" class="toolbar">
        <label>Dia:</label>
        <select name="date" onchange="this.form.submit()">
            @forelse($availableDates as $d)
                <option value="{{ $d }}" @selected($d === $date)>{{ $d }}</option>
            @empty
                <option value="{{ $date }}">{{ $date }}</option>
            @endforelse
        </select>

        <label>Stage:</label>
        <select name="stage">
            <option value="">Todos</option>
            @foreach($availableStages as $st)
                <option value="{{ $st }}" @selected($stage === $st)>{{ $st }}</option>
            @endforeach
        </select>

        <label>Linhas:</label>
        <input type="number" name="lines" value="{{ $lines }}" min="50" max="5000" step="50" {{ $full ? 'disabled' : '' }}>

        <label><input type="checkbox" name="full" value="1" @checked($full) onchange="this.form.submit()"> Arquivo inteiro</label>
        <label><input type="checkbox" name="autorefresh" value="1" @checked(request('autorefresh')) onchange="this.form.submit()"> Auto-refresh 5s</label>

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-rotate"></i> Atualizar</button>
    </form>

    <div class="log-card">
        @if($exists && trim($content) !== '')
            @php
                $stages = ['JOB-START','JOB-END','JOB-ALREADY-RUNNING','STATION-START','STATION-DONE','READING-INSERTED','READING-SKIPPED','REPROCESS-START','REPROCESS-DONE','ERROR'];
                $colored = '';
                foreach (preg_split('/\r?\n/', $content) as $line) {
                    $line = e($line);
                    $line = preg_replace('/\[inv=([^\]]+)\]/', '<span class="inv-tag">[inv=$1]</span>', $line);
                    foreach ($stages as $stg) {
                        if (str_contains($line, "[{$stg}]")) {
                            $line = str_replace("[{$stg}]", "<span class=\"evt-{$stg}\">[{$stg}]</span>", $line);
                            break;
                        }
                    }
                    $colored .= $line . "\n";
                }
            @endphp
            <pre id="logbox">{!! $colored !!}</pre>
        @else
            <div class="empty">Sem conteúdo para mostrar</div>
        @endif
    </div>
</div>

<script>
async function cleanOldLogs() {
    if (!confirm('Apagar arquivos de log com mais de 7 dias?')) return;
    const r = await fetch('{{ route('lrgs-sync-logs.log-file.clean') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json'
        }
    });
    const j = await r.json();
    alert(j.deleted_count > 0 ? `Apagados ${j.deleted_count} arquivo(s):\n` + j.deleted.join('\n') : 'Nenhum arquivo antigo a apagar');
    location.reload();
}
</script>
</body>
</html>
