#!/bin/bash
set -euo pipefail

npm install --no-audit --no-fund
npm run build
php artisan migrate --force
php artisan db:seed --class=ProductionOwnerSeeder --force
