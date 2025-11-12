<?php

namespace App\Console\Commands;

use App\Services\Lrgs\LrgsService;
use Illuminate\Console\Command;
use Exception;

class LrgsFetchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lrgs:fetch {--limit=10 : Número de mensagens para exibir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca mensagens DCP do LRGS e exibe o resultado parseado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando busca de mensagens DCP...');
        $this->newLine();

        try {
            $lrgsService = new LrgsService();

            // Busca as mensagens
            $this->info('📡 Executando comando LRGS...');
            $filePath = $lrgsService->fetchMessages();
            $this->info("✅ Arquivo salvo em: {$filePath}");
            $this->newLine();

            // Lê o arquivo
            $this->info('📄 Lendo arquivo...');
            $lines = $lrgsService->readMessagesFromFile($filePath);
            $this->info("✅ Total de linhas encontradas: " . count($lines));
            $this->newLine();

            // Parseia as mensagens
            $this->info('🔍 Parseando mensagens...');
            $messages = $lrgsService->fetchAndParseMessages();
            $totalMessages = count($messages);
            $this->info("✅ Total de mensagens parseadas: {$totalMessages}");
            $this->newLine();

            if ($totalMessages === 0) {
                $this->warn('⚠️  Nenhuma mensagem foi encontrada.');
                return 0;
            }

            // Exibe algumas mensagens
            $limit = (int) $this->option('limit');
            $this->info("📋 Exibindo primeiras {$limit} mensagens:");
            $this->newLine();

            foreach (array_slice($messages, 0, $limit) as $index => $message) {
                $num = $index + 1;
                $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                $this->line("<fg=cyan>Mensagem #{$num}</>");
                $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

                $this->line("<fg=yellow>Header:</>");
                $this->line("  DCP Address: " . ($message['header']['dcp_address'] ?? 'N/A'));
                $this->line("  Timestamp: " . ($message['header']['timestamp'] ?? 'N/A'));
                $this->line("  Raw Header: " . ($message['header']['raw'] ?? 'N/A'));

                $this->newLine();

                $this->line("<fg=yellow>Data:</>");
                $this->line("  Total Values: " . ($message['data']['count'] ?? 0));
                $this->line("  First 10 Values: " . implode(', ', array_slice($message['data']['values'] ?? [], 0, 10)));

                $this->newLine();
            }

            if ($totalMessages > $limit) {
                $this->info("... e mais " . ($totalMessages - $limit) . " mensagens.");
                $this->info("Use --limit=N para ver mais mensagens.");
            }

            $this->newLine();
            $this->info('✨ Concluído com sucesso!');

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Erro ao buscar mensagens:');
            $this->error($e->getMessage());
            $this->newLine();
            $this->error('Stack trace:');
            $this->line($e->getTraceAsString());

            return 1;
        }
    }
}
