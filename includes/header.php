<?php
// includes/header.php

require_once __DIR__ . '/../lang/trad.php';

?>


<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a2a44">
    <meta name="description" content="Partithéco - Votre bibliothèque de partitions liturgiques">
    <?php csrf_meta_tag(); ?>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/assets/img/icon-192.png">
    <title>Partithéco</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/animations.css">
    <link rel="stylesheet" href="/assets/css/navbar.css">
    <link rel="stylesheet" href="/assets/css/buttons.css">
    <link rel="stylesheet" href="/assets/css/projects.css">
    <link rel="stylesheet" href="/assets/css/project-detail.css">
    <link rel="stylesheet" href="/assets/css/forms.css">
    <link rel="stylesheet" href="/assets/css/liturgique.css">
    <link rel="stylesheet" href="/assets/css/homepage.css">
    <link rel="stylesheet" href="/assets/css/publications.css">
    <link rel="stylesheet" href="/assets/css/newsletter.css">
    <link rel="stylesheet" href="/assets/css/comments.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
    <link rel="stylesheet" href="/assets/css/ux-improvements.css">
    <link rel="stylesheet" href="/assets/css/about.css">
    <link rel="stylesheet" href="/assets/css/audio-player.css">
    <link rel="stylesheet" href="/assets/css/rating.css">
    <script src="assets/js/base.js" defer></script>
    <script src="assets/js/validation.js"></script>
    <script src="assets/js/dynamic-features.js" defer></script>
    <script src="assets/js/offline-cache.js" defer></script>
    <script>
      // Enregistrement du Service Worker PWA
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('SW enregistré:', reg.scope))
            .catch(err => console.log('SW erreur:', err));
        });
      }
    </script>

</head>
<body>
