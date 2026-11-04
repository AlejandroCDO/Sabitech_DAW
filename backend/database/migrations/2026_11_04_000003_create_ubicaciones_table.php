<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->string('provincia', 100);
            $table->string('municipio', 100);
            $table->string('calle', 100);
            $table->string('codigo_postal', 10);
            $table->string('numero', 10);
            $table->string('puerta', 10)->nullable();
            $table->string('nombre', 50);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['provincia', 'municipio'], 'idx_ubicaciones_provincia_municipio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones');
    }
};
