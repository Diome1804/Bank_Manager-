#!/bin/bash
echo "🧹 Nettoyage complet du cache Laravel..."

# Vider tous les caches Laravel
echo "📋 Vidage du cache..."
php artisan cache:clear

echo "⚙️ Vidage de la configuration..."
php artisan config:clear

echo "🛣️ Vidage des routes..."
php artisan route:clear

echo "👁️ Vidage des vues..."
php artisan view:clear

echo "🎯 Vidage des routes compilées..."
php artisan route:cache

echo "✅ Cache nettoyé avec succès !"
echo ""
echo "📝 Prochaines étapes recommandées :"
echo "1. Régénérez la documentation Swagger : php artisan l5-swagger:generate"
echo "2. Redémarrez votre serveur si nécessaire"