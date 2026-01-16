<?php

namespace App\Observers;

use App\Jobs\SectionExamAttachJob;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;

class ExamObserver
{
    private function sendSectionExamNotification(Exam $exam)
    {
        SectionExamAttachJob::dispatch($exam);
    }
    /**
     * Handle the Exam "created" event.
     */
    public function created(Exam $exam): void
    {
        if ($exam->model_type == \App\Models\Section::class) {
            $this->sendSectionExamNotification($exam);
        }
    }

    /**
     * Handle the Exam "updated" event.
     */
    public function updated(Exam $exam): void
    {
        if ($exam->model_type === \App\Models\Section::class && $exam->getOriginal('model_id') != $exam->model_id) {
            $this->sendSectionExamNotification($exam);
        }
    }

    /**
     * Handle the Exam "deleted" event.
     */
    public function deleted(Exam $exam): void
    {
        DB::table('exam_question')
            ->where('exam_id', $exam->id)
            ->update(['deleted_at' => now()->toDateTimeString()]);

        // DB::table('student_exams')
        //     ->where('exam_id', $exam->id)
        //     ->update(['deleted_at' => now()->toDateTimeString()]);
    }
    /**
     * Handle the Exam "force deleted" event.
     */
    public function forceDeleted(Exam $exam): void
    {
        DB::table('exam_question')
            ->where('exam_id', $exam->id)
            ->delete();

        DB::table('student_exams')
            ->where('exam_id', $exam->id)
            ->delete();
    }

    public function restore(Exam $exam)
    {
        DB::table('exam_question')
            ->where('exam_id', $exam->id)
            ->update(['deleted_at' => null]);

        // DB::table('student_exams')
        //     ->where('exam_id', $exam->id)
        //     ->update(['deleted_at' => null]);
    }
}
