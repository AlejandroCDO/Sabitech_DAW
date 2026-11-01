---
name: documentation-research
description: Consulta de documentación oficial y fuentes fiables para las versiones reales de SABITECH WEB (React 19, Vite 8, Laravel 13, PHPUnit 12, MySQL 8). Úsala cuando haya dudas sobre una API, una configuración o un mensaje de error.
---

# documentation-research

## Objetivo
Responder dudas técnicas con fuentes oficiales y de la versión que usa el proyecto, citando el enlace.

## Cuándo usarla
- Antes de usar una API cuya firma o comportamiento no esté claro.
- Al interpretar un mensaje de error desconocido.
- Cuando un hallazgo de revisión depende del comportamiento del framework.

## Procedimiento
1. Obtén la versión real del archivo de dependencias: `react/package.json`, `laravel/composer.json` (o el lockfile si necesitas la versión exacta).
2. Consulta primero la fuente oficial:
   - React: https://react.dev
   - Vite: https://vite.dev
   - Laravel: https://laravel.com/docs/13.x
   - PHPUnit: https://docs.phpunit.de
   - Tailwind CSS: https://tailwindcss.com/docs
   - MySQL 8.0: https://dev.mysql.com/doc/refman/8.0/en/
   - oxlint: https://oxc.rs/docs/guide/usage/linter
   - Seguridad: https://owasp.org/www-project-top-ten/ y https://cheatsheetseries.owasp.org
3. Si la fuente oficial no lo resuelve, usa el repositorio oficial del proyecto (issues, changelog). Evita blogs sin fecha o de versiones antiguas.
4. Resume la respuesta en pocas líneas con el enlace exacto y la versión a la que aplica.

## Reglas y limitaciones
- Indica siempre la versión de la documentación consultada.
- Si la documentación contradice el código del proyecto, informa de la contradicción; no cambies el código.
- No envíes código del proyecto, secretos ni datos personales a servicios externos en las búsquedas.

## Comprobaciones
- ¿La fuente es oficial y de la versión correcta?
- ¿La respuesta incluye enlace?

## Terminado cuando
La duda está resuelta con cita verificable o queda marcada como "no resuelta" con lo consultado.

## Agente recomendado
`quick-reviewer` (Haiku). Cualquier agente con acceso web puede usarla.
