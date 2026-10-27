#!/bin/sh
set -eu

php artisan migrate --force
php artisan db:seed --class=ProductionOwnerSeeder --force
exec apache2-foreground
