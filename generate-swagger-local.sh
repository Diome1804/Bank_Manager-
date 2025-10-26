#!/bin/bash
echo "🔄 Génération Swagger pour développement local..."
./switch-to-local.sh
php artisan l5-swagger:generate
echo "✅ Swagger généré pour http://localhost:8080/api/documentation"