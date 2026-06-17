### Plan d'Amélioration du Projet Partitheco

Ce document dresse la liste des améliorations prévues pour le projet, classées par catégories. Les éléments cochés ([x]) sont déjà réalisés.

#### 1. Infrastructure & Migration (MIGRATION_MEMO.md)
- [x] Migrer le dépôt de GitLab vers GitHub
- [x] Passer de SQLite à PostgreSQL (pour Render) — SQLite entièrement retiré, y compris des tests (PostgreSQL réel via docker-compose/CI)
- [ ] Configurer Cloudinary pour le stockage des médias (Images/PDF) (À faire en dernier)
- [x] Implémenter Composer pour la gestion des dépendances
- [x] Utiliser des variables d'environnement (`.env`) pour les secrets

#### 2. Sécurité & Robustesse
- [x] Implémenter des jetons CSRF sur tous les formulaires (`POST`)
- [x] Nettoyer l'utilisation de `htmlspecialchars` (uniquement à l'affichage, pas à l'insertion)
- [x] Renforcer la validation des types MIME lors de l'upload
- [x] Refactoriser `Database.php` pour séparer la connexion de l'initialisation des tables
- [x] Remplacer les `die()` par une gestion d'exceptions (`try/catch`) et une page d'erreur

#### 3. Architecture & Qualité du Code
- [x] Mettre en place l'autoloading PSR-4 avec Composer
- [x] Centraliser la configuration (URL de base, etc.) dans un fichier dédié ou `.env`
- [x] Ajouter du typage strict sur les propriétés et retours de fonctions restants
- [x] Créer une structure de réponse JSON commune pour l'API

#### 4. Interface Utilisateur (UI)
- [x] Implémenter un Mode Sombre (Dark Mode) avec variables CSS
- [x] Créer un véritable menu "Burger" pour la version mobile
- [x] Ajouter des "Skeletons" de chargement pour les projets
- [x] Améliorer les indicateurs d'état (spinners sur les boutons lors de l'envoi)
- [x] Remplacer les messages d'alerte statiques par des notifications "Toasts"

#### 5. Expérience Utilisateur (UX) & Fonctionnalités
- [x] Ajouter une barre de progression pour l'upload de médias
- [x] Intégrer un système de cache pour l'API OpenWeather
- [x] Transformer le bouton "Charger plus" en chargement infini (Infinite Scroll)
- [x] Intégrer une visionneuse PDF (`PDF.js` / Iframe) pour lire les partitions sans quitter le site
- [x] Ajouter une "Lightbox" pour la galerie d'images des projets