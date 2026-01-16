<?php

namespace App\Services\Admin\Student;

use DB;
use App\Models\User;
use App\Models\StudentExam;
use App\Services\Admin\User\UserService;
use App\Http\Requests\Api\Admin\Student\BulkDeleteStudentRequest;


class StudentService extends UserService
{
        public function bulkDelete(BulkDeleteStudentRequest &$request)
        {
                DB::transaction(function()use(&$request){
                        StudentExam::whereIn('student_id' , $request->validated('trash_students'))->delete();
                        User::whereIn('id' , $request->validated('trash_students'))->delete();
                }) ; 
        }
}
