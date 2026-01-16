<?php

use App\Models\User;
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
        Schema::create('certificate_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class , 'student_id')->constrained('users')->cascadeOnDelete() ;
            $table->foreignIdFor(Section::class , 'course_id')->constrained('sections')->cascadeOnDelete() ;
            $table->string('status');
            $table->string('file')->nullable();
            $table->date('rejected_at')->nullable();
            $table->date('accepted_at')->nullable();
            $table->text('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_requests');
    }
};
