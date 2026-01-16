<?php

namespace Database\Seeders;

use App\Models\Info;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use App\Services\Admin\Info\InfoService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SiteInfoSeeder extends Seeder
{
    public function __construct(protected InfoService $infoService)
    {
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cache::flush();
        Info::truncate();
        $this->infoService->insertOrUpdateData(Info::$info);

    }
}
