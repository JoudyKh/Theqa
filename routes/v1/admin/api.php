<?php

use App\Constants\Constants;
use App\Http\Controllers\Api\Admin\Firebase\FirebaseController;
use Illuminate\Support\Facades\Route;
use App\Services\Admin\WhatsappService;
use App\Http\Controllers\Api\Admin\Exam\ExamController;
use App\Http\Controllers\Api\Admin\Admin\AdminController;
use App\Http\Controllers\Api\Admin\Offer\OfferController;
use App\Http\Controllers\Api\General\Auth\AuthController;
use App\Http\Controllers\Api\General\Info\InfoController;
use App\Http\Controllers\Api\Admin\Coupon\CouponController;
use App\Http\Controllers\Api\Admin\Option\OptionController;
use App\Http\Controllers\Api\Admin\Slider\SliderController;
use App\Http\Controllers\Api\Admin\Question\QuestionController;
use App\Http\Controllers\Api\Admin\Whatsapp\WhatsappController;
use App\Http\Controllers\Api\General\Section\SectionController;
use App\Http\Controllers\Api\General\Storage\StorageController;
use App\Http\Controllers\Api\General\Student\StudentController;
use App\Http\Controllers\Api\General\Teacher\TeacherController;
use App\Http\Controllers\Api\Admin\TopStudent\TopStudentController;
use App\Http\Controllers\Api\Admin\PurchaseCode\PurchaseCodeController;
use App\Http\Controllers\Api\General\Governorate\GovernorateController;
use App\Http\Controllers\Api\Admin\Book\BookController as AdminBookController;
use App\Http\Controllers\Api\Admin\City\CityController as AdminCityController;
use App\Http\Controllers\Api\Admin\Info\InfoController as AdminInfoController;
use App\Http\Controllers\Api\Admin\ContactMessage\ContactMessageController;
use App\Http\Controllers\Api\General\Book\BookController as GeneralBookController;
use App\Http\Controllers\Api\General\Exam\ExamController as GeneralExamController;
use App\Http\Controllers\Api\Admin\Lesson\LessonController as AdminLessonController;
use App\Http\Controllers\Api\Admin\Section\SectionController as AdminSectionController;
use App\Http\Controllers\Api\Admin\Student\StudentController as AdminStudentController;
use App\Http\Controllers\Api\Admin\Teacher\TeacherController as AdminTeacherController;
use App\Http\Controllers\Api\General\Lesson\LessonController as GeneralLessonController;
use App\Http\Controllers\Api\Admin\SubscriptionRequest\SubscriptionRequestController;
use App\Http\Controllers\Api\General\CertificateRequest\CertificateRequestController;
use App\Http\Controllers\Api\Admin\Governorate\GovernorateController as AdminGovernorateController;
use App\Http\Controllers\Api\Admin\CourseTeacher\CourseTeacherController as AdminCourseTeacherController;
use App\Http\Controllers\Api\General\CourseTeacher\CourseTeacherController as GeneralCourseTeacherController;
use App\Http\Controllers\Api\Admin\CertificateRequest\CertificateRequestController as AdminCertificateRequestController;


/** @Auth */
Route::post('login', [AuthController::class, 'login'])->name('admin.login');//
Route::post('reset-password', [AuthController::class, 'resetPassword']);//
Route::post('send/verification-code', [AuthController::class, 'sendVerificationCode']);//
Route::post('check/verification-code', [AuthController::class, 'checkVerificationCode']);//

