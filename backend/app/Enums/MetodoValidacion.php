<?php

namespace App\Enums;

/**
 * Cómo se registró una asistencia.
 * No tiene columna propia en el modelo de datos: asistencias.qr_token NULL significa manual.
 */
enum MetodoValidacion: string
{
    case Qr     = 'qr';
    case Manual = 'manual';
}
