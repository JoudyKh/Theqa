<?php

use App\Models\Exam;
use App\Models\User;
use App\Models\Option;
use App\Models\Question;
use App\Models\StudentExam;
use App\Models\StudentAnswer;
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
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            
            $table->foreignIdFor(Question::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Option::class)->nullable()->constrained()->nullOnDelete();

            $table->foreignIdFor(StudentExam::class)->constrained()->cascadeOnDelete();

            $table->unique(['question_id' , 'student_exam_id']) ;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
