# Movie API

API REST développée avec **Symfony 8.1**, dans l'esprit de SensCritique : un catalogue de films, des comptes utilisateurs, des notes sur 10, des abonnements entre utilisateurs et un feed.

## Fonctionnalités

- **Films** : CRUD complet avec validation, recherche (titre, année, catégorie), tri et pagination
- **Catégories** : CRUD complet, recherche par nom, pagination
- **Acteurs** : liés aux films (ManyToMany), visibles dans le détail d'un film
- **Authentification** : inscription, connexion par **JWT** (`lexik/jwt-authentication-bundle`)
- **Rôles** : tout le monde peut lire le catalogue ; seul un `ROLE_ADMIN` peut créer, modifier ou supprimer des films et des catégories
- **Notes** : un utilisateur note un film de 1 à 10 (avec un commentaire facultatif), une seule note par film et par utilisateur, avec la date
- **Abonnements** : suivre / ne plus suivre un utilisateur (relation ManyToMany réflexive)
- **Feed** : dernières notes des utilisateurs que je suis, et historique de mes propres notes
- **Import** : commande qui remplit la base avec ~23 000 films du dataset `sample_mflix`, sans doublons de films, catégories ou acteurs

## Modèle de données

| Entité | Champs principaux | Relations |
|---|---|---|
| `Movie` | title, description, director, releaseYear, runtime, posterUrl, createdAt | ManyToMany `Category`, ManyToMany `Actor`, OneToMany `Rating` |
| `Category` | name (unique) | ManyToMany `Movie` |
| `Actor` | name (unique) | ManyToMany `Movie` |
| `User` | email (unique), username (unique), password, roles, createdAt | ManyToMany `User` (following / followers), OneToMany `Rating` |
| `Rating` | score (1 à 10), comment, ratedAt | ManyToOne `User`, ManyToOne `Movie` (unique par couple) |

## Prérequis

- PHP 8.4 ou plus, avec les extensions `pdo_sqlite`, `intl`, `mbstring`, `openssl`, `sodium`
- Composer
- Symfony CLI (facultatif, pour `symfony serve`)

La base de données est un fichier **SQLite** (`var/data_dev.db`) : rien d'autre à installer.

## Installation

```bash
git clone https://github.com/ismartphone/mon-api.git
cd mon-api
composer install

# Clés de signature des tokens JWT (dans config/jwt/, non versionnées)
php bin/console lexik:jwt:generate-keypair

# Création des tables
php bin/console doctrine:migrations:migrate --no-interaction
```

### Importer les films

Télécharger le fichier `movies.json` dans le dossier `data/` (non versionné, ~43 Mo) :

```bash
mkdir data
curl -L -o data/movies.json https://raw.githubusercontent.com/neelabalan/mongodb-sample-dataset/main/sample_mflix/movies.json
```

Puis lancer l'import (l'option `--no-debug` évite que la mémoire sature) :

```bash
php bin/console app:import-movies --no-debug
# ou seulement les 500 premiers films :
php bin/console app:import-movies --limit=500 --no-debug
```

La commande peut être relancée sans créer de doublons.

### Créer un administrateur

```bash
php bin/console app:create-user admin@example.com admin admin1234 --admin
```

Les utilisateurs simples s'inscrivent via `POST /api/register`, ou avec la même commande sans `--admin`.

## Lancer le projet

```bash
symfony serve
# ou, sans la Symfony CLI :
php -S 127.0.0.1:8000 -t public
```

L'API répond sur `http://127.0.0.1:8000/api`.

## Tester les routes

Toutes les routes sont dans **`requests.http`**, utilisable avec l'extension VS Code **REST Client** (ou le client HTTP de PhpStorm) :

1. Créer l'admin (voir plus haut) et lancer la requête *Register a new account*
2. Lancer les deux requêtes *Login* : les tokens sont récupérés automatiquement
3. Lancer les autres requêtes avec **Send Request**

## Routes

`🔓` publique · `👤` utilisateur connecté · `🛡️` administrateur

### Authentification et compte

| Méthode | Route | Accès | Description |
|---|---|---|---|
| POST | `/api/register` | 🔓 | Créer un compte (`email`, `username`, `password`) |
| POST | `/api/login_check` | 🔓 | Se connecter (`email`, `password`) → token JWT + infos utilisateur |
| GET | `/api/me` | 👤 | Mon compte |
| GET | `/api/me/ratings` | 👤 | Mes dernières notes (paginé) |
| GET | `/api/me/following` | 👤 | Les utilisateurs que je suis |
| GET | `/api/me/followers` | 👤 | Mes abonnés |

