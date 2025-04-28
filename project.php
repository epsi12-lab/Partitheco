<?php
// project.php

session_start();

$lang = $_GET['lang'] ?? 'fr';

require_once __DIR__ . '/assets/locales/trad.php';
$t = loadTranslations($lang);

if (!isset($_GET['id'])) {
    die("ID de projet manquant.");
}
$projectId = (int) $_GET['id'];

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Project.php';

$db         = new Database();
$pdo        = $db->getPDO();
$projectObj = new Project($pdo);
$project    = $projectObj->getProjectById($projectId);

if (!$project) {
    die("Projet introuvable.");
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<main class="project-detail">
  <h1><?= htmlspecialchars($project['title']) ?></h1>

  <?php if (!empty($project['thumbnail'])): ?>
    <?php
      $file    = $project['thumbnail'];
      $ext     = strtolower(pathinfo($file, PATHINFO_EXTENSION));
      $fileUrl = "assets/img/" . $file;
    ?>
    <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
      <a href="<?= htmlspecialchars($fileUrl) ?>" target="_blank">
        <img
          src="<?= htmlspecialchars($fileUrl) ?>"
          alt="Image du projet <?= htmlspecialchars($project['title']) ?>"
          style="max-width:100%; height:auto; display:block; margin-bottom:1rem;">
      </a>

    <?php elseif ($ext === 'pdf'): ?>
      <a href="<?= htmlspecialchars($fileUrl) ?>" target="_blank">
        <img
          src="assets/static/file-pdf-solid.svg"
          alt="PDF du projet <?= htmlspecialchars($project['title']) ?>"
          class="project-thumbnail pdf-icon">
      </a>

    <?php else: ?>
      <p>
        <a href="<?= htmlspecialchars($fileUrl) ?>" target="_blank">
          Télécharger le fichier (<?= strtoupper($ext) ?>)
        </a>
      </p>
    <?php endif; ?>
  <?php endif; ?>

  <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
