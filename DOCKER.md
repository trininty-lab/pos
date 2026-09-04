# Docker Operations

This repository uses Docker Compose for local development and controlled self-hosted deployments. The application is split into PHP-FPM (`app`), unprivileged Nginx (`web`), MySQL (`database`), Redis (`redis`), a queue worker, and a scheduler. The WhatsApp gateway is optional and enabled with the `whatsapp` profile.

## Prerequisites

- Docker Engine 24+ with the Compose v2 plugin
- At least 4 GB available memory for the default stack; the optional WhatsApp service needs more for Chromium

No PHP, Composer, Node, MySQL, or Redis installation is required on the host.

## Local development

```powershell
Copy-Item .env.example .env
# Generate a key inside the development container and write the displayed value to APP_KEY in .env.
docker compose -f compose.yaml -f compose.dev.yaml run --rm app php artisan key:generate --show
docker compose -f compose.yaml -f compose.dev.yaml up -d --build

docker compose -f compose.yaml -f compose.dev.yaml exec app php artisan migrate --seed
docker compose -f compose.yaml -f compose.dev.yaml exec app php artisan storage:link
docker compose -f compose.yaml -f compose.dev.yaml ps
```

Open `http://localhost:8080`. Vite HMR is available at `http://localhost:5173`.

To include WhatsApp, add `--profile whatsapp` to the `up` command and keep `WA_SERVICE_URL=http://whatsapp:3001` in `.env`.

## Common commands

```powershell
# Rebuild only application images and remove their old containers/tags.
# Requires DB_PASSWORD and DB_ROOT_PASSWORD in .env.
.\docker\scripts\rebuild-app.ps1

# View logs
 docker compose -f compose.yaml -f compose.dev.yaml logs -f

# Enter the application container
 docker compose -f compose.yaml -f compose.dev.yaml exec app sh

# Run migrations
 docker compose -f compose.yaml -f compose.dev.yaml exec app php artisan migrate

# Run tests (the test suite uses in-memory SQLite from phpunit.xml)
 docker compose -f compose.yaml -f compose.dev.yaml exec app php artisan test

# Stop the environment, retaining named volumes
 docker compose -f compose.yaml -f compose.dev.yaml down

# Rebuild from scratch
 docker compose -f compose.yaml -f compose.dev.yaml build --no-cache
 docker compose -f compose.yaml -f compose.dev.yaml up -d

# Remove the environment and all local data
 docker compose -f compose.yaml -f compose.dev.yaml down -v
```

## Production deployment

Build and tag immutable images in CI using the commit SHA. Push both the PHP and Nginx images to a registry, then deploy the exact tags on the server:

```powershell
$env:APP_IMAGE = "registry.example.com/point-of-sales:git-<commit-sha>"
$env:WEB_IMAGE = "registry.example.com/point-of-sales-web:git-<commit-sha>"
docker compose -f compose.yaml -f compose.prod.yaml pull
docker compose -f compose.yaml -f compose.prod.yaml up -d database redis
docker compose -f compose.yaml -f compose.prod.yaml run --rm app php artisan migrate --force
docker compose -f compose.yaml -f compose.prod.yaml run --rm app /usr/local/bin/deploy.sh
docker compose -f compose.yaml -f compose.prod.yaml up -d app web queue scheduler
curl --fail http://127.0.0.1:8080/health
```

The migration command is explicit and fails the deployment if migrations fail. Do not put `migrate --force` in a long-running service command. For rollback, redeploy the previous known-good image tags and run only the migrations that are compatible with that version.

For a real TLS deployment, put a managed load balancer or a dedicated reverse proxy in front of `web`. The Compose Nginx service is HTTP-only and binds only the application port. MySQL and Redis have no host port published and are reachable only on the internal backend network.

## Persistence and backups

Named volumes are used for:

- `db-data`: MySQL data at `/var/lib/mysql`
- `redis-data`: Redis append-only data at `/data`
- `app-storage`: Laravel uploaded files at `storage/app`
- `whatsapp-session`: the optional WhatsApp login session
- framework, logs, and bootstrap cache volumes for writable runtime state

Volumes survive `docker compose down` but are not backups. Back up MySQL at least daily, retain 7 daily and 4 weekly copies in storage outside the host, and back up `app-storage` whenever uploads change. A practical initial target is RPO 24 hours and RTO 4 hours; tighten these with managed database/object storage as business requirements grow.

Example backup and restore commands:

```powershell
# Backup
Get-Date -Format yyyyMMdd | ForEach-Object { docker compose exec -T database sh -c 'mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' | Out-File -Encoding ascii "backup-$_.sql" }

# Restore into a running database
Get-Content .\backup-YYYYMMDD.sql | docker compose exec -T database sh -c 'mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'
```

Store backup files in encrypted, access-controlled external storage. Test restoration regularly.

## Logs and health

All Laravel logs use stderr in the container and are collected by Docker or the hosting platform. Nginx access/error logs, worker output, scheduler output, MySQL logs, and Redis logs are available with `docker compose logs`. `/health` returns `200` only when Laravel can connect to both MySQL and Redis; it returns `503` without diagnostic details otherwise.

The worker health check verifies the queue process and Redis connection. The database and Redis health checks verify their actual service protocols. Container metrics, error tracking, and alerting should be supplied by the deployment platform rather than adding an unnecessary monitoring stack here.

## Security notes

- `.env`, production credentials, keys, and tokens are ignored and never copied into the image.
- PHP-FPM runs as the non-root `app` user; Nginx uses the unprivileged image.
- The backend network is internal, and database/Redis ports are not published.
- Production dependencies are installed from lock files in a multi-stage build; Composer, npm, and build packages are absent from the PHP runtime image.
- Scan the exact pushed image in CI with a tool such as Trivy or Docker Scout before deployment.
- Pin image digests in the registry promotion step for a fully reproducible supply chain.

## Troubleshooting

- `503 /health`: inspect `docker compose logs app web database redis`; confirm `.env` credentials match the MySQL container on a fresh volume.
- Missing product images: run `php artisan storage:link` and confirm the `app-storage` volume is mounted.
- Stale permission data after seeding: log out and log back in.
- Vite assets not updating: inspect the `frontend` service and port `5173`; rebuild if dependencies changed.
- WhatsApp QR missing: start with `--profile whatsapp`, inspect its logs, then open the WhatsApp settings screen. The session is stored in `whatsapp-session`.
- A clean database reset is destructive: use `docker compose down -v` only for local development.
