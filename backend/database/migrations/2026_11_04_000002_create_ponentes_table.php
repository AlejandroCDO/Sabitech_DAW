<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ponentes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('biografia')->nullable();
            $table->string('foto_url', 255)->nullable();
            $table->string('especialidad', 100)->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ponentes');
    }
};
