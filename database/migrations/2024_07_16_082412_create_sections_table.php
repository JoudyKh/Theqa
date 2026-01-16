<?php

use App\Constants\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('sections')->onDelete('set null');
            $table->enum('type', array_keys(Constants::SECTIONS_TYPES));
            //general attr
            $table->text('name')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();

            //customized attr
            $table->string('is_free')->default(0);
            $table->double('price')->nullable();
            $table->double('discount')->nullable();
            $table->boolean('is_special')->default(false);

            $table->string('intro_video')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
