<?php

namespace Zenoware\Laravel\SqlDumpValidator\Providers;

use Illuminate\Support\ServiceProvider as ServiceProviderBase;
use Zenoware\Laravel\SqlDumpValidator\Adapters\IFileAdapter;
use Zenoware\Laravel\SqlDumpValidator\Adapters\LocalFileAdapter;
use Zenoware\Laravel\SqlDumpValidator\Services\SqlDumpValidatorService;
use Zenoware\Laravel\SqlDumpValidator\Console\ValidateSqlDumpCommand;

class SqlDumpValidatorServiceProvider extends ServiceProviderBase
{
    public function register()
    {
        $this->app->singleton(IFileAdapter::class, LocalFileAdapter::class);
        $this->app->singleton(SqlDumpValidatorService::class, function ($app) {
            return new SqlDumpValidatorService($app->make(IFileAdapter::class));
        });
    }

    public function boot()
    {
        $this->commands([
            ValidateSqlDumpCommand::class,
        ]);
    }
}
