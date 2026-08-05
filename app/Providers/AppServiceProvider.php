<?php

namespace App\Providers;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use PDO;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        DB::extend('access', function ($config, $name) {

            $pdo = new PDO(
                'odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=' . env('ACCESS_DB_PATH') . ';',
                '',
                ''
            );

            return new Connection($pdo, $config['database'] ?? '', '', $config);
        });
    }
}
