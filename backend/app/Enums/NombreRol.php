<?php

namespace App\Enums;

/** Valores de roles.nombre; deben coincidir con los usados en el middleware role:... de las rutas. */
enum NombreRol: string
{
    case Admin = 'admin';
    case Acceso = 'acceso';
    case Asistente = 'asistente';
}
