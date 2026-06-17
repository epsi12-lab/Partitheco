# PARTITHECO

Plateforme web de partitions musicales liturgiques, inspirée de "Chantons en Église".

---

## 📖 Présentation

**PARTITHECO** est une bibliothèque de ressources liturgiques permettant aux chorales et animateurs de messe de :

- **Rechercher** des partitions par moment de la messe, temps liturgique, tonalité, voix
- **Créer un compte** pour publier et gérer ses partitions (PDF, images, audio, vidéo)
- **Organiser** ses favoris et créer des playlists ("Ma Chorale") pour les célébrations
- **Consulter** les détails : aperçu PDF intégré, lecteur audio/vidéo, métadonnées complètes
- **Partager** ses playlists via un lien unique
- **Changer de langue** (🇫🇷 Français / 🇬🇧 English)

---

## 🛠️ Stack technique

| Composant | Technologie |
|-----------|-------------|
| **Backend** | PHP natif (PSR-4 autoloading via Composer) |
| **Base de données** | PostgreSQL (Supabase en prod, conteneur Docker en local) |
| **Hébergement** | Render |
| **Médias** | Cloudinary |
| **Frontend** | HTML5, CSS3, JavaScript vanilla |

---

## ✨ Fonctionnalités

### Liturgiques
- Filtres par **moment de la messe** (Entrée, Kyrie, Gloria, Offertoire, Communion, Envoi...)
- Filtres par **temps liturgique** (Avent, Noël, Carême, Pâques, Temps Ordinaire...)
- Métadonnées : auteur, arrangeur, tonalité, voix (SATB, unisson...)

### Utilisateur
- Inscription / Connexion avec "Se souvenir de moi"
- Tableau de bord personnel
- Système de favoris ❤️
- Playlists personnalisées avec notes et ordre

### UI/UX
- Mode sombre 🌙
- Design responsive (mobile-first)
- Infinite scroll
- Visionneuse PDF intégrée
- Notifications toast
- Skeletons de chargement

### Sécurité
- Jetons CSRF sur tous les formulaires
- Validation MIME à l'upload
- Mots de passe hashés (bcrypt)
- Requêtes préparées (PDO)

---

## 🚀 Installation locale

```bash
# Cloner le projet
git clone https://github.com/epsi12-lab/Partitheco.git
cd Partitheco

# Installer les dépendances
composer install

# Configurer l'environnement
cp .env.example .env
# Éditer .env avec vos paramètres

# Démarrer une base PostgreSQL locale (Docker requis)
docker compose up -d db
php scripts/migrate.php

# Vérifier le projet
composer lint
composer test

# Lancer le serveur (le webroot est public/)
php -S localhost:8000 -t public
```

PostgreSQL est requis même en local : il n'y a plus de mode SQLite de secours. `docker-compose.yml` fournit un service `db` prêt à l'emploi pour le développement, et un service `db-test` dédié à `composer test`.

### Variables d'environnement

```env
BASE_URL=http://localhost:8000

# Base de données PostgreSQL (obligatoire, y compris en local)
DB_HOST=aws-1-eu-west-1.pooler.supabase.com
DB_PORT=5432
DB_NAME=postgres
DB_USER=postgres.votre_projet
DB_PASS=votre_mot_de_passe

# Cloudinary (optionnel)
CLOUDINARY_URL=cloudinary://...

# OpenWeather (optionnel)
OPENWEATHER_API_KEY=

# Politique CSP personnalisée (optionnel)
CONTENT_SECURITY_POLICY=
```

### Commandes utiles

```bash
# Vérification syntaxique de tout le projet
composer lint

# Tests locaux (nécessitent une base PostgreSQL de test)
docker compose up -d db-test
TEST_DB_HOST=localhost TEST_DB_PORT=5434 TEST_DB_USER=postgres TEST_DB_PASS=postgres TEST_DB_NAME=partitheco_test composer test
```

---

## 📁 Structure du projet

```
Partitheco/
├── public/              # Webroot — seul ce dossier est exposé par Apache
│   ├── index.php, login.php, ...  # Pages
│   ├── api/             # Endpoints JSON
│   └── assets/
│       ├── css/        # Styles (thème liturgique)
│       ├── js/         # Scripts
│       └── static/     # Images statiques
├── lang/                # Traductions (fr, en) — hors webroot, jamais appelé par URL
├── classes/             # Classes PHP (repositories, services, modeles)
├── includes/            # Composants (navbar, footer, sécurité, auth)
├── scripts/             # Outils CLI uniquement (lint, migrate, reset_db — jamais exposés en HTTP)
├── tests/               # Runner et tests locaux (PostgreSQL réel)
├── vendor/              # Dépendances Composer
├── bootstrap.php        # Initialisation
├── docker-compose.yml   # Environnement de dev/test local (Postgres inclus)
└── Dockerfile           # Configuration Docker (DocumentRoot = public/)
```

---

## 📝 Documentation

- `AMELIORATION.md` - Plan d'amélioration
- `PLAN_REFONTE_LITURGIQUE.md` - Détails de la refonte liturgique
- `MIGRATION_MEMO.md` - Notes de migration

---

## 👤 Auteur

**TUMPA MADILA Bruce**

---

*Dernière mise à jour : 20 mars 2026*
