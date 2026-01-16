<?php

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
        Schema::create('purchase_codes', function (Blueprint $table) {
        
            $table->id();
            $table->string('code' , 16)->unique() ;
            $table->date('expire_date') ;
            $table->integer('usage_limit') ;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_codes');
    }
};
