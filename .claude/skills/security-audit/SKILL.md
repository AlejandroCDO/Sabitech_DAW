---
name: security-audit
description: Auditoría de seguridad de SABITECH WEB (React, Laravel, MySQL en Docker) basada en OWASP Top 10, sin modificar archivos. Úsala antes de una entrega, tras cambios en autenticación, autorización, endpoints, subida de datos o configuración, y cuando aparezca un posible secreto expuesto.
---

# security-audit

## Objetivo
Identificar riesgos de seguridad con evidencia y gravedad, para que el coordinador encargue las correcciones.

## Cuándo usarla
- Antes de una entrega (dentro de `release-check`).
- Tras cambios en rutas, controladores, autenticación, validación o configuración.
- Al detectar un secreto o dato personal en el repositorio.

## Procedimiento
1. **Secretos**: busca credenciales en archivos versionados (`git ls-files`, Grep por `password`, `secret`, `key`, `token`). Comprueba que `.env` está en `.gitignore` (lo está en `backend/.gitignore` y en el `.gitignore` de la raíz). Ya conocido: `docker-compose.yml` contiene credenciales de desarrollo en claro → recomendar moverlas a variables de entorno antes de cualquier despliegue.
2. **Configuración**: `APP_DEBUG` debe ser `false` fuera de desarrollo; `APP_KEY` generado y no versionado; puerto 3306 expuesto solo en local.
3. **Control de acceso**: rutas que modifican datos con `auth` y policy; comprobación de propiedad del recurso (IDOR).
4. **Validación de entrada**: Form Requests en Laravel, sin SQL concatenado (`DB::raw`, `whereRaw` con variables).
5. **XSS**: `{!! !!}` en Blade y `dangerouslySetInnerHTML` en React con datos de usuario.
6. **CSRF y CORS**: formularios web con `@csrf`; CORS limitado a orígenes conocidos en Laravel.
7. **Asignación masiva**: modelos con `#[Fillable]` explícito.
8. **Abuso**: limitación de peticiones (`throttle`) en login y endpoints sensibles; tokens de un solo uso que no se puedan reutilizar ni adivinar (aplicable si en el futuro se implementan códigos QR).
9. **Datos personales**: mínimo necesario, sin datos personales en logs, contraseñas solo con hash.
10. **Dependencias**: `npm audit` en `frontend/` y `backend/` y `composer audit` en `backend/` (solo lectura; requieren las dependencias instaladas). No ejecutes `npm audit fix`.

## Reglas y limitaciones
- No modificar archivos ni explotar vulnerabilidades contra servicios reales.
- Secretos encontrados: indicar archivo y línea, nunca el valor.
- No marcar como vulnerabilidad lo que no existe aún (p. ej. falta de autenticación en una API que no existe); anotarlo como "requisito de seguridad pendiente".

## Comprobaciones
Cada punto del procedimiento queda como OK / HALLAZGO / NO APLICA / NO VERIFICADO.

## Terminado cuando
Se entrega la tabla de comprobaciones y la lista de hallazgos en formato `sabitech-code-review`, con las correcciones recomendadas para `fullstack-developer`.

## Agente recomendado
`test-security-auditor` (Sonnet). `quick-reviewer` (Haiku) puede hacer un primer barrido de secretos y patrones peligrosos.
