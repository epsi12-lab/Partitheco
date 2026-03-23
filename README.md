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
| **Base de données** | PostgreSQL (Supabase) / SQLite (local) |
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

# Vérifier le projet
composer lint
composer test

# Lancer le serveur
php -S localhost:8000
```

### Variables d'environnement

```env
BASE_URL=http://localhost:8000

# Base de données (PostgreSQL pour production)
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

# Tests locaux
composer test
```

---

## 📁 Structure du projet

```
Partitheco/
├── .github/workflows/   # CI GitHub Actions
├── api/                 # Endpoints JSON
├── assets/
│   ├── css/            # Styles (thème liturgique)
│   ├── js/             # Scripts
│   ├── locales/        # Traductions (fr, en)
│   └── static/         # Images statiques
├── classes/            # Classes PHP (repositories, services, modeles)
├── includes/           # Composants (navbar, footer)
├── scripts/            # Outils de verification locaux
├── tests/              # Runner et tests locaux
├── vendor/             # Dépendances Composer
├── bootstrap.php       # Initialisation
├── index.php           # Page d'accueil
└── Dockerfile          # Configuration Docker
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
