<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('stage', ['new','contacted','qualified','proposal','negotiation','won','lost'])->default('new');
            $table->decimal('value', 12, 2)->nullable();
            $table->string('currency')->default('EUR');
            $table->integer('probability')->default(50);
            $table->timestamp('expected_close_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('pipelines'); }
};
