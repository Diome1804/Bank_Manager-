#!/bin/bash
echo "🔄 Génération Swagger pour production..."
./switch-to-docker.sh
docker compose exec app php artisan l5-swagger:generate
echo "✅ Swagger généré pour production"