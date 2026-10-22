<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('dose', 8, 2);
            $table->string('unit');
            $table->string('dose_notes')->nullable();
            $table->string('scheduled_time')->nullable();
            $table->string('usage_rule')->default('daily');
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('supplement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplement_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->boolean('taken')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'supplement_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplement_logs');
        Schema::dropIfExists('supplements');
    }
};
