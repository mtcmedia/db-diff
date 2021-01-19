<?php

namespace Mtc\DbDiff\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TableDiffCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-diff:list-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show missing tables for remote database schema';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $connection = $this->choice('Select connection to compare current schema with', $this->connections());
        $this->listTables($connection);

        return 0;
    }

    private function connections(): array
    {
        return array_keys(config('database.connections', []));
    }

    private function listTables($connection): void
    {
        try {
            $diff = $this->getTables($connection)
                ->diff($this->getTables($this->defaultSchema()))
                ->map(function ($table_name) {
                    return [$table_name];
                })
                ->toArray();

            $this->table(['Tables missing in main database'], $diff);
        } catch (QueryException $exception) {
            $this->error('Unable to Run Comparison. Please make sure you selected correct remote');
        }
    }

    private function defaultSchema(): string
    {
        return config('database.default');
    }

    private function getTables($connection): Collection
    {
        return collect(DB::connection($connection)->select('SHOW TABLES'))
            ->map(function ($table) {
                $as_table = (array)$table;
                return array_shift($as_table);
            });
    }
}
