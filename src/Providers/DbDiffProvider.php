<?php

namespace Mtc\DbDiff\Providers;

use Illuminate\Support\ServiceProvider;
use Mtc\DbDiff\Console\Commands\ColumnDiffCommand;
use Mtc\DbDiff\Console\Commands\TableDiffCommand;

class DbDiffProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ColumnDiffCommand::class,
                TableDiffCommand::class,
            ]);
        }
    }
}
