<?php

namespace App\Console\Commands;

use App\Models\Info;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class MigrateForApp extends Command
{
    // php artisan migrate:forapp --app=Theqa --seed --refresh
    protected $signature = 'migrate:forapp
                            {--app= : The application name}
                            {--database= : The database connection to use}
                            {--seed : Seed after migration}
                            {--refresh : Drop all tables before migrating}
                            {--sql= : Execute SQL files created after the given date}
                            
                            ';

    protected $description = 'Run migrations for a specific app';

    public function handle()
    {
        $appName = $this->option('app');

        if (!$appName) {
            $this->error('Please provide an app name using the --app option.');
            return 1;
        }

        Config::set('app.name', $appName);
        Info::initialize();



        if ($this->option('refresh')) {
            $this->info('Dropping all tables for app: ' . $appName);
            $this->dropAllTables();
            $this->info('All tables dropped successfully.');
        }

        $options = [
            '--database' => Config::get('database.default'),
        ];
        try {
            Artisan::call('migrate', $options, $this->getOutput());

        } catch (\Exception $e) {
            $this->error('migrate_error' . $e->getMessage());
        }

        if ($this->option('seed')) {
            Artisan::call('db:seed', ['--database' => Config::get('database.default')], $this->getOutput());
        }
        if ($this->option('sql')) {
            Artisan::call('sql:execute', ['--date' => $this->option('sql')], $this->getOutput());
        }

        return 0;
    }

   

    protected function dropAllTables()
    {
        Schema::dropAllTables();
    }
}
