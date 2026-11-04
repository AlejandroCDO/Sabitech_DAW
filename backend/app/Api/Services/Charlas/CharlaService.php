<?php

namespace App\Api\Services\Charlas;

use App\Api\Exceptions\SinPlazasException;
use App\Enums\EstadoReserva;
use App\Models\Charla;
use App\Models\Reserva;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Lógica de negocio de las charlas. Los controladores validan la entrada (Requests)
 * y delegan aquí; el servicio mantiene los invariantes del modelo de datos.
 *
 * plazas_ocupadas cuenta las reservas activas (pendiente + confirmada).
 */
class CharlaService
{
    private const RELACIONES = ['ponente', 'ubicacion'];

    private const CAMPOS_EDITABLES = [
        'ponente_id', 'ubicacion_id', 'titulo', 'descripcion', 'inicio', 'fin', 'aforo',
    ];

    // ------------------------------------------------------------------
    // Consultas
    // ------------------------------------------------------------------

    /** Listado para administración (incluye canceladas salvo que se filtre). */
    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        return Charla::query()
            ->with(self::RELACIONES)
            ->when(!empty($filtros['ponente_id']), fn ($q) => $q->where('ponente_id', $filtros['ponente_id']))
            ->when(!empty($filtros['ubicacion_id']), fn ($q) => $q->where('ubicacion_id', $filtros['ubicacion_id']))
            ->when(!empty($filtros['solo_activas']), fn ($q) => $q->where('cancelada', false))
            ->orderBy('inicio')
            ->paginate(min($perPage, 100));
    }

    /** Listado público: solo charlas no canceladas que aún no han terminado. */
    public function proximas(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        return Charla::query()
            ->with(self::RELACIONES)
            ->where('cancelada', false)
            ->where('fin', '>', now())
            ->when(!empty($filtros['provincia']), fn ($q) => $q->whereHas(
                'ubicacion', fn ($u) => $u->where('provincia', $filtros['provincia'])
            ))
            ->when(!empty($filtros['municipio']), fn ($q) => $q->whereHas(
                'ubicacion', fn ($u) => $u->where('municipio', $filtros['municipio'])
            ))
            ->orderBy('inicio')
            ->paginate(min($perPage, 100));
    }

    public function obtener(Charla $charla): Charla
    {
        return $charla->load(self::RELACIONES);
    }

    // ------------------------------------------------------------------
    // Administración
    // ------------------------------------------------------------------

    public function crear(array $datos): Charla
    {
        $this->validarFechas($datos['inicio'], $datos['fin']);

        // Arr::only: aunque llegue "plazas_ocupadas" o "cancelada" en la petición, se ignoran.
        $charla = Charla::create(Arr::only($datos, self::CAMPOS_EDITABLES) + [
            'plazas_ocupadas' => 0,
            'cancelada'       => false,
        ]);

        return $charla->load(self::RELACIONES);
    }

    public function actualizar(Charla $charla, array $datos): Charla
    {
        return DB::transaction(function () use ($charla, $datos) {
            // Bloqueo de fila: evita que entre una reserva mientras se cambia el aforo.
            $actual = Charla::lockForUpdate()->findOrFail($charla->id);

            if ($actual->cancelada) {
                throw new ConflictHttpException('No se puede modificar una charla cancelada.');
            }

            $datos = Arr::only($datos, self::CAMPOS_EDITABLES);

            $this->validarFechas($datos['inicio'] ?? $actual->inicio, $datos['fin'] ?? $actual->fin);

            if (isset($datos['aforo']) && $datos['aforo'] < $actual->plazas_ocupadas) {
                throw ValidationException::withMessages([
                    'aforo' => "El aforo no puede ser menor que las plazas ya ocupadas ({$actual->plazas_ocupadas}).",
                ]);
            }

            $actual->update($datos);

            return $actual->load(self::RELACIONES);
        });
    }

    /**
     * Cancela la charla y todas sus reservas activas.
     * Devuelve la charla; si quieres avisar por email a los afectados, este es el sitio
     * para despachar el Job/Mail correspondiente (después del commit).
     */
    public function cancelar(Charla $charla): Charla
    {
        return DB::transaction(function () use ($charla) {
            $actual = Charla::lockForUpdate()->findOrFail($charla->id);

            if (!$actual->cancelada) {
                Reserva::activas()
                    ->where('charla_id', $actual->id)
                    ->update(['estado' => EstadoReserva::Cancelada->value]);

                $actual->update(['cancelada' => true, 'plazas_ocupadas' => 0]);
            }

            return $actual->load(self::RELACIONES);
        });
    }

    public function eliminar(Charla $charla): void
    {
        if ($charla->reservas()->exists()) {
            throw new ConflictHttpException('La charla tiene reservas. Cancélala en lugar de borrarla.');
        }

        $charla->delete();
    }

    // ------------------------------------------------------------------
    // Plazas (las usará el servicio de reservas)
    // ------------------------------------------------------------------

    /**
     * Ocupa una plaza de forma atómica. Un único UPDATE condicionado garantiza que,
     * con peticiones simultáneas, nunca se supere el aforo (el CHECK de la BD es la red de seguridad).
     *
     * @throws SinPlazasException     si no quedan plazas
     * @throws ConflictHttpException  si la charla está cancelada, ya ha terminado o no existe
     */
    public function ocuparPlaza(int $charlaId): void
    {
        $filas = Charla::whereKey($charlaId)
            ->where('cancelada', false)
            ->where('fin', '>', now())
            ->whereColumn('plazas_ocupadas', '<', 'aforo')
            ->increment('plazas_ocupadas');

        if ($filas === 1) {
            return;
        }

        $charla = Charla::find($charlaId);

        if (!$charla || $charla->cancelada || $charla->fin->isPast()) {
            throw new ConflictHttpException('La charla no admite reservas.');
        }

        throw new SinPlazasException();
    }

    /** Libera una plaza (al cancelar una reserva activa). Nunca baja de 0. */
    public function liberarPlaza(int $charlaId): void
    {
        Charla::whereKey($charlaId)
            ->where('plazas_ocupadas', '>', 0)
            ->decrement('plazas_ocupadas');
    }

    // ------------------------------------------------------------------

    private function validarFechas(mixed $inicio, mixed $fin): void
    {
        if (Carbon::parse($fin)->lessThanOrEqualTo(Carbon::parse($inicio))) {
            throw ValidationException::withMessages([
                'fin' => 'La fecha de fin debe ser posterior a la de inicio.',
            ]);
        }
    }
}