<?php

use App\Models\User;
use App\Models\Coupon;
use App\Models\Section;
use Illuminate\Support\Facades\Schema;
use App\Enums\SectionStudentStatusEnum;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_requests', function (Blueprint $table) {
            $table->id();
            //new
            $table->foreignIdFor(Coupon::class)->nullable()->constrained()->nullOnDelete();
            
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Section::class)->constrained()->cascadeOnDelete();
            $table->string('image');
            $table->text('reject_reason')->nullable();
            $table->enum('status', SectionStudentStatusEnum::all())
                ->default(SectionStudentStatusEnum::PENDING->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_requests');
    }
};
