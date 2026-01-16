<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentExam extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'exam_id',
        'start_date',
        'end_date',
        'attempts_count',
        'degree', // count of the correct questions of student
        'total_degree',// the total count of the current exam questions .
        'on_time',
        'exam_degree', // the total mark of the exam
        'created_at',
        'updated_at',
        'exam_pass_percentage', // the stored pass percentage from the exam , so the admin changes does not affect the students history
    ];

    public $incrementing = true;

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student_answers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class);
    }
    public function questions()
    {
        return $this->hasManyThrough(
            Question::class,
            StudentAnswer::class,
            'student_exam_id', // Foreign key on the `student_answers` table
            'id',              // Foreign key on the `questions` table
            'id',              // Local key on the `student_exams` table
            'question_id'      // Local key on the `student_answers` table
        );
    }

    public static function isPassedTheExam($studentExam = null)
    {
        if (!$studentExam)
            return false;

        if (
            $studentExam
            and $studentExam?->degree
            and $studentExam?->total_degree
            and $studentExam?->start_date
            and $studentExam?->end_date
            and (( ($studentExam?->degree * 100) / $studentExam?->total_degree) >= $studentExam->exam_pass_percentage)
            //and Carbon::parse($studentExam->start_date)->diffInMinutes($studentExam->end_date) >= $exam->minutes
        ) {
            return true;
        }
        return false;
    }
}