Les routes protégées attendent l'en-tête `Authorization: Bearer <token>`.

### Films

| Méthode | Route | Accès | Description |
|---|---|---|---|
| GET | `/api/movies` | 🔓 | Liste paginée et filtrable |
| GET | `/api/movies/{id}` | 🔓 | Détail (acteurs, note moyenne, nombre de notes) |
| POST | `/api/movies` | 🛡️ | Créer un film |
| PUT | `/api/movies/{id}` | 🛡️ | Modifier un film |
| DELETE | `/api/movies/{id}` | 🛡️ | Supprimer un film |

Paramètres de `GET /api/movies` :

| Paramètre | Exemple | Description |
|---|---|---|
| `title` | `star` | Le titre contient ce texte (insensible à la casse) |
| `year` | `1977` | Année de sortie |
| `category` | `3` | Id d'une catégorie |
| `sort` | `title` | `title`, `releaseYear` (défaut) ou `createdAt` |
| `order` | `asc` | `asc` ou `desc` (défaut) |
| `page` | `2` | Numéro de page (défaut 1) |
| `limit` | `20` | Éléments par page, de 1 à 50 (défaut 10) |

Corps attendu pour `POST` / `PUT` :

```json
{
    "title": "The Matrix",
    "description": "A computer hacker learns about the true nature of reality.",
    "director": "Lana Wachowski, Lilly Wachowski",
    "releaseYear": 1999,
    "runtime": 136,
    "posterUrl": "https://example.com/matrix.jpg",
    "categoryIds": [1, 2]
}
```

### Catégories

| Méthode | Route | Accès | Description |
|---|---|---|---|
| GET | `/api/categories` | 🔓 | Liste paginée (`name`, `page`, `limit`) |
| GET | `/api/categories/{id}` | 🔓 | Détail |
| POST | `/api/categories` | 🛡️ | Créer (`name`) |
| PUT | `/api/categories/{id}` | 🛡️ | Renommer (`name`) |
| DELETE | `/api/categories/{id}` | 🛡️ | Supprimer |

### Notes

| Méthode | Route | Accès | Description |
|---|---|---|---|
| GET | `/api/movies/{id}/ratings` | 🔓 | Notes d'un film (paginé) |
| PUT | `/api/movies/{id}/rating` | 👤 | Noter un film (`score` de 1 à 10, `comment` facultatif) : 201 à la création, 200 en cas de modification |
| DELETE | `/api/movies/{id}/rating` | 👤 | Supprimer ma note |

### Utilisateurs, abonnements et feed

| Méthode | Route | Accès | Description |
|---|---|---|---|
| GET | `/api/users/{id}` | 🔓 | Profil public (pseudo, nombre d'abonnés et d'abonnements) |
| GET | `/api/users/{id}/ratings` | 🔓 | Dernières notes d'un utilisateur (paginé) |
| POST | `/api/users/{id}/follow` | 👤 | Suivre un utilisateur |
| DELETE | `/api/users/{id}/follow` | 👤 | Ne plus suivre un utilisateur |
| GET | `/api/feed` | 👤 | Dernières notes des utilisateurs que je suis (paginé) |

### Format des réponses

Les listes paginées ont toutes la même forme :

```json
{
    "items": [],
    "total": 23539,
    "page": 1,
    "limit": 10,
    "lastPage": 2354
}
```

Codes HTTP utilisés : `200` OK, `201` créé, `204` supprimé / pas de contenu, `401` non connecté, `403` accès refusé, `404` introuvable, `422` données invalides (avec le détail des erreurs de validation).

## Organisation du code

```
src/
├── Command/         # app:import-movies, app:create-user
├── Controller/      # un contrôleur par ressource, méthodes courtes
├── Dto/             # données d'entrée validées (body et query string)
├── Entity/          # entités Doctrine et groupes de sérialisation
├── EventListener/   # ajoute les infos utilisateur à la réponse de login
├── Model/           # Paginator
├── Repository/      # requêtes (recherche, feed...)
└── Service/         # logique métier (création, notes, abonnements, import)
```
