#!/bin/sh
set -eu

if [ "${MIGRATE_RENDER_TO_NEON:-false}" = "true" ]; then
    : "${NEON_DATABASE_URL:?NEON_DATABASE_URL must be configured before migration}"
    : "${DB_URL:?The Render source database URL is required for migration}"

    migrations_table_exists="$(psql "$NEON_DATABASE_URL" -v ON_ERROR_STOP=1 -Atqc \
        "SELECT to_regclass('public.migrations') IS NOT NULL")"
    migration_complete="false"
    if [ "$migrations_table_exists" = "t" ]; then
        migration_complete="$(psql "$NEON_DATABASE_URL" -v ON_ERROR_STOP=1 -Atqc \
            "SELECT EXISTS (SELECT 1 FROM migrations WHERE migration = '__render_to_neon_complete')")"
    fi

    if [ "$migration_complete" != "t" ]; then
        echo "Copying the Render PostgreSQL database to Neon..."
        pg_dump --format=custom --no-owner --no-acl "$DB_URL" \
            | pg_restore --no-owner --no-acl --exit-on-error --single-transaction --dbname="$NEON_DATABASE_URL"
        psql "$NEON_DATABASE_URL" -v ON_ERROR_STOP=1 \
            -c "INSERT INTO migrations (migration, batch) VALUES ('__render_to_neon_complete', 0) ON CONFLICT DO NOTHING"
        echo "Render database copy completed."
    else
        echo "Render database copy already completed; keeping the Neon database."
    fi
fi

php artisan migrate --force
php artisan db:seed --class=ProductionOwnerSeeder --force
exec apache2-foreground
