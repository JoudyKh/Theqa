<?php

use App\Models\StudentExam;
use App\Models\User;
use App\Models\Section;
use App\Constants\Notifications;
use App\Models\CertificateRequest;
use App\Events\CourseSubscribedEvent;
use Illuminate\Support\Facades\Route;
use App\Events\CertificateAcceptedEvent;
use App\Enums\CertificateRequestStatusEnum;
use App\Jobs\SectionExamAttachJob;
use App\Models\Exam;
use App\Notifications\CourseSubsribedNotification;
use App\Services\App\StudentExam\StudentExamService;
use App\Notifications\CertificateAcceptedNotification;
use App\Services\General\Notification\NotificationService;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return redirect('api/documentation');
});

Route::get('/test', function () {

    return $exam = Exam::find(1);
});

Route::get('fix', function () {

    return DB::transaction(function () {
        DB::table('student_exams')
        ->join('exams', 'student_exams.exam_id', '=', 'exams.id')
        ->whereNull('student_exams.exam_pass_percentage')
        ->whereNotNull('exam_id')
        ->update([
            'student_exams.exam_pass_percentage' => DB::raw('exams.pass_percentage'),
        ]);
    }) ;

});
