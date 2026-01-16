<?php

use App\Models\Exam;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Section;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_exams', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class , 'student_id')->constrained('users')->restrictOnDelete();
            $table->foreignIdFor(Exam::class)->constrained()->restrictOnDelete();
            $table->dateTime('start_date')->nullable() ;
            $table->dateTime('end_date')->nullable() ;
            $table->integer('attempts_count')->default(1);
            $table->double('degree')->nullable();
            $table->double('total_degree')->nullable();
            $table->double('exam_degree');
            $table->integer('exam_pass_percentage');

            $table->boolean('on_time')->nullable()->default(null) ;

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_exams');
    }
};
