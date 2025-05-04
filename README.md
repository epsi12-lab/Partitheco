# PARTITHECO

Un site de mise en ligne et de consultation de partitions de musique, accompagné d’extraits audio et/ou vidéo.

---

## 📖 Présentation

**PARTITHECO** est une plateforme web responsive et multilingue (Français / English) qui permet à tout utilisateur de :

- **Créer un compte** pour publier ses partitions (PDF, images, vidéos, audios…)
- **Consulter** la liste des projets publiés, avec pagination et recherche en temps réel
- **Voir** le détail d’un projet : titre, description, date de publication, métadonnées (auteur, arrangeur, genre, tonalité), aperçu PDF/image et lecteur audio/vidéo intégré
- **Gérer** ses propres projets depuis un tableau de bord (CRUD : création, modification, suppression)
- **Envoyer** des commentaires sur chaque projet
- **S’abonner** à une newsletter
- **Changer de langue** à la volée grâce à un switch 🇫🇷 / 🇬🇧

Le site est construit en **PHP** (sans frameworks), utilise **SQLite** via **PDO**, et tous les formulaires sont validés côté client (JS) et côté serveur (PHP). L’UI intègre des animations CSS/JS (fade-in, néon, boutons dégradés, back-to-top…).

---

## 🔧 Limites du projet

- **Pas de framework** : toute la logique est faite « à la main », ce qui peut entraîner des répétitions ou un manque d’abstractions avancées.

- **Sécurité minimale** :

    - Les injections SQL sont protégées via PDO + requêtes préparées, mais d’autres failles (CSRF, validation plus fine…) ne sont pas exhaustivement couvertes.

    - Les mots de passe sont hashés, mais il n’y a pas d’envoi d’email de confirmation ou de réinitialisation sécurisée.

- **Gestion des fichiers** :

    - Les médias sont stockés directement dans assets/img/ sans CDN ni stockage cloud.

    - Pas de traitement avancé (redimensionnement, compression, antivirus…) sur l’upload.

- **Performances** :

    - Pas de cache HTTP ni moteur de sessions distribué.

    - Les requêtes AJAX sont basiques et ne gèrent pas la mise en cache côté client.

- **Responsive** : testé sur mobiles et desktop, mais certaines combinaisons d’écran très larges ou très étroits peuvent mal se comporter.

- **Internationalisation** : seuls les éléments statiques sont traduits via PHP. Les contenus utilisateurs (titres, descriptions, commentaires, etc.) restent dans la langue d’origine (français).

---

## ⚠️ À savoir

Ce projet est une preuve de concept développée dans le cadre du cours de **Programmation Web 2**.  
Il peut donc contenir des imperfections, des bugs ou des limitations fonctionnelles/UI. N’hésitez pas à me faire remonter les éventuels problèmes !

---
Par **TUMPA MADILA Bruce**  
*Dernière mise à jour : 04/05/2025*
