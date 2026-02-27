<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('company')->nullable();
            $table->string('position')->nullable();
            $table->string('website')->nullable();
            $table->string('linkedin')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable()->default('Italy');
            $table->string('postal_code')->nullable();
            $table->enum('status', ['lead','prospect','active','inactive','churned'])->default('lead');
            $table->enum('priority', ['low','medium','high'])->default('medium');
            $table->string('source')->nullable();
            $table->string('avatar')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('annual_value', 12, 2)->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status','priority']);
            $table->index('email');
            $table->index('company');
        });
    }
    public function down(): void { Schema::dropIfExists('customers'); }
};
