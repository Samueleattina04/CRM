<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('position')->nullable()->after('avatar');
            $table->boolean('is_active')->default(true)->after('position');
            $table->string('microsoft_token')->nullable()->after('is_active');
            $table->string('microsoft_refresh_token')->nullable()->after('microsoft_token');
            $table->timestamp('microsoft_token_expires_at')->nullable()->after('microsoft_refresh_token');
            $table->string('microsoft_user_id')->nullable()->after('microsoft_token_expires_at');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone','avatar','position','is_active','microsoft_token','microsoft_refresh_token','microsoft_token_expires_at','microsoft_user_id']);
        });
    }
};
