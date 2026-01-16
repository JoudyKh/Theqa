<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use DateTime;

class ExecuteSQLFiles extends Command
{
    protected $signature = 'sql:execute {--date= : The date in YYYY-MM-DD format}';

    protected $description = 'Execute SQL files created after the given date';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $inputDate = new DateTime($this->option('date'));
        $directory = base_path('database/migrations/sql');

        if (!File::exists($directory)) {
            $this->error("Directory not found: $directory");
            return 1;
        }

        $files = collect(File::files($directory))
            ->filter(function ($file) use ($inputDate) {
                $fileName = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                try {
                    $fileDate = new DateTime($fileName);
                } catch (\Exception $e) {
                    return false;
                }

                return $fileDate > $inputDate || 1;
            })
            ->sortBy(function ($file) {
                return $file->getFilename();
            });

        if ($files->isEmpty()) {
            $this->info('No files to execute.');
            return 0;
        }

        foreach ($files as $file) {
            $this->info("Executing: {$file->getFilename()}");
            $sql = File::get($file->getPathname());

            try {
                DB::unprepared($sql);
                $this->info("Executed successfully: {$file->getFilename()}");
            } catch (\Exception $e) {
                $this->error("Error executing {$file->getFilename()}: {$e->getMessage()}");
            }
        }

        return 0;
    }
}
