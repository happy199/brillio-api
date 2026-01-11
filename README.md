# Brillio API - Backend Laravel

Backend API REST et Dashboard Admin pour la plateforme Brillio.

## Prérequis

- PHP >= 8.2
- Composer >= 2.0
- MySQL >= 8.0
- Extensions PHP : BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

## Installation

### 1. Cloner et installer les dépendances

```bash
cd brillio-api
composer install
```

### 2. Configuration de l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configurer la base de données

Éditer le fichier `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=brillio
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Créer la base de données

```bash
mysql -u root -p -e "CREATE DATABASE brillio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Exécuter les migrations

```bash
php artisan migrate
```

### 6. Charger les données de test

```bash
php artisan db:seed
```

### 7. Créer le lien symbolique pour le stockage

```bash
php artisan storage:link
```

### 8. Lancer le serveur

```bash
php artisan serve
```

L'API est maintenant accessible sur `http://localhost:8000`

## Comptes de test

### Admin
- Email: `admin@brillio.com`
- Mot de passe: `BrillioAdmin2024!`

### Jeunes (10 comptes)
- Email: `aminata.diallo@test.com` (et autres)
- Mot de passe: `password123`

### Mentors (5 comptes)
- Email: `ousmane.sow@mentor.com` (et autres)
- Mot de passe: `password123`

## Configuration des APIs externes

### DeepSeek (Chatbot IA)

```env
DEEPSEEK_API_KEY=your_deepseek_api_key
DEEPSEEK_API_URL=https://api.deepseek.com/v1/chat/completions
DEEPSEEK_MODEL=deepseek-chat
```

> Note : Si l'API n'est pas configurée, le chatbot utilise des réponses de fallback.

### 16Personalities

Le test de personnalité utilise un algorithme interne basé sur MBTI. Aucune API externe n'est requise pour le MVP.

## Structure du projet

```
brillio-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/          # Controllers API mobile
│   │   │   └── Admin/           # Controllers Dashboard
│   │   ├── Middleware/
│   │   └── Requests/            # Form Requests (validation)
│   ├── Models/                   # Modèles Eloquent
│   ├── Services/                 # Logique métier
│   └── Resources/               # API Resources
├── config/
├── database/
│   ├── migrations/              # Migrations BDD
│   ├── seeders/                 # Données de test
│   └── factories/
├── routes/
│   ├── api.php                  # Routes API
│   └── web.php                  # Routes Dashboard
└── resources/views/             # Vues Blade (Dashboard)
```

## Endpoints API

Tous les endpoints sont préfixés par `/api/v1/`

### Authentification

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| POST | `/auth/register` | Inscription | Non |
| POST | `/auth/login` | Connexion | Non |
| POST | `/auth/logout` | Déconnexion | Oui |
| GET | `/auth/user` | Profil utilisateur | Oui |
| PUT | `/auth/profile` | Mise à jour profil | Oui |
| POST | `/auth/upload-photo` | Upload photo profil | Oui |

### Test de personnalité

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/personality/questions` | Questions du test | Oui |
| POST | `/personality/submit` | Soumettre réponses | Oui |
| GET | `/personality/status` | Statut du test | Oui |
| GET | `/personality/result` | Résultat complet | Oui |

### Chatbot IA

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/chat/conversations` | Liste conversations | Oui |
| POST | `/chat/conversations` | Nouvelle conversation | Oui |
| GET | `/chat/conversations/{id}/messages` | Messages | Oui |
| POST | `/chat/send` | Envoyer message | Oui |
| DELETE | `/chat/conversations/{id}` | Supprimer conversation | Oui |

### Mentors

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/mentors` | Liste mentors publiés | Non |
| GET | `/mentors/{id}` | Détail mentor | Non |
| GET | `/mentor/profile` | Mon profil mentor | Oui |
| POST | `/mentor/profile` | Créer/MAJ profil | Oui |
| PUT | `/mentor/publish` | Publier profil | Oui |
| POST | `/mentor/roadmap/step` | Ajouter étape | Oui |
| PUT | `/mentor/roadmap/step/{id}` | Modifier étape | Oui |
| DELETE | `/mentor/roadmap/step/{id}` | Supprimer étape | Oui |

### Documents académiques

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/academic/documents` | Liste documents | Oui |
| POST | `/academic/upload` | Upload document | Oui |
| GET | `/academic/documents/{id}` | Détail document | Oui |
| DELETE | `/academic/documents/{id}` | Supprimer document | Oui |

## Dashboard Admin

Accessible sur `/admin`

- `/admin/login` - Connexion admin
- `/admin/dashboard` - Tableau de bord
- `/admin/users` - Gestion utilisateurs
- `/admin/mentors` - Validation mentors
- `/admin/analytics/personality` - Stats personnalités
- `/admin/analytics/chat` - Stats chatbot
- `/admin/chat-logs` - Logs conversations
- `/admin/documents` - Documents uploadés

## Format des réponses API

### Succès

```json
{
  "success": true,
  "message": "Message de succès",
  "data": { ... }
}
```

### Erreur

```json
{
  "success": false,
  "message": "Message d'erreur",
  "errors": { ... }
}
```

## Tests

```bash
php artisan test
```

## Commandes utiles

```bash
# Rafraîchir la BDD
php artisan migrate:fresh --seed

# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Lister les routes
php artisan route:list --path=api
```

## Sécurité

- Authentification via Laravel Sanctum (tokens Bearer)
- Validation stricte de toutes les entrées
- Upload de fichiers sécurisé (hors dossier public)
- Rate limiting sur les endpoints sensibles
- CORS configuré pour l'app mobile

## License

Propriétaire - Tous droits réservés.
