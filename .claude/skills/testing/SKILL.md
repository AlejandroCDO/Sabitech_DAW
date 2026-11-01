---
name: testing
description: Estrategia de pruebas y verificación de SABITECH WEB con los comandos reales del proyecto (PHPUnit vía composer test en Laravel, oxlint y vite build en React). Úsala para verificar cualquier cambio y para decidir qué pruebas nuevas hacen falta.
---

# testing

## Objetivo
Verificar los cambios con las herramientas configuradas y comunicar resultados reales, nunca supuestos.

## Cuándo usarla
Después de cada implementación, antes de aceptar un trabajo y dentro de `release-check`.

## Herramientas disponibles (verificadas en la configuración)
| Ámbito | Comando | Tipo |
|---|---|---|
| Laravel | `cd laravel && composer test` | PHPUnit 12 (suites `Unit` y `Feature`, SQLite en memoria) |
| Laravel | `cd laravel && php artisan test --filter=NombreTest` | Prueba concreta |
| Laravel | `cd laravel && vendor/bin/pint --test` | Estilo PHP (no modifica) |
| React | `cd react && npm run lint` | oxlint |
| React | `cd react && npm run build` | Compilación Vite |

**Sin ejecutor de pruebas**: React (no hay Vitest ni Testing Library). Añadirlos es instalar dependencias: requiere autorización del usuario.

## Procedimiento
1. Comprueba prerrequisitos: `laravel/vendor`, `react/node_modules`, y PHP/Composer en el PATH. Si faltan, informa "NO EJECUTADO: falta X" y no lo instales sin autorización.
2. Ejecuta primero las verificaciones del área tocada y después la suite completa del proyecto afectado.
3. Para código nuevo de Laravel escribe pruebas en `laravel/tests/Feature` (HTTP, validación, autorización) o `laravel/tests/Unit` (lógica pura). Usa factories y `RefreshDatabase`.
4. Casos mínimos por endpoint o regla: válido, inválido, sin permiso, límite (p. ej. el último hueco disponible) y repetición (misma operación dos veces).
5. Registra la salida resumida de cada comando (número de pruebas, fallos, primer error).

## Reglas y limitaciones
- Nunca declares que una prueba pasa sin haberla ejecutado en esta sesión.
- No desactives, marques como omitidas ni borres pruebas para que la suite pase.
- `--pretend` y bases de datos en memoria sí; migraciones contra MySQL real no, salvo autorización.

## Comprobaciones
- ¿Se ejecutaron los comandos del área afectada?
- ¿Hay pruebas para el comportamiento nuevo?
- ¿Algún fallo preexistente se distingue de los introducidos por el cambio?

## Terminado cuando
Cada verificación tiene estado OK / FALLO / NO EJECUTADO con salida o motivo, y los fallos introducidos por el cambio están corregidos.

## Agente recomendado
`test-security-auditor` (Sonnet) para verificar; `fullstack-developer` (Sonnet) para escribir las pruebas.
