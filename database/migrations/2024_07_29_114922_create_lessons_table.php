<?php

use App\Models\Exam;
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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_free')->default(false) ;
            $table->text('description');
            $table->string('video_url');
            $table->string('cover_image');
            $table->time('time');
            $table->integer('lesson_order');    
            $table->foreignIdFor(Section::class)->constrained()->cascadeOnDelete() ;
            $table->softDeletes();
            $table->unique(['deleted_at' , 'lesson_order' , 'section_id']); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
