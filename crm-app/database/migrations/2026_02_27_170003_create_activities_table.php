<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['email','call','whatsapp','sms','meeting','note','task','email_incoming','email_outgoing']);
            $table->string('subject');
            $table->longText('body')->nullable();
            $table->enum('direction', ['inbound','outbound','internal'])->default('internal');
            $table->enum('status', ['pending','completed','cancelled','scheduled'])->default('completed');
            $table->integer('duration_minutes')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email_from')->nullable();
            $table->string('email_to')->nullable();
            $table->string('email_message_id')->nullable()->index();
            $table->string('outlook_conversation_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['customer_id','type','occurred_at']);
            $table->index(['user_id','occurred_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('activities'); }
};
