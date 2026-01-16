<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseTeacher extends Model
{
    use HasFactory;

    protected $table = 'course_teacher' ;
    protected $fillable = ['teacher_id' , 'course_id'] ;
}
