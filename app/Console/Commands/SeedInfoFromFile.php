<?php

namespace App\Console\Commands;

use App\Models\Info;
use Illuminate\Console\Command;
use App\Services\Admin\Info\InfoService;

class SeedInfoFromFile extends Command
{
    public function __construct(protected InfoService $infoService)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'info:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = ' description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Info::truncate();
        $this->infoService->insertOrUpdateData(Info::$info);
    }
}
