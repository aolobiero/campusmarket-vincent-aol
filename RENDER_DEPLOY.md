# Deploy CampusMarket on Render

The repo includes a Render Blueprint (`render.yaml`) that builds the app from its Dockerfile. It uses the free web-service plan by default and listens on the port provided by Render.

## Before creating the service

1. Create an externally reachable MySQL database. Render Blueprints can provision Render Postgres, but this app currently uses MySQL (`pdo_mysql`), so use a MySQL-compatible database provider or migrate the app to PostgreSQL first.
2. Import `database.sql` into that database. It creates the `campusmarket` schema and seeds the sample marketplace listings.
3. Confirm the database provider allows connections from Render and note its host, database, username, password, and port. Do not put credentials in this repository.

## Create the Render service

1. Push the repository to GitHub.
2. In the Render Dashboard, choose **New > Blueprint** and connect this repository and the branch to deploy.
3. Render reads `render.yaml`, builds `Dockerfile`, and prompts for the credentials marked `sync: false`.
4. Set `DB_HOST`, `DB_USER`, and `DB_PASSWORD` to the MySQL credentials. `DB_NAME` defaults to `campusmarket`; `DB_PORT` defaults to `3306`.
5. Add the Africa's Talking credentials in the service environment settings: `AT_USERNAME`, `AT_API_KEY`, and, if configured for the account, `AT_SENDER_ID`. SMS delivery must be configured for phone verification in production. Production mode intentionally does not display verification codes when SMS delivery fails.
6. Deploy and open the generated `https://<service-name>.onrender.com` address. The Blueprint health check requests `/index.php`.

## Uploaded listing photos

The free service uses ephemeral storage. Uploaded files in `uploads/` can be lost when Render restarts or redeploys the service. For persistent local uploads, upgrade the web service to a paid plan and attach a persistent disk mounted at `/var/www/html/uploads` (1 GB is enough to start). The container entrypoint makes the mount writable by Apache. Render disks are tied to one instance and prevent scaling that service to multiple instances.

For multi-instance hosting or a free service, use external object storage instead of writing uploads to the local filesystem.

## Verify after deployment

- Open the site and confirm the seeded listings load. If the page reports a database connection issue, check `DB_*` values and the database provider's network access rules.
- Request a phone verification code and confirm it arrives before relying on signup or selling.
- Create a test listing and verify its image remains available after a redeploy if persistent storage is enabled.
- Check the Render deploy logs for Docker build, Apache startup, and PHP errors.

Never commit `.env` files, database passwords, or SMS API keys. `.dockerignore` excludes `.env` and uploaded files from the image build context.
