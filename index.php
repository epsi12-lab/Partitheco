<?php
// index.php
session_start();
$lang = $_GET['lang'] ?? 'fr';

require_once 'classes/Database.php';
require_once 'classes/Project.php';

$db         = new Database();
$projectObj = new Project($db->getPDO());
$projects   = $projectObj->getProjects(5, 0);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<main>
  <!-- Container des projets -->
  <div id="projectsContainer">
    <?php foreach ($projects as $project): ?>
      <div class="project-item" style="--order: <?= $i++ ?>">
        <?php
        $file    = $project['thumbnail'] ?? '';
        $ext     = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $fileUrl = "assets/img/{$file}";
        $detail  = "project.php?id={$project['id']}&lang={$lang}";
        ?>
        <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
          <a href="<?= $detail ?>">
            <img src="<?= htmlspecialchars($fileUrl) ?>" class="project-thumbnail">
          </a>
        <?php elseif ($ext === 'pdf'): ?>
          <a href="<?= htmlspecialchars($fileUrl) ?>" target="_blank">
            <img src="assets/static/file-pdf-solid.svg" class="project-thumbnail pdf-icon">
          </a>
        <?php endif; ?>

        <h3><a href="<?= $detail ?>"><?= htmlspecialchars($project['title']) ?></a></h3>
        <p><?= nl2br(htmlspecialchars(substr($project['description'], 0, 100))) ?>…</p>
      </div>
    <?php endforeach; ?>
  </div>

  <button onclick="location.href='publications.php?lang=<?= $lang ?>'">
  Voir plus de publications
  </button>

</main>

<script src="assets/js/base.js"></script>
<script src="assets/js/getProjects.js"></script>

<?php include 'includes/footer.php'; ?>
