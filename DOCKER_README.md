# Configuration Docker - Symfony avec MariaDB et phpMyAdmin

## Services configurés

### 1. **MariaDB** (Base de données)
- Image: `mariadb:11.2`
- Port: `3306`
- Credentials par défaut:
  - Root password: `root`
  - Database: `app`
  - User: `app`
  - Password: `!ChangeMe!`

### 2. **phpMyAdmin** (Interface de gestion de base de données)
- Image: `phpmyadmin:latest`
- URL d'accès: http://localhost:8080
- Connexion automatique configurée

### 3. **Mailpit** (Serveur mail de développement)
- Ports: 1025 (SMTP), 8025 (Interface web)

## Commandes Docker

### Démarrer les services
```bash
docker compose up -d
```

### Arrêter les services
```bash
docker compose down
```

### Voir les logs
```bash
docker compose logs -f
```

### Reconstruire les services
```bash
docker compose up -d --build
```

### Supprimer tout (y compris les volumes)
```bash
docker compose down -v
```

## Accès aux services

- **phpMyAdmin**: http://localhost:8080
  - Serveur: `database`
  - Utilisateur: `app`
  - Mot de passe: `!ChangeMe!`

- **Mailpit**: http://localhost:8025

## Configuration Symfony

La DATABASE_URL est déjà configurée dans `.env`:
```
DATABASE_URL="mysql://app:!ChangeMe!@database:3306/app?serverVersion=11.2-MariaDB&charset=utf8mb4"
```

## Migrations

Après avoir démarré les services, exécutez les migrations:

```bash
php bin/console doctrine:migrations:migrate
```

Ou si vous voulez créer une nouvelle migration:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## Dépannage

### La base de données ne se connecte pas
1. Vérifiez que les services sont en cours d'exécution: `docker compose ps`
2. Vérifiez les logs: `docker compose logs database`
3. Attendez que le healthcheck soit passé (60 secondes max)

### Réinitialiser la base de données
```bash
docker compose down -v
docker compose up -d
php bin/console doctrine:migrations:migrate
```

### phpMyAdmin ne charge pas
1. Attendez que le service `database` soit healthy
2. Vérifiez: `docker compose ps`
3. Le service phpMyAdmin attend que MariaDB soit prêt grâce à `depends_on` avec condition de santé
