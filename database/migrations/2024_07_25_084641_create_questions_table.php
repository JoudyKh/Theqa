<?php

use App\Models\Exam;
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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->longText('text');
            $table->integer('degree')->default(1);
            $table->longText('note')->nullable() ;
            $table->string('image')->nullable();
            $table->string('note_image')->nullable();
            $table->longText('video')->nullable();
            $table->integer('page_number')->nullable();
            
            /**
             * 
             * 
             * $table->integer('order');
             * should we add order field to allow the teacher to control the order 
             * of the questions in the exam ?
             * or just depend on the created at and allow the teacher to update it ?
             * 
             * 
             * 
             * $table->string('group_question')->nullable() ;
             * to group questions 
             */
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
