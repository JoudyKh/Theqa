<?php

namespace App\Services\App\Home;

use App\Constants\TamkeenInfo;

use App\Constants\EliteInfo;
use App\Constants\TheqaInfo;
use App\Constants\MawahbInfo;
use App\Constants\KhrejeenInfo;

use App\Services\General\Info\InfoService;
use App\Http\Resources\Section\CourseResource;
use App\Http\Resources\Section\SectionResource;
use App\Services\General\Teacher\TeacherService;

class HomeService
{
    public function __construct(
        protected TeacherService $teacherService,
        protected InfoService $infoService
    ) {
    }
    public function getMobileHome()
    {
        $data = $this->infoService->getAll();
        return TheqaInfo::mobileHome($data);
    }
}