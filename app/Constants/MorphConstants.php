<?php

namespace App\Constants;
use App\Models\Lesson;
use App\Models\Section;

class MorphConstants
{
    const EXAM_MODELS_TABLES = ['lessons' , 'sections'] ; 

    const NAME_CLASS = [
        'lessons' => Lesson::class ,
        'sections' => Section::class ,
    ] ;

    const CLASS_NAME = [
        Lesson::class => 'lessons',
        Section::class => 'sections',
    ] ;
}