<?php

declare(strict_types=1);

namespace Modules\Core\Console;

use Illuminate\Console\Command;

// @codeCoverageIgnoreStart
final class Refresh extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'core:refresh';

    /**
     * The console command description.
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Delete all data and run migrations.');

        $this->call('cache:clear');

        $this->call('migrate:fresh', [
            '--seed' => true,
        ]);

        $this->call('module:seed', [
            '--all' => true,
        ]);

        $this->call('cache:clear');

        $this->info('All done!');

    }

    /**
     * @return array<int, mixed[]>
     */
    protected function getArguments(): array
    {
        return [
        ];
    }

    /**
     * @return array<int, mixed[]>
     */
    protected function getOptions(): array
    {
        return [
        ];
    }
}
// @codeCoverageIgnoreEnd
