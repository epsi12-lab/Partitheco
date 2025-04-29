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
require_once __DIR__ . '/classes/Comment.php';

$db         = new Database();
$pdo        = $db->getPDO();
$projectObj = new Project($pdo);
$commentObj = new Comment($pdo);

$project    = $projectObj->getProjectById($projectId);
if (!$project) {
    die("Projet introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_submit'])) {
    $author  = trim($_POST['comment_author']  ?? '');
    $content = trim($_POST['comment_content'] ?? '');
    if ($author !== '' && $content !== '') {
        $commentObj->insert($projectId, $author, $content);
        header("Location: project.php?id=$projectId&lang=$lang");
        exit;
    }
}

$comments = $commentObj->getByProject($projectId);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<main class="project-detail">
  <h1><?= htmlspecialchars($project['title']) ?></h1>

  <?php
    $dt        = new DateTime($project['date_publication']);
    $formatted = $dt->format('d/m/Y \à H\hi');
  ?>
  <p class="project-date">Publié le <?= htmlspecialchars($formatted) ?></p>

  <ul class="project-meta">
    <?php if ($project['author']):   ?><li><strong>Auteur :</strong> <?= htmlspecialchars($project['author'])   ?></li><?php endif; ?>
    <?php if ($project['arranger']): ?><li><strong>Arrangeur :</strong> <?= htmlspecialchars($project['arranger']) ?></li><?php endif; ?>
    <?php if ($project['genre']):    ?><li><strong>Genre :</strong> <?= htmlspecialchars($project['genre'])    ?></li><?php endif; ?>
    <?php if ($project['tonality']): ?><li><strong>Tonalité :</strong> <?= htmlspecialchars($project['tonality']) ?></li><?php endif; ?>
  </ul>

  <?php
    $thumb = $project['thumbnail'] ?? '';
    if ($thumb) {
        $ext = strtolower(pathinfo($thumb, PATHINFO_EXTENSION));
        $url = "assets/img/{$thumb}";
        if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
          <img
            src="<?= htmlspecialchars($url) ?>"
            alt="Image de <?= htmlspecialchars($project['title']) ?>"
            class="project-media">
        <?php elseif ($ext === 'pdf'): ?>
          <a href="<?= htmlspecialchars($url) ?>" target="_blank">
            <img
              src="assets/img/pdf-icon.png"
              alt="PDF de <?= htmlspecialchars($project['title']) ?>"
              class="project-media pdf-icon">
          </a>
        <?php endif;
    }
  ?>

  <?php if (!empty($project['media'])):
    $mext = strtolower(pathinfo($project['media'], PATHINFO_EXTENSION));
    $murl = "assets/img/{$project['media']}";
    if (in_array($mext, ['mp4','webm','ogg'])): ?>
      <video class="project-media" controls preload="metadata">
        <source src="<?= htmlspecialchars($murl) ?>" type="video/<?= $mext ?>">
        Votre navigateur ne supporte pas la vidéo.
      </video>
    <?php elseif (in_array($mext, ['mp3','wav','oga','ogg'])): ?>
      <audio class="project-media" controls preload="metadata">
        <source src="<?= htmlspecialchars($murl) ?>" type="audio/<?= $mext ?>">
        Votre navigateur ne supporte pas l’audio.
      </audio>
    <?php endif;
  endif; ?>

  <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>

  <section id="commentsSection">
    <h3>Commentaires</h3>

    <?php if (empty($comments)): ?>
      <p>Aucun commentaire pour le moment.</p>
    <?php else: ?>
      <?php foreach ($comments as $cm): ?>
        <div class="comment-item">
          <p class="author">
            <?= htmlspecialchars($cm['author']) ?>
            <span class="date">
              <?= (new DateTime($cm['created_at']))->format('d/m/Y H:i') ?>
            </span>
          </p>
          <p class="content"><?= nl2br(htmlspecialchars($cm['content'])) ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" novalidate class="comment-form">
      <input
        type="text"
        name="comment_author"
        placeholder="Votre nom"
        required>
      <textarea
        name="comment_content"
        rows="3"
        placeholder="Votre commentaire"
        required></textarea>
      <button name="comment_submit" type="submit">Envoyer</button>
    </form>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
