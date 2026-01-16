<?php

namespace App\Models;

use App\Constants\Constants;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =
        [
            'name',
            'description',
            'video_url',
            'time',
            'cover_image',
            'is_free',
            'section_id',
            'lesson_order',
        ];
    protected $casts = [
        'time' => 'string',
        'is_free' => 'boolean',
    ];
    public function getTimeAttribute($value)
    {
        return $value? \Carbon\Carbon::createFromFormat('H:i:s', $value)->format('H:i:s'):null;
    }
    public function setTimeAttribute($value)
    {
        $this->attributes['time'] = \Carbon\Carbon::createFromFormat('H:i:s', $value)->format('H:i:s');
    }
    public static function loadLessonStudentArray(User|int|string|null $studentId = null, $mergeWithRequest = true)
    {
        if ($studentId instanceof User) {
            $studentId->loadMissing('studentLessonsPivotTable');
            $lessonStudentArray = $studentId->studentLessonsPivotTable->pluck('lesson_id')->toArray();
        } elseif (!$studentId) {
            $lessonStudentArray = LessonStudent::where('student_id', auth('sanctum')->id())->pluck('lesson_id')->toArray();
        } else {
            $lessonStudentArray = LessonStudent::where('student_id', $studentId)->pluck('lesson_id')->toArray();
        }

        if ($mergeWithRequest and !app()->bound('lessonStudentArray')) {
            app()->instance('lessonStudentArray' , $lessonStudentArray);
        }

        return $lessonStudentArray;
    }
    public static function getNextLessonId(Lesson|string|int $lesson, $mergeWithRequest = true)
    {
        if (!($lesson instanceof Lesson)) {
            $lesson = Lesson::where('id', $lesson)->firstOrFail();
        }

        $nextLesson = Lesson::where('section_id', $lesson->section_id)
            ->where('lesson_order', '>', $lesson->lesson_order ?? 0)
            ->orderBy('lesson_order')
            ->first();

        $nextLessonId = null;
        if ($nextLesson and !empty($nextLesson) and $nextLesson->section_id == $lesson->section_id) {
            $nextLessonId = $nextLesson->id;
        } else {
            $nextLessonId = -1;
        }

        if ($mergeWithRequest)
            app()->instance('next_lesson_id' , $nextLessonId);

        return $nextLessonId;
    }
    public function lessonStudents():HasMany
    {
        return $this->hasMany(LessonStudent::class , 'lesson_id') ;
    }
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'model');
    }
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lesson_student', 'lesson_id', 'student_id')->withTimestamps();
    }

    public function exam()
    {
        return $this->morphOne(Exam::class, 'model');
    }

    public static function isPassedLesson(&$lesson , $userId = null)
    {
        $exam = $lesson->exam ;

        if(!$exam or $exam->questions()->count() == 0)return true;

        $studentExam = StudentExam::where([
            'student_id' => $userId ?? auth('sanctum')->id() ,
            'exam_id' => $exam->id ,
        ])
        ->orderByDesc('created_at')
        ->first() ;

        return StudentExam::isPassedTheExam($studentExam) ;
    }
}
