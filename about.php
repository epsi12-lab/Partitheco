<?php
// about.php

require_once __DIR__ . '/assets/locales/trad.php';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <section class="about-section">

    <h1 class="fade-in-up" style="--delay:0.1s;">
      <?= htmlspecialchars($t['pages']['about_title']) ?>
    </h1>

    <div class="about-profile fade-in-up" style="--delay:0.2s;">
      <img
        src="/assets/static/epsi12.JPG"
        alt="Photo de Epsilon12!"
        class="profile-photo">
      <div class="profile-text">
        <h2>Bonjour, je suis Epsilon12!</h2>
        <p>
          Passionné de musique et de développement web, j’ai conçu PARTITHECO pour partager
          mes partitions, mes enregistrements audio et mes vidéos. Mon objectif est de rendre
          accessible à tous un espace où télécharger et découvrir des œuvres en un clic.
        </p>
      </div>
    </div>

    <div class="project-description fade-in-up" style="--delay:0.4s;">
      <h2>PARTITHECO</h2>
      <p>
        PARTITHECO est une plateforme de publication de partitions de musique, accompagnées
        d’extraits audio et de vidéos pédagogiques. Chaque utilisateur peut créer son compte,
        déposer ses fichiers (PDF, MP3, MP4…), et les visiteurs peuvent parcourir, écouter
        et télécharger librement les créations.
      </p>
      <p>
        Construit en PHP (SQLite + PDO), responsive et multilingue, PARTITHECO met l’accent
        sur la simplicité d’usage, l’accessibilité et la sécurité des contenus.
      </p>
      <p>
        Pour toute question ou collaboration, n’hésitez pas à nous <a href="contact.php?lang=<?= $lang ?>">contacter</a>.
      </p>
    </div>

  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
