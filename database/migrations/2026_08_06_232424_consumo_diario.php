<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumo_diario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('suplemento_id')->constrained('suplementos')->cascadeOnDelete();
            $table->date('data_consumo');
            $table->boolean('consumido')->default(false);
            $table->timestamps();

            $table->unique(['suplemento_id', 'data_consumo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumo_diario');
    }
};
