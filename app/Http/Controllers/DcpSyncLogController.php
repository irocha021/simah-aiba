<?php

namespace App\Http\Controllers;

use App\Models\DcpReading;
use App\Models\DcpStation;
use App\Models\DcpSyncLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DcpSyncLogController extends Controller
{
    private const LOG_DIR = 'logs/dcp';
    private const LOG_RETENTION_DAYS = 7;
    private const DEFAULT_TAIL_LINES = 200;
    private const STAGES = [
        'JOB-START', 'JOB-END', 'JOB-ALREADY-RUNNING',
        'STATION-START', 'STATION-DONE',
        'READING-INSERTED', 'READING-SKIPPED',
        'REPROCESS-START', 'REPROCESS-DONE',
        'ERROR',
    ];

    public function index(Request $request)
    {
        $query = DcpSyncLog::with('dcpStation')->orderBy('created_at', 'desc');

        if ($stationId = $request->input('station_id')) {
            $query->where('dcp_station_id', $stationId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($from = $request->input('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to = $request->input('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($to)->endOfDay());
        }

        $logs = $query->paginate(30)->withQueryString();
        $stations = DcpStation::orderBy('station_name')->get(['id', 'station_name', 'station_label', 'dcp_address']);

        return view('dcp-sync-logs.index', compact('logs', 'stations'));
    }

    public function show(int $id)
    {
        $log = DcpSyncLog::with('dcpStation')->findOrFail($id);

        $readings = DcpReading::where('dcp_station_id', $log->dcp_station_id)
            ->where('reading_datetime', '>=', $log->start_time)
            ->where('reading_datetime', '<=', $log->end_time)
            ->orderBy('reading_datetime', 'desc')
            ->paginate(50);

        return view('dcp-sync-logs.show', compact('log', 'readings'));
    }

    public function logFile(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $full = (bool) $request->input('full', false);
        $lines = (int) $request->input('lines', self::DEFAULT_TAIL_LINES);
        $stage = $request->input('stage');
        if (!in_array($stage, self::STAGES, true)) {
            $stage = null;
        }

        $logDir = storage_path(self::LOG_DIR);
        $availableFiles = collect(File::exists($logDir) ? File::files($logDir) : [])
            ->map(fn($f) => $f->getFilename())
            ->filter(fn($n) => preg_match('/^dcp-(\d{4}-\d{2}-\d{2})\.log$/', $n))
            ->sort()
            ->values()
            ->all();

        $availableDates = array_values(array_map(
            fn($n) => preg_replace('/^dcp-(\d{4}-\d{2}-\d{2})\.log$/', '$1', $n),
            $availableFiles
        ));

        $availableStages = self::STAGES;
        $filePath = storage_path(self::LOG_DIR . "/dcp-{$date}.log");
        $exists = File::exists($filePath);
        $sizeBytes = $exists ? File::size($filePath) : 0;

        $content = '';
        $totalLines = 0;
        $matchedLines = null;
        if ($exists) {
            if ($stage) {
                // Filtra por stage: lê arquivo inteiro, mantém só linhas com [STAGE]
                $raw = File::get($filePath);
                $totalLines = substr_count($raw, "\n");
                $needle = "[{$stage}]";
                $kept = array_values(array_filter(
                    preg_split('/\r?\n/', $raw),
                    fn($l) => str_contains($l, $needle)
                ));
                $matchedLines = count($kept);
                if (!$full) {
                    $kept = array_slice($kept, -$lines);
                }
                $content = implode("\n", $kept);
            } elseif ($full) {
                $content = File::get($filePath);
                $totalLines = substr_count($content, "\n");
            } else {
                [$content, $totalLines] = $this->tailFile($filePath, $lines);
            }
        }

        return view('dcp-sync-logs.log-file', compact(
            'date', 'full', 'lines', 'stage', 'content', 'exists',
            'sizeBytes', 'totalLines', 'matchedLines', 'availableDates', 'availableStages'
        ));
    }

    public function cleanOldLogs()
    {
        $logDir = storage_path(self::LOG_DIR);
        if (!File::exists($logDir)) {
            return response()->json(['success' => true, 'deleted' => []]);
        }

        $cutoff = now()->subDays(self::LOG_RETENTION_DAYS)->timestamp;
        $deleted = [];

        foreach (File::files($logDir) as $file) {
            $name = $file->getFilename();
            if (!preg_match('/^dcp-\d{4}-\d{2}-\d{2}\.log$/', $name)) {
                continue;
            }
            if ($file->getMTime() < $cutoff) {
                File::delete($file->getPathname());
                $deleted[] = $name;
            }
        }

        return response()->json([
            'success' => true,
            'retention_days' => self::LOG_RETENTION_DAYS,
            'deleted_count' => count($deleted),
            'deleted' => $deleted,
        ]);
    }

    private function tailFile(string $path, int $lines): array
    {
        $f = new \SplFileObject($path, 'r');
        $f->seek(PHP_INT_MAX);
        $totalLines = $f->key();

        $start = max(0, $totalLines - $lines);
        $f->seek($start);

        $buffer = '';
        while (!$f->eof()) {
            $buffer .= $f->fgets();
        }
        $f = null;

        return [$buffer, $totalLines];
    }
}
