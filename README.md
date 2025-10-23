# Bank Manager API

Application Laravel 10 pour la gestion bancaire avec API REST et documentation Swagger.

## 🚀 Démarrage rapide

### Configuration initiale
```bash
# Cloner le repository
git clone <votre-repo-url>
cd bank-manager

# Setup complet (build + démarrage + migrations)
make setup
```

### Accès aux services
- **Application** : http://localhost:8080
- **API Documentation** : http://localhost:8080/api/documentation
- **pgAdmin** : http://localhost:5050 (admin@admin.com / admin)

## 🛠️ Commandes de développement

### Docker Compose
```bash
# Démarrer tous les services
make up

# Arrêter tous les services
make down

# Voir les logs
make logs

# Accéder au shell du conteneur
make shell
```

### Base de données
```bash
# Exécuter les migrations
make migrate

# Reset complet de la DB
make db-reset

# Se connecter à PostgreSQL
make db-connect
```

### Tests et documentation
```bash
# Générer la documentation Swagger
make swagger

# Lancer les tests
make test

# Tests avec couverture
make test-coverage
```

## 📁 Structure du projet

```
bank-manager/
├── app/                    # Code de l'application Laravel
├── config/                 # Configuration Laravel
├── database/               # Migrations et seeders
├── docker-compose.yml      # Configuration Docker dev
├── Dockerfile             # Image Docker production
├── .env.local             # Variables dev (local)
├── .env                   # Variables prod (Render)
├── Makefile               # Commandes de développement
└── README.md
```

## 🔄 Workflow de développement

### 1. Développement local
```bash
# Démarrer l'environnement
make up

# Coder vos fonctionnalités...
# Tester sur http://localhost:8080

# Générer la doc API
make swagger
```

### 2. Tests et validation
```bash
# Tests unitaires/fonctionnels
make test

# Vérifier la documentation
curl http://localhost:8080/api/documentation
```

### 3. Déploiement en production
```bash
# Commit des changements
git add .
git commit -m "Nouvelle fonctionnalité"

# Push vers GitHub
git push origin main

# Render déploie automatiquement
```

## 🗄️ Base de données

### Développement (local)
- **Host** : postgres (service Docker)
- **Port** : 5432
- **Database** : bankmanager
- **User** : myuser
- **Password** : mypass

### Production (Render)
- **Host** : dpg-d3t2br7gi27c73e2adj0-a.oregon-postgres.render.com
- **Port** : 5432
- **Database** : bankmanager
- **User** : bankmanager_user
- **Password** : [configuré dans Render]

## 📚 API Documentation

La documentation Swagger est automatiquement générée et accessible sur `/api/documentation`.

### Ajouter de la documentation
```php
/**
 * @OA\Get(
 *     path="/api/clients",
 *     summary="Liste des clients",
 *     @OA\Response(response=200, description="Succès")
 * )
 */
public function index() {
    // Votre code
}
```

## 🚀 Déploiement

### Render (recommandé)
1. Créer un Web Service sur Render
2. Connecter votre repo GitHub
3. Sélectionner "Docker" comme runtime
4. Configurer les variables d'environnement
5. Déploiement automatique !

### Variables d'environnement Render
```
APP_NAME=BankManager
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=pgsql
DB_HOST=<votre-host-postgres>
DB_DATABASE=bankmanager
DB_USERNAME=<votre-user>
DB_PASSWORD=<votre-password>
```

## 🧪 Tests

```bash
# Tests unitaires
make test

# Tests avec couverture
make test-coverage

# Tests spécifiques
docker-compose exec app php artisan test --filter=ClientTest
```

## 🛠️ Technologies utilisées

- **Laravel 10** - Framework PHP
- **PostgreSQL 16** - Base de données
- **Docker** - Conteneurisation
- **Swagger/OpenAPI** - Documentation API
- **Tailwind CSS** - Interface utilisateur
- **Render** - Hébergement cloud

## 📝 Scripts disponibles

Voir `Makefile` pour toutes les commandes disponibles :
```bash
make help
```

## 🤝 Contribution

1. Créer une branche pour votre fonctionnalité
2. Tester localement avec Docker
3. Générer la documentation Swagger
4. Commit et push
5. Créer une Pull Request

## 📞 Support

En cas de problème :
1. Vérifier les logs : `make logs`
2. Tester la connectivité DB : `make db-connect`
3. Vérifier la documentation : `make swagger`
