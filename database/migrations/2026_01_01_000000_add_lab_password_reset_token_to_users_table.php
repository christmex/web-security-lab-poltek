<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SECURITY LAB (intentional): stores the PLAINTEXT password-reset token on the
 * users row so the vulnerable profile API can leak it. A real application must
 * never store a usable reset token in plaintext.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password_reset_token')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_reset_token');
        });
    }
};
