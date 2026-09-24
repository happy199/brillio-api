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

## Endpoints API (v2 - API First)

L'ensemble des fonctionnalités mobiles et web pour les espaces **Jeune** et **Mentor** sont exposées sous le préfixe `/api/v2/`.
Une documentation interactive Swagger/OpenAPI est disponible sur `http://localhost:8000/api/documentation`.

### 1. Authentification Email & Réseaux Sociaux

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `POST` | `/api/v2/register` | Inscription email / mot de passe | Non |
| `POST` | `/api/v2/login` | Connexion email / mot de passe | Non |
| `POST` | `/api/logout` | Déconnexion (révocation token) | Oui |
| `GET` | `/api/v2/auth/social/{provider}/url` | Obtenir l'URL de redirection OAuth (`google` ou `linkedin`) | Non |
| `POST` | `/api/v2/auth/social/token` | Connexion / Inscription via OAuth token (`google` jeune, `linkedin` mentor) | Non |
| `POST` | `/api/v2/password/email` | Demande de réinitialisation de mot de passe | Non |
| `POST` | `/api/v2/password/reset` | Réinitialisation du mot de passe | Non |

### 2. Profil & Onboarding

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/v2/user` | Profil de l'utilisateur connecté | Oui |
| `POST` | `/api/v2/user/profile` | Mise à jour du profil | Oui |
| `POST` | `/api/v2/user/photo` | Upload photo de profil | Oui |
| `DELETE`| `/api/v2/user/photo` | Supprimer photo de profil | Oui |
| `GET` | `/api/v2/onboarding` | Référentiels d'onboarding (pays, niveaux d'études, situations) | Oui |
| `POST` | `/api/v2/onboarding/complete` | Valider et finaliser l'onboarding | Oui |
| `PUT` | `/api/v2/account/password` | Modifier mot de passe | Oui |
| `POST` | `/api/v2/account/archive` | Archiver / désactiver son compte | Oui |

### 3. Test de Personnalité (MBTI) & Historique

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/v2/personality/questions` | Questions MBTI statiques | Oui |
| `GET` | `/api/v2/personality/questions/dynamic` | Questions MBTI contextualisées par IA selon la situation | Oui |
| `POST` | `/api/v2/personality/submit` | Soumettre les réponses au test | Oui |
| `GET` | `/api/v2/personality/status` | Statut du test (complété ou non) | Oui |
| `GET` | `/api/v2/personality/result/{userId?}` | Résultat du test (type, traits, description) | Oui |
| `GET` | `/api/v2/personality/history` | Historique de tous les tests passés | Oui |
| `GET` | `/api/v2/personality/history/{id}` | Détail d'un test historique spécifique (scores, métiers, secteurs) | Oui |

### 4. Analyse de CV par IA (Espace Jeune)

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/v2/cv` | Liste des analyses de CV de l'utilisateur | Oui |
| `POST` | `/api/v2/cv/analyze` | Upload (PDF, Word, Image) et analyse IA du CV (Scoring ATS) | Oui |
| `GET` | `/api/v2/cv/{id}` | Détail complet de l'analyse, forces, faiblesses, structure ATS | Oui |
| `POST` | `/api/v2/cv/{id}/reanalyze` | Relancer l'analyse IA sans re-téléverser | Oui |
| `POST` | `/api/v2/cv/{id}/placeholder` | Remplir une balise guidée `[guide:...]` | Oui |
| `POST` | `/api/v2/cv/{id}/action` | Copier le texte ou télécharger en DOCX/PDF selon template | Oui |
| `DELETE`| `/api/v2/cv/{id}` | Supprimer une analyse de CV | Oui |

### 5. Mentors, Roadmap & Profil

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/v2/mentors` | Liste des mentors publiés (avec filtres spécialisation/pays) | Oui |
| `GET` | `/api/v2/mentors/specializations` | Liste des spécialisations & domaines | Oui |
| `GET` | `/api/v2/mentors/{id}` | Détail public d'un mentor et de son parcours | Oui |
| `GET` | `/api/v2/mentor/profile` | Profil mentor de l'utilisateur connecté | Oui (Mentor) |
| `POST` | `/api/v2/mentor/profile` | Créer ou mettre à jour son profil mentor | Oui (Mentor) |
| `POST` | `/api/v2/mentor/profile/import-linkedin` | Importer profil et parcours via PDF export LinkedIn | Oui (Mentor) |
| `PUT` | `/api/v2/mentor/publish` | Publier ou dépublier son profil mentor | Oui (Mentor) |
| `POST` | `/api/v2/mentor/roadmap/step` | Ajouter une étape de parcours (expérience/formation) | Oui (Mentor) |
| `PUT` | `/api/v2/mentor/roadmap/step/{id}` | Modifier une étape de parcours | Oui (Mentor) |
| `DELETE`| `/api/v2/mentor/roadmap/step/{id}` | Supprimer une étape de parcours | Oui (Mentor) |
| `POST` | `/api/v2/mentor/roadmap/reorder` | Réordonner les étapes du parcours | Oui (Mentor) |

