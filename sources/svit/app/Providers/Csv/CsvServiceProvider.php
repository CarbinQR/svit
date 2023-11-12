<?php

namespace App\Providers\Csv;

use App\Providers\ExampleService;
use App\Services\Csv\DownloadCsvService;
use App\Services\Csv\MergeCsvService;
use App\Services\Csv\SqlToCsvConverter;
use App\Services\Csv\SqlToCsvConverterService;
use Illuminate\Support\ServiceProvider;

class CsvServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(SqlToCsvConverterService::class, function () {
            return new SqlToCsvConverterService();
        });
        $this->app->bind(MergeCsvService::class, function () {
            return new MergeCsvService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
