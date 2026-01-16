<?php

namespace App\Observers;

use App\Models\Option;
use App\Models\Question;

class QuestionObserver
{
    public $afterCommit = true;
    /**
     * Handle the Question "deleted" event.
     */
    public function deleted(Question $question): void
    {
        $question->options()->delete() ;
    }

    /**
     * Handle the Question "restored" event.
     */
    public function restored(Question $question): void
    {
        Option::withTrashed()->where('question_id' , $question->id)->restore() ;
    }

    /**
     * Handle the Question "force deleted" event.
     */
    public function forceDeleted(Question $question): void
    {
        Option::withTrashed()->where('question_id' , $question->id)->forceDelete() ;
    }
}
