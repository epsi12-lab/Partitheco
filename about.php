<?php
// about.php - Page À propos (présentation du site)

require_once __DIR__ . '/assets/locales/trad.php';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <section class="about-section">

    <h1 class="fade-in-up" style="--delay:0.1s;">
      <?= htmlspecialchars($t['about']['title']) ?>
    </h1>

    <div class="about-hero fade-in-up" style="--delay:0.2s;">
      <div class="about-icon">🎵</div>
      <h2>Bienvenue sur Partithéco</h2>
      <p class="tagline">Votre bibliothèque de partitions liturgiques en ligne</p>
    </div>

    <div class="about-content fade-in-up" style="--delay:0.3s;">
      <div class="about-card">
        <div class="card-icon">📚</div>
        <h3>Notre Mission</h3>
        <p>
          Partithéco est une plateforme dédiée au partage de partitions liturgiques et chorales.
          Notre objectif est de faciliter l'accès aux ressources musicales pour les chorales,
          les animateurs liturgiques et tous les passionnés de musique sacrée.
        </p>
      </div>

      <div class="about-card">
        <div class="card-icon">🎶</div>
        <h3>Nos Ressources</h3>
        <p>
          Découvrez une collection variée de partitions classées par moments de la messe
          (Entrée, Kyrie, Gloria, Psaume, Communion...) et par temps liturgiques
          (Avent, Noël, Carême, Pâques, Temps Ordinaire). Chaque partition peut être
          accompagnée d'enregistrements audio pour faciliter l'apprentissage.
        </p>
      </div>

      <div class="about-card">
        <div class="card-icon">👥</div>
        <h3>Pour Qui ?</h3>
        <p>
          Que vous soyez chef de chœur, organiste, animateur liturgique ou simple choriste,
          Partithéco vous offre les outils pour préparer vos célébrations : recherche avancée,
          playlists personnalisées, calendrier liturgique et livrets mensuels téléchargeables.
        </p>
      </div>

      <div class="about-card">
        <div class="card-icon">🌐</div>
        <h3>Fonctionnalités</h3>
        <ul>
          <li>Recherche par titre, auteur, moment de la messe ou temps liturgique</li>
          <li>Lecteur audio intégré avec contrôle de vitesse</li>
          <li>Création de playlists pour vos célébrations</li>
          <li>Calendrier liturgique interactif</li>
          <li>Génération de livrets PDF</li>
          <li>Mode hors-ligne (PWA)</li>
        </ul>
      </div>
    </div>

    <div class="about-cta fade-in-up" style="--delay:0.5s;">
      <h3>Rejoignez notre communauté</h3>
      <p>
        Créez un compte pour publier vos propres partitions, sauvegarder vos favoris
        et organiser vos célébrations.
      </p>
      <div class="cta-buttons">
        <a href="publications.php?lang=<?= $lang ?>" class="btn-primary">Explorer les partitions</a>
        <a href="contact.php?lang=<?= $lang ?>" class="btn-secondary">Nous contacter</a>
      </div>
    </div>

  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
