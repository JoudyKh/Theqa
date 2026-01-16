<?php

use App\Models\Section;
use App\Models\PurchaseCode;
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
        Schema::create('purchase_code_section', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Section::class)->constrained()->cascadeOnDelete() ;
            $table->foreignIdFor(PurchaseCode::class)->constrained()->cascadeOnDelete() ;
            $table->unique(['section_id' , 'purchase_code_id']) ;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_code_sections');
    }
};
