<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

class SeedForApp extends Command
{
    //php artisan db:seed:forapp --app=Theqa --class=SeederClass
    protected $signature = 'db:seed:forapp {--app= : The application name} {--class= : The class name of the root seeder}';

    protected $description = 'Seed the database for a specific app';

    public function handle()
    {
        $appName = $this->option('app');

        if (!$appName) {
            $this->error('Please provide an app name using the --app option.');
            return 1;
        }

        Config::set('app.name', $appName);
        $options = [
            '--database' => Config::get('database.default'),
        ];

        if ($this->option('class')) {
            $options['--class'] = $this->option('class');
        }

        Artisan::call('db:seed', $options, $this->getOutput());

        return 0;
    }

}
