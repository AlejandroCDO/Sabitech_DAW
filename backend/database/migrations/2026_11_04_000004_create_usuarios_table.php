<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_id');
            $table->string('nombre', 100);
            $table->string('email', 150);
            $table->string('telefono', 15);
            $table->date('fecha_nacimiento');
            $table->string('password', 255);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique('email', 'uq_usuarios_email');
            $table->index('rol_id', 'idx_usuarios_rol_id');
            $table->foreign('rol_id', 'fk_usuarios_rol')->references('id')->on('roles')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
