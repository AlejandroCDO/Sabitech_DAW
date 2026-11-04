---
name: frontend-development
description: Convenciones de frontend de SABITECH WEB: SPA en frontend/ (React 19 + Vite 8, JavaScript/JSX, oxlint) y plantillas Blade de correo en backend/resources/views/emails. Úsala al crear o modificar componentes, páginas, estilos o llamadas a la API desde el cliente.
---

# frontend-development

## Objetivo
Implementar cambios de interfaz coherentes con el stack real y verificables con las herramientas configuradas.

## Cuándo usarla
Cualquier encargo que toque `frontend/src/**`, `frontend/index.html`, `frontend/vite.config.js` o `backend/resources/**`.

## Estado de partida (verificado)
- `frontend/src/App.jsx` es la plantilla de Vite; no hay router, gestor de estado, cliente HTTP ni variables de entorno.
- El proyecto usa **JavaScript (`.jsx`)**, no TypeScript (los `@types/*` solo dan autocompletado).
- La interfaz es la SPA de `frontend/`. Laravel es solo API: se retiraron la vista `welcome`, Vite y Tailwind; Blade queda solo para correos en `backend/resources/views/emails/`.

## Procedimiento
1. Confirma en el encargo qué frontend se toca.
2. React:
   - Componentes funcionales con hooks; un componente por archivo en `frontend/src/`, nombre en PascalCase.
   - Cumple las reglas de `frontend/.oxlintrc.json` (`react/rules-of-hooks` es error).
   - Las URLs del backend van en variables `VITE_*` (`import.meta.env`), nunca codificadas; documenta cualquier variable nueva y no subas archivos `.env*.local`.
   - Toda llamada a la API maneja estados de carga y error.
   - Accesibilidad: etiquetas en formularios, `alt` en imágenes, foco visible.
   - Añadir librerías (router, cliente HTTP, lector QR) es instalar dependencias: requiere autorización.
3. Blade (Laravel, solo correos): plantillas en `backend/resources/views/emails`; escapa la salida con `{{ }}` y evita `{!! !!}` con datos de usuario.
4. Verifica con la skill `testing`.

## Reglas y limitaciones
- La validación del cliente es solo de usabilidad; la autoritativa está en el servidor.
- No guardes tokens ni datos personales en `localStorage` sin que el coordinador haya decidido el mecanismo de autenticación.
- No modifiques `package.json` ni lockfiles sin autorización.

## Comprobaciones
- `cd react && npm run lint` sin errores.
- `cd react && npm run build` termina correctamente.
- Sin URLs, claves ni datos personales codificados.

## Terminado cuando
Los criterios del encargo se cumplen, lint y build pasan (con salida adjunta) o se informa claramente de por qué no se pudieron ejecutar.

## Agente recomendado
`fullstack-developer` (Sonnet).
