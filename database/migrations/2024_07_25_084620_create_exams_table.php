<?php

use App\Models\Exam;
use App\Models\Lesson;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('model', 'exams_model_morph');
            $table->text('description')->nullable();
            $table->integer('minutes');
            $table->integer('exam_order')->nullable();
            $table->integer('pass_percentage')->default(80);

            $table->string('solution_file')->nullable();

            $table->string('image')->nullable();
            $table->string('name')->nullable();
            $table->boolean('is_free')->default(false);

            $table->integer('random_questions_max')->nullable();

            $table->double('degree')->default(100.0);
            $table->foreignId('student_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->softDeletes();

            $table->string('type')->default(Exam::types()::ORIGINAL);

            $table->unique(['deleted_at', 'exam_order', 'model_id', 'model_type']);
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
