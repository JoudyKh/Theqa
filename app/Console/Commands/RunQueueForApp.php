<?php

namespace App\Console\Commands;

use App\Models\Info;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class RunQueueForApp extends Command
{

    // php artisan migrate:forapp --app=Theqa --seed --refresh
    protected $signature = 'run:queue
                            {--app= : The application name} ';

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

        try {
            Artisan::call('queue:work --sleep=3 --tries=3 --timeout=90', [], $this->getOutput());

        } catch (\Exception $e) {
            $this->error('migrate_error' . $e->getMessage());
        }



        return 0;
    }



}
