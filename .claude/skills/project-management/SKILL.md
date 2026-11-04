---
name: project-management
description: Planificación y coordinación de tareas en SABITECH WEB. Úsala al recibir una petición nueva, al dividirla en encargos para quick-reviewer, fullstack-developer y test-security-auditor, al priorizar hallazgos y al decidir si un trabajo está terminado.
---

# project-management

## Objetivo
Convertir cada petición del usuario en encargos pequeños, asignados al agente adecuado, sin solapamiento de archivos y con un criterio de aceptación verificable.

## Cuándo usarla
- Al empezar cualquier tarea que toque más de un archivo o más de una carpeta (`frontend/`, `backend/`).
- Al recibir informes de los subagentes.
- Antes de comunicar al usuario que algo está terminado.

## Procedimiento
1. **Entender**: reformula el objetivo en una frase. Comprueba en el código si lo que la petición da por existente existe (consulta `docs/claude/architecture.md`).
2. **Detectar decisiones abiertas**: si la tarea depende de algo no definido (qué backend consume React, modelo de reservas, formato del QR, roles, base de datos MySQL o SQLite), pregunta al usuario antes de planificar.
3. **Dividir** en encargos con esta plantilla:
   ```text
   Agente: <quick-reviewer | fullstack-developer | test-security-auditor>
   Objetivo: ...
   Contexto: ... (lo que el subagente necesita saber; no ve tu conversación)
   Archivos que puede modificar: ... (vacío para agentes de solo lectura)
   No tocar: ...
   Criterios de aceptación: ...
   Verificación esperada: <comandos de la skill testing>
   ```
4. **Asignar archivos**: cada archivo editable pertenece a un solo encargo activo. Las revisiones de solo lectura pueden ir en paralelo; las ediciones sobre los mismos archivos van en serie.
5. **Ejecutar el flujo** de `docs/claude/workflow.md`: análisis (Haiku) → plan → implementación (Sonnet) → verificación (auditor) → revisión independiente opcional (Haiku) → aceptación.
6. **Priorizar hallazgos**: CRÍTICA y ALTA se corrigen antes de seguir; MEDIA se planifica; BAJA se anota.
7. **Revisar el resultado**: lee el diff (`git diff`), compara con los criterios de aceptación y comprueba que la verificación tiene salida real.

## Reglas y limitaciones
- Los subagentes no se hablan entre sí; tú reenvías la información.
- `Agent(...)` restringido solo funciona si esta sesión se inició con `claude --agent project-manager`.
- No delegues tareas triviales (leer un archivo, responder una pregunta corta).
- Las acciones destructivas o irreversibles, la instalación de dependencias y las migraciones requieren autorización del usuario.

## Comprobaciones
- ¿Cada encargo tiene archivos, alcance y criterio de aceptación?
- ¿Hay dos encargos activos que editen el mismo archivo?
- ¿Algún requisito se ha supuesto sin confirmarlo?
- ¿La verificación declarada se ejecutó de verdad?

## Terminado cuando
- Todos los criterios de aceptación se cumplen con evidencia.
- No quedan hallazgos CRÍTICOS ni ALTOS abiertos en el alcance.
- El usuario ha recibido el resumen final: cambios, verificación, pendientes y decisiones.

## Agente recomendado
`project-manager` (Opus).
