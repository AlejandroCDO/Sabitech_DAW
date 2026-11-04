<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reserva_id');
            $table->unsignedBigInteger('validado_por');
            $table->string('qr_token', 128)->nullable();
            $table->timestamp('validada_en')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique('reserva_id', 'uq_asistencias_reserva_id');
            $table->unique('qr_token', 'uq_asistencias_qr_token');
            $table->index('validado_por', 'idx_asistencias_validado_por');
            $table->foreign('reserva_id', 'fk_asistencias_reserva')->references('id')->on('reservas')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('validado_por', 'fk_asistencias_validador')->references('id')->on('usuarios')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
