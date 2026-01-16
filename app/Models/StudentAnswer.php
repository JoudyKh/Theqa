<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_exam_id' ,
        'question_id' ,
        'option_id' ,
    ] ;

    public function option():BelongsTo
    {
        return $this->belongsTo(Option::class) ;
    }
}
