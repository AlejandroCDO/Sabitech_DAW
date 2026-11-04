<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('charla_id');
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada'])->default('pendiente');
            $table->string('qr_secret', 64);
            $table->integer('email_intentos')->default(0);
            $table->timestamp('email_ultimo_envio')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['usuario_id', 'charla_id'], 'uq_reservas_usuario_charla');
            $table->unique('qr_secret', 'uq_reservas_qr_secret');
            $table->index(['charla_id', 'estado'], 'idx_reservas_charla_estado');
            $table->foreign('usuario_id', 'fk_reservas_usuario')->references('id')->on('usuarios')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('charla_id', 'fk_reservas_charla')->references('id')->on('charlas')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
