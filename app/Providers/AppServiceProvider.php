<?php

namespace App\Providers;

use Event;
use App\Models\Book;
use App\Models\Exam;
use App\Models\Info;
use App\Models\User;
use App\Models\Section;
use App\Constants\Constants;
use App\Observers\BookObserver;
use App\Observers\ExamObserver;
use App\Observers\UserObserver;
use App\Observers\SectionObserver;
use Illuminate\Support\Facades\Auth;
use App\Events\CourseSubscribedEvent;
use Illuminate\Support\ServiceProvider;
use App\Listeners\CourseSubscribedListener;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('admin', function ($app) {
            $user = Auth::guard('sanctum')->user();
            return $user && $user->hasRole(Constants::ADMIN_ROLE) ? $user : null;
        });

        $this->app->singleton('student', function ($app) {
            $user = Auth::guard('sanctum')->user();
            return $user && $user->hasRole(Constants::STUDENT_ROLE) ? $user : null;
        });


    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);


        Section::observe(SectionObserver::class);
        User::observe(UserObserver::class);
        Exam::observe(ExamObserver::class);
        Book::observe(BookObserver::class);


        //needs cache:clear  or config:clear when restarting the server
        Info::initialize();
    }

}
