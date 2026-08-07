<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suplementos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->decimal('quantidade_dose', 8, 2);
            $table->string('unidade_medida');
            $table->string('informacao_adicional_dose')->nullable();
            $table->string('horario_indicado');
            $table->string('regra_uso')->default('diario');
            $table->string('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suplementos');
    }
};
