<?php

use App\Api\Controllers\Admin\AsistenciaCharlaController;
use App\Api\Controllers\Admin\CharlaController;
use App\Api\Controllers\Admin\PonenteController;
use App\Api\Controllers\Admin\ReservaCharlaController;
use App\Api\Controllers\Admin\UbicacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('charlas', CharlaController::class);
    Route::post('charlas/{charla}/cancelar', [CharlaController::class, 'cancelar']);
    Route::apiResource('ponentes', PonenteController::class);
    Route::apiResource('ubicaciones', UbicacionController::class)->parameters(['ubicaciones' => 'ubicacion']);
    Route::get('charlas/{charla}/reservas', [ReservaCharlaController::class, 'index']);
    Route::post('charlas/{charla}/reservas/{reserva}/cancelar', [ReservaCharlaController::class, 'cancelar'])->scopeBindings();
    Route::get('charlas/{charla}/asistencias', [AsistenciaCharlaController::class, 'index']);
});
