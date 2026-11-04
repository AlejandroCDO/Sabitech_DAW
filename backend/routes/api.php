
<?php

use Illuminate\Support\Facades\Route;

// CONTROLADORES PÚBLICOS
use App\Api\Controllers\Public\CharlaController as PublicCharlaController;
use App\Api\Controllers\Public\ProvinciaController;

// AUTENTICACIÓN
use App\Api\Controllers\Auth\AuthController;

// ASISTENTE
use App\Api\Controllers\Asistente\ReservaController;
use App\Api\Controllers\Asistente\QrController;
use App\Api\Controllers\Asistente\ReenvioConfirmacionController;

// PERSONAL DE ACCESO
use App\Api\Controllers\Acceso\BusquedaReservaController;
use App\Api\Controllers\Acceso\ValidacionQrController;
use App\Api\Controllers\Acceso\ValidacionManualController;

// ADMINISTRACIÓN
use App\Api\Controllers\Admin\CharlaController as AdminCharlaController;
use App\Api\Controllers\Admin\PonenteController;
use App\Api\Controllers\Admin\UbicacionController;
use App\Api\Controllers\Admin\ReservaCharlaController;
use App\Api\Controllers\Admin\AsistenciaCharlaController;


/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

Route::get('/charlas', [PublicCharlaController::class, 'index']);
Route::get('/charlas/{charla}', [PublicCharlaController::class, 'show']);
Route::get('/provincias', ProvinciaController::class);


/*
|--------------------------------------------------------------------------
| AUTENTICACIÓN
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [AuthController::class, 'usuario']);

        Route::post('/logout', [AuthController::class, 'logout']);

    });

});


/*
|--------------------------------------------------------------------------
| RUTAS DEL ASISTENTE
|--------------------------------------------------------------------------
*/

Route::prefix('asistente')
    ->middleware(['auth:sanctum', 'role:asistente'])
    ->group(function () {

        // Consultar mis reservas
        Route::get('/reservas', [ReservaController::class, 'index']);

        // Reservar una charla
        Route::post('/reservas', [ReservaController::class, 'store']);

        // Cancelar una reserva propia
        Route::delete('/reservas/{reserva}', [ReservaController::class, 'cancelar']);

        // Obtener el QR temporal
        Route::get('/reservas/{reserva}/qr', [QrController::class, 'mostrar']);

        // Reenviar confirmación
        Route::post(
            '/reservas/{reserva}/reenviar-confirmacion',
            [ReenvioConfirmacionController::class, 'reenviar']
        );

    });


/*
|--------------------------------------------------------------------------
| RUTAS DEL PERSONAL DE ACCESO
|--------------------------------------------------------------------------
*/

Route::prefix('acceso')
    ->middleware(['auth:sanctum', 'role:acceso,admin'])
    ->group(function () {

        // Buscar reservas de una charla
        Route::get(
            '/charlas/{charla}/reservas',
            BusquedaReservaController::class
        );

        // Validar mediante QR
        Route::post(
            '/asistencias/qr',
            ValidacionQrController::class
        );

        // Validar manualmente
        Route::post(
            '/asistencias/manual',
            ValidacionManualController::class
        );

    });


/*
|--------------------------------------------------------------------------
| RUTAS DE ADMINISTRACIÓN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {

        // CRUD de charlas
        Route::apiResource('charlas', AdminCharlaController::class);

        Route::post(
            '/charlas/{charla}/cancelar',
            [AdminCharlaController::class, 'cancelar']
        );

        // CRUD de ponentes
        Route::apiResource('ponentes', PonenteController::class);

        // CRUD de ubicaciones
        Route::apiResource('ubicaciones', UbicacionController::class)
            ->parameters(['ubicaciones' => 'ubicacion']);

        // Consultar reservas de una charla
        Route::get(
            '/charlas/{charla}/reservas',
            [ReservaCharlaController::class, 'index']
        );

        // Cancelar una reserva desde administración
        Route::post(
            '/charlas/{charla}/reservas/{reserva}/cancelar',
            [ReservaCharlaController::class, 'cancelar']
        )->scopeBindings();

        // Consultar asistencias de una charla
        Route::get(
            '/charlas/{charla}/asistencias',
            [AsistenciaCharlaController::class, 'index']
        );

    });
