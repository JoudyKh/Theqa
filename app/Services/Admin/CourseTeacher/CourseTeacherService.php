<?php

namespace App\Services\Admin\CourseTeacher;
use App\Models\User;
use App\Models\Section;
use App\Models\CourseTeacher;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\Admin\Section\Teacher\OpenSectionTeacherRequest;
use App\Http\Requests\Api\Admin\Section\Teacher\CancelSectionTeacherRequest;

class CourseTeacherService
{
    public function store(OpenSectionTeacherRequest &$request)
    {
        
        $res = DB::transaction(function () use (&$request) {
            return CourseTeacher::create([
                'teacher_id' => $request->validated('teacher_id') ,
                'course_id' => $request->validated('section_id') ,
            ]) ;
        });

        return success($res) ;
    }
    
    public function delete(CancelSectionTeacherRequest &$request)
    {
        $res = DB::transaction(function () use (&$request) {
            return CourseTeacher::where([
                'teacher_id' => $request->validated('teacher_id') ,
                'course_id' => $request->validated('section_id') ,
            ])->delete() ;
        });

        return success($res) ;
    }
}