<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LessonStudent extends Pivot
{
    protected $fillable = ['student_id' , 'lesson_id'] ;
}
