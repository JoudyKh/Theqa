<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
  use HasFactory, SoftDeletes, Searchable;

  protected $with = ['options'];

  protected $fillable = [
    'text',
    'degree',
    'note',
    'video',
    'image',
    'note_image',
    'page_number',
  ];

  public function toSearchableArray()
  {
    return [
      'text' => $this->text,
      'note' => $this->note,
    ];
  }

  public function exams(): BelongsToMany
  {
    return $this->belongsToMany(Exam::class);
  }
  public function options(): HasMany
  {
    return $this->hasMany(Option::class);
  }
  public static function chosenQuestions($studentId = null, $examId = null, array|null $questionsIds = [], $mergeWithRequest = false)
  {
    $chosen_questions = StudentAnswer::query()
      ->when($questionsIds, function ($query) use ($questionsIds) {
        $query->whereIn('student_answers.question_id', array_unique($questionsIds));
      })
      ->when($studentId or $examId, function ($query) use ($studentId, $examId) {
        $query->join('student_exams', 'student_answers.student_exam_id', '=', 'student_exams.id')
          ->when($studentId, function ($subQuery1) use ($studentId) {
            $subQuery1->where('student_exams.student_id', $studentId);
          })
          ->when($examId, function ($subQuery2) use ($examId) {
            $subQuery2->where('student_exams.exam_id', $examId);
          });
      })
      ->pluck('student_answers.question_id')
      ->toArray();

    if ($mergeWithRequest and !app()->bound('chosen_questions')) {
      app()->instance('chosen_questions' , array_unique($chosen_questions));
    }
    return $chosen_questions;
  }
}