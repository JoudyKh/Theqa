<?php

namespace App\Jobs;

use App\Models\Exam;
use App\Models\SectionStudent;
use App\Models\User;
use App\Notifications\SectionExamAttachNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SectionExamAttachJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Exam $exam)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        App::setLocale('ar');

        if($this->exam->model_type !== \App\Models\Section::class)return ;

        $section = $this->exam->section;

        $students = User::join('section_student', 'users.id', '=', 'section_student.student_id')
            ->where('section_student.section_id', $section->id)
            ->select('users.*')
            ->get();

        if( ! app()->isProduction()){
            Log::emergency('students ' , $students->pluck('id')->toArray()) ;
        }

        $data = [
            'clickable' => true ,
            'params' =>[
                'exam' => $this->exam->only('id', 'name') ,
                'section' => $section->only('id', 'name' , 'parent_id' , 'type') ,
            ] ,
            'state' => SectionExamAttachNotification::STATE ,
        ];

        $notification = new SectionExamAttachNotification();
        $notification->setAttribute('data' , $data);

        $notification->notify($students);
    }
}
