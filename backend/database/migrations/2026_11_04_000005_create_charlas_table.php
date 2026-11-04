<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charlas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ponente_id');
            $table->unsignedBigInteger('ubicacion_id');
            $table->string('titulo', 100);
            $table->text('descripcion');
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->integer('aforo');
            $table->integer('plazas_ocupadas')->default(0);
            $table->boolean('cancelada')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('ponente_id', 'idx_charlas_ponente_id');
            $table->index('ubicacion_id', 'idx_charlas_ubicacion_id');
            $table->index('inicio', 'idx_charlas_inicio');
            $table->foreign('ponente_id', 'fk_charlas_ponente')->references('id')->on('ponentes')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('ubicacion_id', 'fk_charlas_ubicacion')->references('id')->on('ubicaciones')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        // CHECK solo en MySQL (el diagrama los define; el SQLite de los tests no se toca).
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE charlas ADD CONSTRAINT chk_charlas_aforo CHECK (aforo > 0)');
            DB::statement('ALTER TABLE charlas ADD CONSTRAINT chk_charlas_plazas CHECK (plazas_ocupadas <= aforo)');
            DB::statement('ALTER TABLE charlas ADD CONSTRAINT chk_charlas_fechas CHECK (fin > inicio)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('charlas');
    }
};
