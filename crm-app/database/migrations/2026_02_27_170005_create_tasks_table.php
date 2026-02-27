<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['call','email','meeting','follow_up','other'])->default('follow_up');
            $table->enum('priority', ['low','medium','high','urgent'])->default('medium');
            $table->enum('status', ['pending','in_progress','completed','cancelled'])->default('pending');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('reminder_at')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id','status','due_date']);
            $table->index(['customer_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('tasks'); }
};
