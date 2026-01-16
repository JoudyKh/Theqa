<?php

use App\Http\Controllers\Api\General\Exam\QuestionsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\General\Auth\AuthController;
use App\Http\Controllers\Api\General\Exam\ExamController;
use App\Http\Controllers\Api\General\Info\InfoController;
use App\Http\Controllers\Api\General\Offer\OfferController;
use App\Http\Controllers\Api\General\Course\CourseController;
use App\Http\Controllers\Api\General\Section\SectionController;
use App\Http\Controllers\Api\General\Storage\StorageController;
use App\Http\Controllers\Api\General\Student\StudentController;
use App\Http\Controllers\Api\General\Teacher\TeacherController;
use App\Http\Controllers\Api\App\StudentExam\StudentExamController;
use App\Http\Controllers\Api\General\TopStudent\TopStudentController;
use App\Http\Controllers\Api\App\Auth\AuthController as AppAuthController;
use App\Http\Controllers\Api\App\Home\HomeController as AppHomeController;
use App\Http\Controllers\Api\App\LessonStudent\LessonStudentController;
use App\Http\Controllers\Api\General\Governorate\GovernorateController;
use App\Http\Controllers\Api\App\ContactMessage\ContactMessageController;
use App\Http\Controllers\Api\App\SectionStudent\SectionStudentController;
use App\Http\Controllers\Api\General\Notification\NotificationController;
use App\Http\Controllers\Api\General\CourseTeacher\CourseTeacherController;
use App\Http\Controllers\Api\General\Book\BookController as GeneralBookController;
use App\Http\Controllers\Api\General\Home\HomeController as GeneralHomeController;
use App\Http\Controllers\Api\App\SubscriptionRequest\SubscriptionRequestController;
use App\Http\Controllers\Api\General\Lesson\LessonController as GeneralLessonController;
use App\Http\Controllers\Api\General\CertificateRequest\CertificateRequestController;


/** @Auth */
Route::post('login', [AuthController::class, 'login'])->name('user.login');//
Route::post('register', [AppAuthController::class, 'register'])->name('user.register');//
Route::post('reset-password', [AuthController::class, 'resetPassword']);//
Route::post('send/verification-code', [AuthController::class, 'sendVerificationCode']);//
Route::post('check/verification-code', [AuthController::class, 'checkVerificationCode']);//

Route::prefix('sections')->group(function () {
    /**
     * parent_id options :
     * empty for all section at the top layer .
     * section id to get its sub sections .
     */
    Route::prefix('/{parentSection?}')->group(function () {

        //new
        Route::post('open', [SectionStudentController::class, 'open']);

        Route::get('/', [SectionController::class, 'index']);

        //new
        Route::prefix('lessons')->group(function () {
            Route::get('/', [GeneralLessonController::class, 'index']);
            Route::get('/{lesson}', [GeneralLessonController::class, 'show']);
            Route::post('/{lesson}/open', [LessonStudentController::class, 'store']);
            Route::get('{lesson}/files/{file}/download', [GeneralLessonController::class, 'download']);
        });

        //new
        Route::get('/free-lessons', [GeneralLessonController::class, 'courseIndex']);

        //new
        Route::prefix('books')->group(function () {
            Route::get('/', [GeneralBookController::class, 'index']);
            Route::get('/{book}', [GeneralBookController::class, 'show']);
            Route::get('/{book}/download', [GeneralBookController::class, 'download']);
        });
    });
    Route::get('/detail/{section}', [SectionController::class, 'show']);//
});

Route::group(['middleware' => ['auth:api', 'last.active']], function () {
    /** @Auth */

    Route::post('update-email', [AuthController::class, 'resetEmail']);//
    Route::post('update-phone', [AuthController::class, 'resetPhone']);//

    Route::get('notifications', [NotificationController::class, 'index']);

    Route::post('logout', [AuthController::class, 'logout']);//
    Route::get('/check/auth', [AuthController::class, 'authCheck']);//
    Route::get('profile', [AuthController::class, 'profile']);//
    Route::put('change-password', [AuthController::class, 'changePassword']);//
    Route::put('profile/update', [AuthController::class, 'updateProfile']);//

    Route::prefix('course-student')->group(function () {
        Route::post('/', [SectionStudentController::class, 'store']);
    });

    //do we need those routes ?
    // Route::prefix('students')->group(function () {
    //     Route::get('/', [StudentController::class, 'index']);
    //     Route::get('/{student}', [StudentController::class, 'show']);
    // });

    Route::prefix('auth')->group(function () {
        Route::prefix('/done-lessons')->group(function () {
            Route::get('/', [LessonStudentController::class, 'doneLessons']);
        });
        Route::prefix('/courses')->group(function () {
            Route::get('/', [CourseController::class, 'myCourses']);
        });
        Route::prefix('exams')->group(function () {
            Route::get('/', [ExamController::class, 'myExams']);
        });
    });

    Route::prefix('/student/subs-requests')->group(function () {
        Route::post('/', [SubscriptionRequestController::class, 'store']);
        Route::get('/', [SubscriptionRequestController::class, 'index']);
        Route::post('/coupon-check', [SubscriptionRequestController::class, 'checkCoupon']);
    });

    //new
    Route::prefix('certificate-requests')->group(function () {//
        Route::get('/', [CertificateRequestController::class, 'index']);//
        Route::post('/', [CertificateRequestController::class, 'store']);//
    });
});

/**@Guest */

//new
Route::prefix('/student-exams')->group(function () {
    Route::post('{exam}/create', [StudentExamController::class, 'create'])
        ->middleware(['auth:api', 'last.active']);
    Route::post('{exam}/store', [StudentExamController::class, 'store']);
});

Route::post('/contact-messages', [ContactMessageController::class, 'store']);//

Route::prefix('offers')->group(function () {
    Route::get('/', [OfferController::class, 'index']);//
    Route::get('/{offer}', [OfferController::class, 'show']);//
});

Route::get('/top-students', [TopStudentController::class, 'index']);//

Route::prefix('teachers')->group(function () {
    Route::get('/', [TeacherController::class, 'index']);
    Route::get('/{teacher}', [TeacherController::class, 'show']);
    Route::get('/{teacher}/courses', [CourseTeacherController::class, 'getCourses']);
});

//new
Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
});

//new
Route::prefix('students')->group(function () {
    Route::get('/', [StudentController::class, 'index']);
});

//new
Route::get('/infos', [InfoController::class, 'index']);//contact us and about for mobile
Route::get('/home', [GeneralHomeController::class, 'index']);//home website
Route::get('/home/mobile', [AppHomeController::class, 'index']);//home mobile

Route::prefix('exams')->group(function () {
    Route::get('/', [ExamController::class, 'index']);
    Route::post('/', [ExamController::class, 'store']);
    Route::post('/generate', [\App\Http\Controllers\Api\Admin\Exam\ExamController::class, 'store']);

    Route::get('/{exam}', [ExamController::class, 'show']);
    Route::post('/{exam}/open', [StudentExamController::class, 'open'])->middleware(['auth:api', 'last.active']);
});
//todo move them from guest 
Route::prefix('questions')->group(function () {
    Route::get('/', [QuestionsController::class, 'index']);
});
Route::prefix('pages')->group(function () {
    Route::get('/', [QuestionsController::class, 'pages']);
});

Route::prefix('storage')->group(function () {
    Route::get('download', [StorageController::class, 'download']);
});

Route::apiResource('governorates', GovernorateController::class)->only('show', 'index');
