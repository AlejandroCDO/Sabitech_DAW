# SABITECH – Frontend (React 19 + Vite 8)

SPA en JavaScript/JSX que consume la API de Laravel (`backend/app/Api`).

## Arquitectura: organización por funcionalidad (feature-based)

```
src/
├─ app/          App.jsx, providers/ (contextos globales), router/ (rutas y guards por rol)
├─ features/     un módulo por área funcional del backend
│  ├─ auth/        login, sesión          ↔ Api/Controllers/Auth
│  ├─ charlas/     catálogo y detalle     ↔ Api/Controllers/Public
│  ├─ reservas/    reservar, QR, correo   ↔ Api/Controllers/Asistente
│  ├─ acceso/      validación QR/manual   ↔ Api/Controllers/Acceso
│  └─ admin/       charlas, ponentes, ubicaciones, reservas, asistencias ↔ Api/Controllers/Admin
├─ shared/       código reutilizable: components/{ui,feedback}, layouts, hooks, utils, constants
├─ lib/          infraestructura: cliente HTTP, almacenamiento de sesión, formateo de fechas
├─ styles/       tokens (variables CSS), reset y estilos globales
├─ assets/       images/, icons/
└─ test/         configuración de pruebas
```

Cada feature contiene `api/` (llamadas HTTP), `hooks/`, `components/` y `pages/`.

## Convenciones

- Alias `@` → `src/` (`import X from '@/shared/...'`).
- Una feature no importa de otra feature; lo común va a `shared/`.
- Componentes en PascalCase, hooks `useXxx`, carpetas en minúsculas.
- Perfiles: público, asistente, personal de acceso y administrador (rutas protegidas por rol).

## Ejecución

```bash
cp .env.example .env   # VITE_API_URL
npm install
npm run dev
npm run lint
npm run build
```
