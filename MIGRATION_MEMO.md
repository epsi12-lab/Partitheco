### Mémo de Migration : PARTITHECO (GitLab ➔ GitHub ➔ Render)

> **Statut (mise en conformité production) :** la migration SQLite → PostgreSQL décrite ci-dessous est terminée. SQLite a été retiré entièrement du code, y compris des tests (qui tournent désormais contre un vrai PostgreSQL via `docker-compose` en local et un service Postgres en CI). Ce document est conservé à titre historique.

Ce mémo regroupe toutes les informations nécessaires pour transformer votre projet L2 en une application web moderne, prête pour le déploiement sur **Render**.

---

### 1. Procédure de migration vers GitHub
Pour transférer votre projet du GitLab de la faculté vers GitHub tout en gardant votre historique :

1.  **Créer un nouveau dépôt vide** sur votre compte GitHub (sans README ni Licence).
2.  **Dans votre terminal (à la racine du projet) :**
    ```bash
    # Renommer l'ancien lien GitLab pour le garder en archive
    git remote rename origin gitlab

    # Ajouter le nouveau lien GitHub (remplacez par votre URL)
    git remote add origin https://github.com/VOTRE_PSEUDO/partitheco.git

    # Pousser le code vers GitHub
    git push -u origin main
    ```

---

### 2. Nouvelles technologies à implémenter

| Composant | Technologie actuelle | **Nouvelle Technologie** | Pourquoi ? |
| :--- | :--- | :--- | :--- |
| **Base de données** | SQLite (`db.sqlite`) | **PostgreSQL** | Render efface les fichiers locaux au redémarrage. PostgreSQL est persistant. |
| **Stockage Fichiers** | Local (`assets/img/`) | **Cloudinary** | Pour que vos images et PDF ne disparaissent pas à chaque mise à jour du site. |
| **Gestion Dépendances** | Manuelle (`require`) | **Composer** | Indispensable pour installer les bibliothèques de sécurité et l'API Cloudinary. |
| **Secrets** | En dur dans le code | **Variables d'env (.env)** | Protéger vos mots de passe et clés API (OpenWeather) sur GitHub. |

---

### 3. Changements de Code prioritaires

#### A. Connexion à la Base de données (`classes/Database.php`)
Il faudra passer d'une connexion fichier à une connexion par serveur :
```php
// Nouveau format pour PostgreSQL
$dsn = "pgsql:host=" . $_ENV['DB_HOST'] . ";port=5432;dbname=" . $_ENV['DB_NAME'];
$this->pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
```

#### B. Sécurité (XSS & CSRF)
*   **XSS :** Supprimer les `htmlspecialchars()` lors des `INSERT` dans les classes `Project.php` et `User.php`. Gardez-les **uniquement** dans les fichiers HTML au moment de l'affichage (`<?= htmlspecialchars(...) ?>`).
*   **CSRF :** Ajouter un champ caché avec un jeton de sécurité dans `create.php` et `login.php`.

#### C. Centralisation API
Refactoriser les fichiers dans `api/` pour qu'ils utilisent une structure de réponse JSON commune (ex: un dossier `api/utils/` pour gérer les erreurs).

---

### 4. Configuration Render (Check-list)

*   **Runtime :** Sélectionner `PHP`.
*   **Build Command :** `composer install`.
*   **Variables d'environnement :** À configurer dans l'onglet "Environment" sur Render :
    *   `DB_URL` (identifiants PostgreSQL).
    *   `CLOUDINARY_URL` (pour l'envoi d'images).
    *   `OPENWEATHER_API_KEY`.

---

### 5. Structure cible du projet
```text
/partitheco
├── .env (À NE PAS SUR GIT)
├── .gitignore (Ajouter .env et vendor/)
├── composer.json (Nouveau)
├── classes/
├── assets/
└── ... (le reste de vos fichiers)
```