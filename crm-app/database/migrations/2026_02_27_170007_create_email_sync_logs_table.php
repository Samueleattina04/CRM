<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('email_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('message_id')->unique();
            $table->string('conversation_id')->nullable();
            $table->string('subject')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->text('to_addresses')->nullable();
            $table->longText('body_preview')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('direction')->default('inbound');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
            $table->index(['user_id','received_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('email_sync_logs'); }
};