Route::group(['middleware' => ['auth:api', 'last.active', 'ability:' . Constants::ADMIN_ROLE.'|'.Constants::SUPER_ADMIN_ROLE]], function () {

    /** @Auth */
    Route::post('logout', [AuthController::class, 'logout']);//
    Route::get('/check/auth', [AuthController::class, 'authCheck']);//
    Route::get('profile', [AuthController::class, 'profile']);//
    Route::put('change-password', [AuthController::class, 'changePassword']);//
    Route::put('profile/update', [AuthController::class, 'updateProfile']);//


    Route::prefix('sections')->group(function () {
        /**
         * parent_id options :
         * empty for all section at the top layer
         * section id to get its sub sections .
         */
        Route::post('open-for-student', [AdminSectionController::class, 'open']);
        Route::post('cancel-for-student', [AdminSectionController::class, 'cancel']);

        Route::post('open-for-teacher', [AdminCourseTeacherController::class, 'store']);
        Route::post('cancel-for-teacher', [AdminCourseTeacherController::class, 'delete']);

        Route::prefix('/{parentSection?}')->group(function () {

            Route::get('/', [SectionController::class, 'index']);//

            Route::prefix('/lessons')->group(function () {
                Route::get('/', [GeneralLessonController::class, 'index']);
                Route::get('/{lesson}', [GeneralLessonController::class, 'show']);
                Route::post('/', [AdminLessonController::class, 'store']);
                Route::put('/{lesson}', [AdminLessonController::class, 'update']);
                Route::delete('/{lesson}/{force?}', [AdminLessonController::class, 'delete']);
                Route::patch('/{lesson}/restore', [AdminLessonController::class, 'restore']);
                Route::get('{lesson}/files/{file}/download', [GeneralLessonController::class, 'download']);
            });

            Route::prefix('books')->group(function () {
                Route::get('/', [GeneralBookController::class, 'index']);
                Route::get('/{book}', [GeneralBookController::class, 'show']);
                Route::post('/', [AdminBookController::class, 'store']);
                Route::put('/{book}', [AdminBookController::class, 'update']);
                Route::delete('/{book}', [AdminBookController::class, 'delete']);
                //Route::patch('restore' , [AdminBookController::class , 'restore']) ;
                Route::get('/{book}/download', [GeneralBookController::class, 'download']);
            });
        });
        Route::get('/detail/{section}', [SectionController::class, 'show']);//
        Route::post('/store/{type}/{parentSection?}', [AdminSectionController::class, 'store']);
        Route::put('/{section}', [AdminSectionController::class, 'update']);//
        Route::delete('/{section}', [AdminSectionController::class, 'destroy'])->withTrashed();//
    });

    Route::prefix('contact-messages')->group(function () {
        Route::get('/', [ContactMessageController::class, 'index']);//
        Route::delete('{contactMessage}/{force?}', [ContactMessageController::class, 'delete'])->withTrashed();//
        Route::patch('{contactMessage}/restore', [ContactMessageController::class, 'restore'])->withTrashed();//
    });

    Route::prefix('offers')->group(function () {//
        Route::get('/', [OfferController::class, 'index']);//
        Route::get('/{offer}', [OfferController::class, 'show']);
        Route::post('/', [OfferController::class, 'store']);//
        Route::put('/{offer}', [OfferController::class, 'update']);//
        Route::delete('/{offer}/{force?}', [OfferController::class, 'delete'])->withTrashed();//
    });

    //new
    Route::prefix('top-students')->group(function () {//
        Route::get('/', [TopStudentController::class, 'index']);//
        Route::get('/{top_student}', [TopStudentController::class, 'show']);
        Route::post('/', [TopStudentController::class, 'store']);//
        Route::put('/{top_student}', [TopStudentController::class, 'update']);//
        Route::delete('/{top_student}/{force?}', [TopStudentController::class, 'delete'])->withTrashed();//
    });

    //new
    Route::prefix('certificate-requests')->group(function () {//
        Route::get('/', [CertificateRequestController::class, 'index']);//
        Route::get('/{certificate_request}', [CertificateRequestController::class, 'show']);
        Route::post('/', [CertificateRequestController::class, 'store']);//
        Route::put('/{certificate_request}', [AdminCertificateRequestController::class, 'update']);//
        Route::delete('/{certificate_request}/{force?}', [AdminCertificateRequestController::class, 'delete'])->withTrashed();//
    });

    //new
    Route::prefix('sliders')->group(function () {//
        Route::get('/', [SliderController::class, 'index']);//
        Route::get('/{slider}', [SliderController::class, 'show']);
        Route::post('/', [SliderController::class, 'store']);//
        Route::put('/{slider}', [SliderController::class, 'update']);//
        Route::delete('/{slider}/{force?}', [SliderController::class, 'delete'])->withTrashed();//
    });

    Route::prefix('teachers')->group(function () {
        Route::get('/', [TeacherController::class, 'index']);
        Route::get('/{teacher}', [TeacherController::class, 'show']);
        Route::get('/{teacher}/courses', [GeneralCourseTeacherController::class, 'getCourses']);//new
        Route::post('/', [AdminTeacherController::class, 'store']);
        Route::put('/{teacher}', [AdminTeacherController::class, 'update']);
        Route::delete('/{teacher}/{force?}', [AdminTeacherController::class, 'delete'])->withTrashed();
    });

    Route::prefix('students')->group(function () {
        Route::prefix('/subs-requests')->group(function () {
            Route::get('/', [SubscriptionRequestController::class, 'index']);
            Route::delete('/{subscriptionRequest}', [SubscriptionRequestController::class, 'delete'])->withTrashed();
            ;
            Route::post('/{subscriptionRequest}/status', [SubscriptionRequestController::class, 'manageStatus'])->withTrashed();
            ;
        });
        //new
        Route::post('bulk-delete' , [AdminStudentController::class , 'bulkDelete']);
        Route::get('/', [StudentController::class, 'index']);
        Route::get('/{student}', [StudentController::class, 'show']);
        Route::post('/', [AdminStudentController::class, 'store']);
        Route::put('/{student}', [AdminStudentController::class, 'update']);
        Route::delete('/{student}', [AdminStudentController::class, 'delete'])->withTrashed();
    });

    Route::prefix('admins')->group(function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::get('/{admin}', [AdminController::class, 'show']);
        Route::post('/', [AdminController::class, 'store']);
        Route::put('/{admin}', [AdminController::class, 'update']);
        Route::delete('/{admin}', [AdminController::class, 'delete'])->withTrashed();
    });

    Route::prefix('purchase-codes')->group(function () {
        Route::get('/', [PurchaseCodeController::class, 'index']);
        Route::get('/{purchaseCode}', [PurchaseCodeController::class, 'show']);
        Route::post('/', [PurchaseCodeController::class, 'store']);
        Route::put('/{purchaseCode}', [PurchaseCodeController::class, 'update']);
        Route::delete('/{purchaseCode}', [PurchaseCodeController::class, 'delete']);
    });

    Route::post('/clone/exams', [ExamController::class, 'clone']);

    Route::prefix('exams')->group(function () {
        //new
        Route::post('/bulk-delete', [ExamController::class, 'bulkDelete']);

        Route::get('/', [GeneralExamController::class, 'index']);
        Route::get('/{exam}', [GeneralExamController::class, 'show']);
        Route::get('/{exam}/students', [ExamController::class, 'examResults']);
        Route::post('/', [ExamController::class, 'store']);

        Route::put('/{exam}', [ExamController::class, 'update']);
        Route::delete('/{exam}/{force?}', [ExamController::class, 'delete'])->withTrashed();
        Route::patch('/{exam}/restore', [ExamController::class, 'restore'])->withTrashed();
    });

    Route::prefix('exam/questions')->group(function () {
        Route::get('/search', [QuestionController::class, 'search']);
        Route::get('/', [QuestionController::class, 'index']);
        Route::get('/{question}', [QuestionController::class, 'show']);
        Route::post('/', [QuestionController::class, 'store']);
        Route::put('/{question}', [QuestionController::class, 'update']);
        Route::delete('/{question}/{force?}', [QuestionController::class, 'delete'])->withTrashed();
        Route::patch('/{question}/restore', [QuestionController::class, 'restore'])->withTrashed();
    });

    Route::prefix('exam/question/options')->group(function () {
        Route::put('/{option}', [OptionController::class, 'update']);
        Route::delete('/{option}/{force?}', [OptionController::class, 'delete'])->withTrashed();
        Route::patch('/{option}/restore', [OptionController::class, 'restore'])->withTrashed();
    });

    Route::prefix('infos')->group(function () {
        Route::get('/', [InfoController::class, 'index']);//
        Route::post('/update', [AdminInfoController::class, 'update']);//
    });

    Route::prefix('coupons')->group(function () {//
        Route::get('/', [CouponController::class, 'index']);//
        Route::get('/{coupon}', [CouponController::class, 'show']);
        Route::post('/', [CouponController::class, 'store']);//
        Route::put('/{coupon}', [CouponController::class, 'update']);//
        Route::delete('/{coupon}/{force?}', [CouponController::class, 'delete'])->withTrashed();//
    });

    Route::apiResource('governorates' , AdminGovernorateController::class)->except('show') ;
    Route::apiResource('governorates' , GovernorateController::class)->only('show') ;
    Route::apiResource('governorates.cities' , AdminCityController::class);

    //new
    // Route::post('/whatsapp/broadcast' , [WhatsappController::class , 'broadcast']) ;//
    Route::post('/firebase/broadcast' , [FirebaseController::class , 'broadcast']) ;//

});

Route::prefix('storage')->group(function(){
    Route::get('download' , [StorageController::class , 'download']) ;
});
