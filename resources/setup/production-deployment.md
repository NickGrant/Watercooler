# Production Deployment

Watercooler uses the shared CTRL Studio PHP + Angular FTP deployment workflow from `NickGrant/ctrl-game-workflows`.

Rock Paper Tactics is the reference consumer for this deployment model. Watercooler keeps one important application-specific difference: its Angular client uses same-origin relative `/api/...` URLs, so the frontend production build does not compile a separate API base URL.

## Trigger behavior

`.github/workflows/deploy-production.yml` supports:

- automatic deployments after relevant changes reach `master`
- manual deployments through **Actions → Deploy Production**
- component selection: `frontend`, `backend`, or `both`
- manual FTP dry runs, defaulting to `true`

Automatic `master` deployments are real deployments. Manual runs should use dry-run mode until production configuration has been validated.

## Production environment variables

Create a protected GitHub environment named `production` and define:

- `FTP_SERVER`
- `FTP_PROTOCOL`
- `FTP_PORT`
- `FRONTEND_SERVER_DIR`
- `BACKEND_SERVER_DIR`
- `PRODUCTION_FRONTEND_URL`
- `PRODUCTION_API_URL`

Both server-directory values must end in `/`.

For the existing shared-hosting layout, `BACKEND_SERVER_DIR` should point at the remote directory corresponding to the deployable contents of `backend/api/`, because the shared workflow packages that directory's contents directly.

Watercooler uses relative browser API URLs. `PRODUCTION_API_URL` is still required by the shared workflow for backend HTTP health verification; it is not compiled into the Angular bundle.

If frontend and API are exposed through the same origin, `PRODUCTION_FRONTEND_URL` and `PRODUCTION_API_URL` may resolve to the same public host. Keep them as separate variables so the deployment contract remains explicit.

## Repository-level secrets

Reusable workflows cannot receive caller environment-scoped secrets.

Store these under **Settings → Secrets and variables → Actions → Repository secrets**:

- `FRONTEND_FTP_USERNAME`
- `FRONTEND_FTP_PASSWORD`
- `BACKEND_FTP_USERNAME`
- `BACKEND_FTP_PASSWORD`
- `CTRL_GAME_API_GITHUB_TOKEN`

The FTP values are explicitly forwarded to the reusable workflow. `CTRL_GAME_API_GITHUB_TOKEN` must have read access to the private `NickGrant/ctrl-game-api-core` repository.

## Runtime `.env`

The backend deployment artifact intentionally excludes `.env` and `.env.*`.

The production server's existing `backend/api/.env` remains server-managed and must contain the application's runtime values, including:

- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`

Watercooler is same-origin by default and therefore does not require CORS configuration for its normal production topology. If the frontend and API are later split across origins, add:

```dotenv
CORS_ALLOWED_ORIGINS=https://your-frontend.example.com
```

Do not commit or deploy the production `.env`.

## Database migrations

Production migrations are never run automatically.

The workflow detects changes beneath `database/migrations/` and emits a warning. Apply required schema changes separately before or alongside the application deployment using the normal production database procedure.

## First validation

After this workflow is merged to `master`:

1. Open **Actions → Deploy Production**.
2. Run from `master`.
3. Leave `deployment_ref` as `master`.
4. Select `component: both`.
5. Leave `dry_run: true`.
6. Confirm:
   - private Composer authentication succeeds
   - backend tests and lint pass
   - backend production packaging succeeds
   - frontend tests and production build pass
   - production environment variables resolve
   - backend FTPS authentication succeeds
   - frontend FTPS authentication succeeds
   - both remote directories are correct
   - the proposed FTP plan contains no unexpected deletions

Do not weaken the protected production environment merely to validate a PR branch.

## First real deployment

After a green dry run, run the same workflow with `dry_run: false` or allow the next relevant merge to `master` to trigger the normal automatic deployment.

Verify:

- the backend `/health` endpoint succeeds
- the frontend root loads
- creating and joining a game works
- polling continues to use same-origin `/api/...` requests
- the server-managed `.env` remains intact
- the stale-game purge cron still resolves the deployed backend path
- no database migration was executed automatically

The first deployment through FTP-Deploy-Action may upload most or all files because no shared workflow sync-state file exists yet. Subsequent deployments should become incremental.
