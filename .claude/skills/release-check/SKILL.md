---
name: release-check
description: Lista de comprobación previa a una entrega de SABITECH WEB (entrega del Proyecto Final DAW o demo). Úsala solo cuando el usuario pida preparar una entrega, una demo o una versión.
---

# release-check

## Objetivo
Confirmar que lo que se entrega funciona, está probado, no expone secretos y está documentado.

## Cuándo usarla
Solo cuando el usuario lo pida (`/release-check`) o el coordinador prepare una entrega. No se activa en revisiones normales.

## Procedimiento
1. **Repositorio**: `git status` limpio o con cambios explicados; sin archivos `.env`, `vendor/`, `node_modules/`, `dist/` ni `public/build` versionados.
2. **Verificación**: ejecuta todo lo de la skill `testing` en los tres proyectos y anota el resultado.
3. **Seguridad**: ejecuta `security-audit`; ningún hallazgo CRÍTICO o ALTO abierto.
4. **Base de datos**: migraciones reversibles y ejecutables desde cero en una base de datos de prueba (solo con autorización); seeders sin datos personales reales.
5. **Configuración**: `.env.example` actualizado con todas las variables nuevas (sin valores reales); `APP_DEBUG=false` documentado para producción.
6. **Documentación**: `README.md` con instrucciones de instalación verificadas; `docs/claude/architecture.md` refleja lo implementado.
7. **Funcionalidades**: lista de lo que funciona, lo parcial y lo pendiente, sin exagerar.

## Reglas y limitaciones
- Solo verifica e informa; no corrige.
- Nada de `git tag`, `git push` ni despliegues sin autorización del usuario.

## Comprobaciones
Tabla con los 7 puntos: OK / HALLAZGO / NO VERIFICADO.

## Terminado cuando
El coordinador recibe la tabla y la lista de bloqueos. La entrega solo es apta si no hay bloqueos CRÍTICOS/ALTOS y las verificaciones se ejecutaron de verdad.

## Agente recomendado
`test-security-auditor` (Sonnet), coordinado por `project-manager` (Opus).
