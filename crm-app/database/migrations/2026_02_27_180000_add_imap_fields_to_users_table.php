<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('imap_host')->nullable()->after('microsoft_user_id');
            $table->integer('imap_port')->nullable()->after('imap_host');
            $table->string('imap_encryption')->nullable()->default('ssl')->after('imap_port');
            $table->string('imap_username')->nullable()->after('imap_encryption');
            $table->text('imap_password')->nullable()->after('imap_username');
            $table->string('imap_protocol')->nullable()->default('imap')->after('imap_password');
            $table->timestamp('imap_last_sync_at')->nullable()->after('imap_protocol');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['imap_host','imap_port','imap_encryption','imap_username','imap_password','imap_protocol','imap_last_sync_at']);
        });
    }
};
