<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Exam extends Model
{
  use HasFactory, SoftDeletes, Searchable;
  protected $fillable = [
    'solution_file',
    'description',
    'minutes',
    'pass_percentage',
    'model_id',
    'model_type',
    'exam_order',
    'image',
    'name',
    'is_free',
    'random_questions_max',
    'degree',
    'student_id',
    'type',
    'expires_at',
  ];

  protected $appends = ['attempts_count'];

  const RANDOM_QUESTIONS_MAX = 1000;
  public function getRandomQuestionsMaxAttribute($value)
  {
    return $value;
  }

  public function getAttemptsCountAttribute()
  {
    if ($this->relationLoaded('studentExams')) {
      return $this->studentExams->count();
    }

    return $this->student_exams_count;
  }

  public static function types()
  {
    return new class {
      public const ORIGINAL = 'ORIGINAL';
      public const GENERATED = 'GENERATED';

      public function all()
      {
        return [
          self::ORIGINAL,
          self::GENERATED,
        ];
      }
    };
  }

  public function scopeJoinLastStudentExams($query, $authStudentId, $currStudentExamCreatedAt = null, bool|null $desc = true, bool $withGeneratedExams = false)
  {
    $joinType = $withGeneratedExams ? 'leftJoinSub' : 'joinSub';
    return $query
          ->when($withGeneratedExams, function ($query) use ($authStudentId) {
            $query->where(function ($q) use ($authStudentId) {
              $q->where('exams.student_id', $authStudentId)
                ->orWhereNull('exams.student_id');
            });
          })
      ->$joinType(
        DB::table('student_exams')
          ->selectRaw("
                      student_exams.* ,
                      row_number() over (partition by exam_id order by student_exams.created_at desc) as row_num ,
                      CASE
                          WHEN student_exams.total_degree > 0
                              AND student_exams.degree IS NOT NULL
                              AND ((student_exams.degree * 100) / student_exams.total_degree) >= student_exams.exam_pass_percentage
                          THEN true
                          ELSE false
                      END as pass
                  ")
          ->where('student_exams.student_id', $authStudentId),
        'latest_student_exams',
        function ($join) {
          $join->on('latest_student_exams.exam_id', '=', 'exams.id')
            ->where('latest_student_exams.row_num', '=', 1); // Ensure only the latest row
        }
      )
        ->when($currStudentExamCreatedAt, function ($query) use ($currStudentExamCreatedAt) {
          $query->where('latest_student_exams.created_at', '<', $currStudentExamCreatedAt);
        })
        ->when($desc !== null, function ($query) use ($desc) {
          $query->orderBy('latest_student_exams.created_at', $desc ? 'desc' : 'asc');
        })
        ->selectRaw("
              exams.id as id,
              exams.name as name,
              exams.description as description,
              exams.image as image,
              exams.minutes as minutes,
              exams.created_at as created_at,
              exams.updated_at as updated_at,
              exams.pass_percentage,
              latest_student_exams.exam_pass_percentage as latest_student_exam_pass_percentage,
              latest_student_exams.degree as latest_student_exam_degree,
              latest_student_exams.id as latest_student_exam_id,
              latest_student_exams.start_date as latest_student_exam_start_date,
              latest_student_exams.end_date as latest_student_exam_end_date,
              latest_student_exams.attempts_count as latest_student_exam_attempts_count,
              latest_student_exams.total_degree as latest_student_exam_total_degree,
              latest_student_exams.on_time as latest_student_exam_on_time,
              latest_student_exams.exam_degree as latest_student_exam_exam_degree,
              latest_student_exams.created_at as latest_student_exam_created_at,
              latest_student_exams.updated_at as latest_student_exam_updated_at,
              latest_student_exams.pass as pass
          ")
        ->distinct()
        ->when(request()->has('status'), function ($query) {
          if (request()->get('status') === 'solved') {
            $query->where('latest_student_exams.pass', true);
          } else {
            $query->where('latest_student_exams.pass', false);
          }
        });
  }

  public function toSearchableArray()
  {
    return [
      'description' => $this->description,
    ];
  }

  protected $append = ['question_count'];

  public function questions(): BelongsToMany
  {
    return $this->belongsToMany(Question::class);
  }
  public function getQuestionsCountAttribute()
  {
    return $this->questions()->count();
  }
  public function model(): MorphTo
  {
    return $this->morphTo();
  }
  public function lesson(): ?MorphTo
  {
    if ($this->model_type !== \App\Models\Lesson::class) {
      return null;
    }
    return $this->morphTo('model');
  }
  public function section(): ?MorphTo
  {
    if ($this->model_type !== \App\Models\Section::class) {
      return null;
    }
    return $this->morphTo('model');
  }
  public function studentExams(): HasMany
  {
    return $this->hasMany(StudentExam::class, 'exam_id');
  }
  public static function getNextExamId(Exam &$exam, $mergeWithRequest = true, $user = null)
  {
    if ($user) {
      $updated_date = $exam->studentExams()->where(['student_id' => $user->id])->first()?->updated_date;
      $nextExam = Exam::joinLastStudentExams($user->id, $updated_date, true)
        ->first();
    } else {
      $nextExam = Exam::where([
        'model_id' => $exam->model_id,
        'model_type' => $exam->model_type,
      ])
        ->where('exam_order', '>', $exam->exam_order ?? 0)
        ->orderBy('exam_order')
        ->first();
    }

    $nextExamId = null;
    if (
      $nextExam and
      !empty($nextExam) and
      ($exam->model_id == $nextExam->model_id) and
      ($exam->model_type == $nextExam->model_type)
    ) {
      $nextExamId = $nextExam->id;
    } else {
      $nextExamId = -1;
    }

    if ($mergeWithRequest)
      app()->instance('next_exam_id', $nextExamId);

    return $nextExamId;
  }
  public static function getExamsState(string|int|null|User|Authenticatable $userOrId = null, $studentExams = null, $mergeWithRequest = true)
  {
    if (!$studentExams) {
      $ID = $userOrId ?? auth('sanctum')->id();
      if (!$ID)
        return null;
      if ($userOrId === null or is_string($userOrId) or is_int($userOrId)) {
        $userOrId = User::with('studentExams')->where('id', $ID)->first();
        if (!$userOrId)
          return null;
      }
      $studentExams = $userOrId->studentExams();
    }
    $examsState =
      $studentExams
        ->get(['exam_id', 'start_date', 'end_date', 'degree'])
        ->groupBy('exam_id')
        ->mapWithKeys(function ($items, $examId) {
          // Map the result with exam_id as the key
          $firstItem = $items->first();
          return [
            $examId => [
              'start_date' => $firstItem?->start_date,
              'end_date' => $firstItem?->end_date,
              'degree' => $firstItem?->degree,
            ]
          ];
        });

    if ($mergeWithRequest) {
      app()->instance('examsState', $examsState->toArray());
    }
    return $examsState->toArray();
  }
}