### 6. Mentorats & Séances

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/v2/mentorships` | Liste des relations de mentorat | Oui |
| `POST` | `/api/v2/mentorships` | Demander un mentorat | Oui (Jeune) |
| `POST` | `/api/v2/mentorships/{id}/accept` | Accepter une demande de mentorat | Oui (Mentor) |
| `POST` | `/api/v2/mentorships/{id}/refuse` | Refuser une demande de mentorat | Oui (Mentor) |
| `POST` | `/api/v2/mentorships/{id}/disconnect` | Rompre une relation de mentorat | Oui |
| `GET` | `/api/v2/sessions` | Liste des séances de mentorat | Oui |
| `POST` | `/api/v2/sessions` | Réserver une séance de mentorat | Oui (Jeune) |
| `POST` | `/api/v2/sessions/{id}/accept` | Confirmer une séance | Oui (Mentor) |
| `POST` | `/api/v2/sessions/{id}/refuse` | Refuser une séance | Oui (Mentor) |
| `POST` | `/api/v2/sessions/{id}/pay` | Payer une séance avec les crédits du portefeuille | Oui (Jeune) |
| `GET` | `/api/v2/sessions/{id}/meeting` | Obtenir le JWT et les URLs Jitsi 8x8 JaaS pour la visio | Oui |
| `PUT` | `/api/v2/sessions/{id}/report` | Soumettre le compte rendu de séance | Oui (Mentor) |
| `POST` | `/api/v2/sessions/{id}/prefill-report` | Pré-remplir le rapport via l'IA depuis la transcription | Oui (Mentor) |
| `GET` | `/api/v2/sessions/{id}/download-report` | Télécharger le compte rendu PDF | Oui |
| `GET` | `/api/v2/sessions/{id}/download-transcription` | Télécharger la transcription PDF | Oui |
| `POST` | `/api/v2/sessions/compiled-reports` | Télécharger les rapports compilés PDF | Oui |

### 7. Portefeuille, Tarification & Monétisation

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/v2/wallet` | Solde de crédits, historique et coûts dynamiques des fonctionnalités | Oui |
| `GET` | `/api/v2/wallet/packs` | Packs de crédits disponibles à l'achat | Oui |
| `GET` | `/api/v2/wallet/pricing` | Grille complète des tarifs en crédits et prix unitaire | Oui |
| `POST` | `/api/v2/wallet/purchase` | Initialiser un achat de crédits (Checkout Moneroo WebView) | Oui |
| `POST` | `/api/v2/wallet/redeem` | Utiliser un coupon promotionnel | Oui |

### 8. Retraits Mentors (Payouts)

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/mentor/balance` | Solde disponible en FCFA, total gagné | Oui (Mentor) |
| `GET` | `/api/mentor/payout-methods` | Moyens de paiement (Mobile Money, Virement) | Oui (Mentor) |
| `POST` | `/api/mentor/payout/request` | Demande de retrait des gains | Oui (Mentor) |
| `GET` | `/api/mentor/payout-requests` | Historique des demandes de retrait | Oui (Mentor) |
| `POST` | `/api/mentor/payout/{id}/cancel` | Annuler une demande de retrait en attente | Oui (Mentor) |

### 9. Chat IA & Orientation

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/v2/chat/conversations` | Liste des conversations d'orientation | Oui |
| `POST` | `/api/v2/chat/conversations` | Créer une nouvelle conversation | Oui |
| `GET` | `/api/v2/chat/conversations/{id}` | Messages d'une conversation | Oui |
| `POST` | `/api/v2/chat/send` | Envoyer un message (IA ou conseiller humain) | Oui |
| `POST` | `/api/v2/chat/conversations/{id}/request-human` | Solliciter l'intervention d'un conseiller humain | Oui |
| `DELETE`| `/api/v2/chat/conversations/{id}` | Supprimer une conversation | Oui |

### 10. Messages Directs (Jeune <-> Mentor)

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/v2/messages` | Liste des discussions actives | Oui |
| `GET` | `/api/v2/messages/{mentorship}` | Messages échangés dans le cadre d'un mentorat | Oui |
| `POST` | `/api/v2/messages/{mentorship}` | Envoyer un message texte ou pièce jointe | Oui |
| `PATCH` | `/api/v2/messages/{message}/update` | Modifier un message | Oui |
| `DELETE`| `/api/v2/messages/{message}` | Supprimer un message | Oui |

### 11. Ressources Pédagogiques & Documents Académiques

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/v2/resources` | Liste des ressources disponibles | Oui |
| `GET` | `/api/v2/resources/{id}` | Détail d'une ressource | Oui |
| `POST` | `/api/v2/resources/{id}/unlock` | Débloquer une ressource premium avec des crédits | Oui |
| `GET` | `/api/v2/documents` | Liste des documents de l'utilisateur | Oui |
| `POST` | `/api/v2/documents` | Téléverser un document | Oui |
| `GET` | `/api/v2/documents/{id}/download` | Télécharger un document | Oui |
| `DELETE`| `/api/v2/documents/{id}` | Supprimer un document | Oui |
| `GET` | `/api/v2/document-types` | Types de documents acceptés | Oui |

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
