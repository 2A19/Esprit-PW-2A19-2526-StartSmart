# StartSmartIntegrated

This folder is the corrected integration.

Important: the original `startsmart` frontend is the base of the project. Its UI files were not replaced:

- `index.php`
- `login.php`
- `views/`
- `controllers/`
- `models/`
- `services/`
- `style.css`, `forum.css`, `projet.css`, JavaScript, images

StartSmartrh was added as RH/backend modules inside the same project folder:

- `controllers/rh/`
- `models/rh/`
- `views/rh/`
- `core/rh/`
- `rh.php`

## URLs

Frontend home:
`http://127.0.0.1:8090/index.php`

Single shared login:
`http://127.0.0.1:8090/login.php`

RH module entry:
`http://127.0.0.1:8090/rh.php?page=frontend/home`

Startup/RH dashboard:
`http://127.0.0.1:8090/rh.php?page=backend/dashboard`

## Database

Use one database, `startsmart_db`, and import the SQL files from `database/`.

## What changed

- Preserved the frontend app as the main app.
- Added the StartSmartrh backend/RH MVC files under RH-specific folders to avoid controller/model name conflicts.
- Added `rh.php` as a module router inside the same project, not a separate app.
- Added `config/session.php` so the frontend and RH backend use the same PHP session name and session data.
- Kept StartSmartrh authentication as the login authority by routing the JSON login endpoint through the existing RH user authentication controller method.
- Added role-based login redirects:
  - `admin` or `rh` users go to `rh.php?page=backend/dashboard`.
  - all other authenticated users go to the frontend app at `index.php`.
- Added an `RH` navbar link to the original frontend layout.
- Added `getPDO()` compatibility to the existing frontend database class so StartSmartrh controllers can use the same DB connection.

## Bridge rules

- Do not add a second authentication query in frontend code.
- Frontend pages check `$_SESSION['user_id']` from the shared session.
- Frontend forms that need RH/backend behavior should submit to `rh.php?page=...&action=...`.
- `rh.php` maps those bridge URLs to the original RH controllers; the controllers keep their existing business logic and SQL.
