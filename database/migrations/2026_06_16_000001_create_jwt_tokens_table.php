<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jwt_tokens')) {
            return;
        }

        Schema::create('jwt_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('jti')->unique();
            $table->string('refresh_token_hash', 64)->unique();
            $table->timestamp('access_expires_at');
            $table->timestamp('refresh_expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jwt_tokens');
    }
};
