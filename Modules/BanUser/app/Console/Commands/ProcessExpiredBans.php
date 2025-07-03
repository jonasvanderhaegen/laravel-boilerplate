<?php

declare(strict_types=1);

namespace Modules\BanUser\Console\Commands;

use Illuminate\Console\Command;
use Modules\BanUser\Services\BanCheckService;

/**
 * Command to process expired bans.
 */
final class ProcessExpiredBans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bans:process-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and lift expired bans';

    /**
     * Execute the console command.
     */
    public function handle(BanCheckService $banCheckService): int
    {
        $this->info('Processing expired bans...');

        $count = $banCheckService->processExpiredBans();

        if ($count > 0) {
            $this->info("Successfully lifted {$count} expired ban(s).");
        } else {
            $this->info('No expired bans found.');
        }

        return self::SUCCESS;
    }
}
