<?php

namespace Mtc\DbDiff\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ColumnDiffCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-diff:list-columns';

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
            $remote_tables = $this->getTables($connection);
            $local_tables = $this->getTables($this->defaultSchema());

            $this->table(['Table & Column', 'Column in Local', 'Column in Remote'], $this->diff($local_tables, $remote_tables));

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
            ->keyBy(function ($table) {
                $as_table = (array)$table;
                return array_shift($as_table);
            })
            ->map(function ($table, $table_name) use ($connection) {
                return $this->getColumns($connection, $table_name);
            });
    }

    private function getColumns($connection, $table): array
    {
        return DB::connection($connection)->getSchemaBuilder()->getColumnListing($table);
    }

    private function diff(Collection $local_tables, Collection $remote_tables): array
    {
        $columns = collect([]);

        $local_tables->each(function ($table, $table_name) use ($columns, $remote_tables) {
            collect($table)
                ->each(function ($column) use ($columns, $table_name, $remote_tables) {
                    $key = "{$table_name}.{$column}";
                    if ($columns->has($key) == false) {
                        $columns->put($key, [
                            'key' => $key,
                            'local' => true,
                            'remote' => in_array($column, $remote_tables[$table_name] ?? [])
                        ]);
                    }
                });
        });

        $remote_tables->each(function ($table, $table_name) use ($columns) {
            collect($table)
                ->each(function ($column) use ($columns, $table_name) {
                    $key = "{$table_name}.{$column}";
                    if ($columns->has($key) == false) {
                        $columns->put($key, [
                            'key' => $key,
                            'local' => false,
                            'remote' => true
                        ]);
                    }
                });
        });

        return $columns
            ->filter(function ($row) {
                return $row['local'] === false || $row['remote'] === false;
            })
            ->sortBy('key')
            ->toArray();
    }

}
