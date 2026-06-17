### Plan de Refonte Liturgique - Projet Partitheco

Ce document détaille la stratégie de refonte totale du projet pour s'aligner sur un modèle de plateforme de partitions liturgiques professionnel, inspiré par des sites comme *Chantons en Église*.

---

#### 1. Vision et Identité (Style "Chantons en Église")
*   **Thématique :** Transition d'une galerie de musique généraliste vers une bibliothèque de ressources liturgiques et chorales.
*   **Design :** Palette de couleurs sobre (Bleu Nuit, Or, Blanc Cassé), typographie élégante (Serif pour les titres), et mise en page structurée.
*   **Nettoyage :** Suppression des widgets météo, cartes (OpenStreetMap) et autres éléments non essentiels pour se concentrer sur le contenu musical.

---

#### 2. Évolution de la Base de Données & Modèle de Données
Le modèle actuel doit s'enrichir pour permettre un classement liturgique précis :
*   **Table `projects` (Partitions) :** Ajouter des colonnes pour :
    *   `moment_messe` (Entrée, Aspersion, Psaume, Aclammation, Prière Universelle, Offrande, Offertoire, Communion, Action de grâce, Envoi, Kyrie, Gloria, Credo, Sanctus, Agnus Dei, Antienne d'ouverture, Antienne de communion).
    *   `temps_liturgique` (Avent, Noël, Carême, Pâques, Ordinaire).
    *   `is_liturgical` (Booléen pour distinguer les chants liturgiques des chants religieux non-liturgiques).
    *   `voix` (SATB, Unisson, Solo).
*   **Table `users` :** Ajouter des colonnes pour le profil (ex: `paroisse`, `role_choral`).

---

#### 3. Refonte des Comptes Utilisateurs
*   **Espace "Ma Chorale" :** Permettre aux utilisateurs de créer des listes de chants (playlists) pour des célébrations spécifiques.
*   **Gestion des Droits :** Distinction entre utilisateurs "Contributeurs" (peuvent publier) et "Lecteurs" (peuvent consulter/télécharger).
*   **Favoris :** Système pour "mettre de côté" des partitions pour une date ultérieure.

---

#### 4. Interface & Expérience Utilisateur (UI/UX)
*   **Page d'Accueil :**
    *   Moteur de recherche par **Titre**, **Moment de la Messe** ou **Temps Liturgique**.
    *   Section "Suggestions pour dimanche prochain" (basée sur le calendrier liturgique).
*   **Page de Publication (Détail) :**
    *   Affichage prioritaire de la visionneuse PDF (Aperçu direct).
    *   Lecteur audio pour l'apprentissage des voix (si disponible).
    *   Bouton de téléchargement direct.
*   **Filtres de Recherche :** Recherche avancée par instrument, nombre de voix et moment de la messe.

---

#### 5. Feuille de Route Technique (Actions Immédiates)
- [x] **Phase 1 : Nettoyage** ✅
    - Supprimer les fichiers liés à la météo et aux cartes (`map-weather.css`, scripts Leaflet dans `index.php`).
    - Épurer `index.php` pour une présentation plus institutionnelle.
- [x] **Phase 2 : Restructuration DB** ✅
    - Migration des tables pour inclure les métadonnées liturgiques détaillées (Moments, Type de chant).
    - Mise à jour de la classe `Project.php` pour gérer ces nouveaux champs.
- [x] **Phase 3 : Refonte UI** ✅
    - Création d'un nouveau thème CSS "Liturgique".
    - Mise en place d'une page d'accueil avec recherche filtrée.
    - Refonte de la page publications avec filtres avancés.
    - Mise à jour des formulaires create/edit avec champs liturgiques.
    - Tags liturgiques cliquables sur la page de détail.
- [x] **Phase 4 : Gestion des comptes** ✅
    - Système de Favoris fonctionnel.
    - Playlists pour organiser les chants.

---

#### 6. Suppression des Éléments Obsolètes
*   **Fichiers à supprimer/modifier :**
    *   `assets/css/map-weather.css` - Supprimé
    *   `assets/js/base.js` (retirer la logique de cache météo) - Nettoyé
    *   `index.php` (retirer les sections `#mapWeatherSection`) - Refait entièrement

---

#### 7. Fichiers Créés/Modifiés lors de la Refonte

**Nouveaux fichiers CSS :**
- `assets/css/homepage.css` - Styles de la page d'accueil
- `assets/css/publications.css` - Styles de la page publications
- `assets/css/liturgique.css` - Thème liturgique global

**Pages refaites :**
- `index.php` - Page d'accueil complète style Chantons en Église
- `publications.php` - Page de recherche avec filtres avancés
- `create.php` - Formulaire avec champs liturgiques
- `edit.php` - Formulaire d'édition avec champs liturgiques
- `project.php` - Page de détail avec tags liturgiques

---

#### 8. Améliorations Réalisées

- [x] Lecteur audio intégré amélioré (contrôles vitesse, volume, barre de progression)
- [x] Export de playlists (JSON, CSV, TXT)
- [x] Statistiques de téléchargement (table downloads, compteur par partition)

#### 9. Améliorations Dynamiques & Responsive (Réalisées)

- [x] CSS responsive complet (breakpoints: 1200px, 1024px, 768px, 600px, 480px, 360px)
- [x] Optimisations tactiles (touch targets 44px, désactivation hover sur mobile)
- [x] Animations au scroll (Intersection Observer)
- [x] Lazy loading des images
- [x] Autocomplétion de recherche en temps réel
- [x] Pull-to-refresh sur mobile
- [x] Navigation par swipe sur les pills
- [x] Notifications toast (online/offline)
- [x] Préchargement des pages au survol
- [x] Support mode paysage et impression
- [x] Support `prefers-reduced-motion`

#### 10. Améliorations Avancées (Réalisées)

- [x] Livrets mensuels PDF téléchargeables (`livrets.php`, `api/livrets.php`)
- [x] Calendrier liturgique interactif (`calendrier.php`)
- [x] Import de playlists depuis JSON (`api/import-playlist.php`)
- [x] Mode hors-ligne PWA (`manifest.json`, `sw.js`, `offline.html`)
- [x] Classe Cloudinary pour stockage média (`classes/Cloudinary.php`)
- [x] Support PostgreSQL dans Database.php (via variables d'environnement)

#### 11. Configuration pour Déploiement

**Variables d'environnement requises (.env) :**
```
# PostgreSQL (Render)
DB_HOST=your-host.render.com
DB_NAME=partitheco
DB_USER=partitheco_user
DB_PASS=your_password

# Cloudinary
CLOUDINARY_CLOUD_NAME=your_cloud
CLOUDINARY_API_KEY=your_key
CLOUDINARY_API_SECRET=your_secret
```

#### 12. Améliorations Avancées (Session 26/02/2026)

- [ ] ~~**Notifications push**~~ : retirée (mise en conformité production) — l'abonnement était câblé côté serveur mais jamais relié à l'UI, et aucun mécanisme d'envoi réel (VAPID/web-push) n'existait. `classes/PushNotification.php` et `api/push-subscribe.php` ont été supprimés.
- [x] **Recherche par paroles** : `api/search-lyrics.php` avec mise en évidence des termes, câblée dans l'UI de `publications.php` (mise en conformité production)
- [x] **API Calendrier liturgique** : `classes/LiturgicalCalendar.php`, `api/liturgical-calendar.php`
  - Calcul automatique de Pâques (algorithme Meeus/Jones/Butcher)
  - Détermination du temps liturgique pour toute date
  - Fêtes fixes et mobiles
  - Endpoints : season, month, feasts, easter, next-sunday

#### 13. Modifications UI (Session 26/02/2026)

- [x] Lien "À propos" déplacé de la navbar vers le footer
- [x] Page `about.php` refaite pour présenter le site (sans photo personnelle)
- [x] Styles `about.css` mis à jour avec cartes et CTA

#### 14. Améliorations Finales (Session 26/02/2026 - Suite)

- [x] **Système de notation des partitions** : `classes/Rating.php`, `api/rate.php`, `assets/css/rating.css`, `assets/js/rating.js`
  - Étoiles cliquables (1-5) sur la page de détail
  - Moyenne et nombre d'avis affichés
  - Table `ratings` dans la base de données
- [x] **Import de playlists depuis fichier JSON** : Interface utilisateur avec modal dans `playlist.php`
  - Bouton "Importer" avec sélection de fichier
  - Recherche par ID ou titre des partitions
  - Message de confirmation avec compteur

#### 15. Améliorations Infrastructure (Session 26/02/2026)

- [x] **Mode hors-ligne amélioré** :
  - Service Worker v2 avec cache dédié pour les médias (PDF, audio, vidéo)
  - Stratégie Cache-First pour les partitions, Network-First pour les pages
  - API de messages pour gérer le cache manuellement
  - `assets/js/offline-cache.js` : Classe OfflineCacheManager
  - Boutons "Sauvegarder hors-ligne" sur les partitions

- [x] **Migration PostgreSQL** :
  - Script `migrate_postgres.php` pour migrer SQLite vers PostgreSQL
  - Création automatique des tables avec syntaxe PostgreSQL
  - Réinitialisation des séquences après migration
  - Support des variables d'environnement DB_HOST, DB_NAME, DB_USER, DB_PASS

- [x] **Stockage Cloudinary** :
  - Classe `classes/MediaUploader.php` pour upload unifié (local ou Cloudinary)
  - Détection automatique du type de ressource (image, video, raw)
  - Fallback vers stockage local si Cloudinary non configuré
  - Variables d'environnement : CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET
