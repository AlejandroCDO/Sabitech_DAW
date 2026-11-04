---
name: sabitech-code-review
description: Revisión de código de SABITECH WEB (React, Laravel) sin modificar archivos, con informe normalizado de hallazgos por gravedad. Úsala al revisar un cambio, un área del código, un mensaje de error o un log.
---

# sabitech-code-review

> Se llama `sabitech-code-review` y no `code-review` para no sustituir el comando integrado `/code-review` de Claude Code.

## Objetivo
Detectar errores, malas prácticas, inconsistencias y riesgos, y entregarlos al coordinador con evidencia suficiente para decidir.

## Cuándo usarla
- Antes de planificar un cambio en un área (para conocer su estado).
- Después de que `fullstack-developer` entregue un cambio (revisión independiente).
- Al analizar un error, una traza o un log.

## Procedimiento
1. Delimita el alcance: lista exacta de archivos o carpeta.
2. Lee los archivos completos implicados y sus dependencias directas (imports, rutas, modelos, migraciones).
3. Revisa por área:
   - **React (`frontend/src`)**: reglas de hooks, claves en listas, estado derivado innecesario, efectos sin limpieza, URLs o secretos codificados, `dangerouslySetInnerHTML`.
   - **Laravel (`backend/`)**: validación con Form Requests o `$request->validate`, asignación masiva (atributo `#[Fillable]` del modelo), autorización (policies/gates), consultas N+1, rutas sin middleware `auth`, coherencia migración ↔ modelo ↔ factory.
   - **Configuración**: coherencia entre `docker-compose.yml`, `backend/.env.example` y `backend/config/database.php`.
4. Para un error o log: localiza la primera línea relevante de la traza, el archivo y la línea, y relaciónalos con el código.
5. Si dudas sobre el comportamiento de una API del framework, aplica `documentation-research`.
6. Redacta los hallazgos con el formato siguiente.

## Formato de hallazgo
```text
[GRAVEDAD] Título breve
Archivo: ruta/relativa:línea
Evidencia: fragmento literal, salida o enlace
Impacto: qué falla y cuándo
Propuesta: corrección sugerida (no aplicada)
Estado: verificado | no verificado
```
Gravedad: `CRÍTICA` (seguridad explotable, pérdida o fuga de datos), `ALTA` (funcionalidad rota), `MEDIA` (fallo en casos concretos o mala práctica con riesgo), `BAJA` (estilo, legibilidad).

## Reglas y limitaciones
- No modificar archivos.
- No informar como error la ausencia de funcionalidades que aún no existen (reservas, QR, autenticación propia): el proyecto está en fase de plantilla.
- No copiar secretos en el informe; si encuentras uno, indica archivo y línea y escribe `[REDACTADO]`.
- Archivos generados o de terceros (`vendor/`, `node_modules/`, `composer.lock`, `package-lock.json`) no se revisan salvo petición expresa.

## Comprobaciones
- Cada hallazgo tiene archivo y evidencia.
- No hay hallazgos duplicados.
- Se separa lo verificado de lo supuesto.

## Terminado cuando
El informe cubre todo el alcance pedido, está ordenado por gravedad e incluye una recomendación de qué delegar.

## Agente recomendado
`quick-reviewer` (Haiku). También la usa `test-security-auditor` para formatear hallazgos.
