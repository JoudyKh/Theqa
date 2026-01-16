<?php

use App\Models\City;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('location')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('full_name')->nullable();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->string('username');
            $table->string('email')->nullable();

            $table->string('phone_number')->nullable();
            $table->string('family_phone_number')->nullable();

            $table->string('phone_number_country_code')->nullable();
            $table->string('family_phone_number_country_code')->nullable();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->dateTime('last_active_at')->nullable();

            $table->boolean('is_active')->default(1);
            $table->boolean('is_banned')->default(0);

            //for teachers
            $table->text('description')->nullable();
            $table->boolean('is_hidden')->nullable()->default(false);

            $table->foreignIdFor(City::class)->nullable()->constrained()->nullOnDelete();

            $table->unique(['email','deleted_at']);
            $table->unique(['username','deleted_at']);
            $table->unique(['phone_number','deleted_at']);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
