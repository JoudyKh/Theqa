<?php

namespace App\Services\General\Home;


use App\Constants\TheqaInfo;
use App\Services\General\Info\InfoService;
use App\Services\General\Teacher\TeacherService;

class HomeService
{
    public function __construct(protected InfoService $infoService, protected TeacherService $teacherService)
    {
    }
    public function getHome()
    {
        $data = $this->infoService->getAll();


        return TheqaInfo::home($data);
    }
}
