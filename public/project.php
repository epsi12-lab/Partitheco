<?php
// project.php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../lang/trad.php';

use App\Database;
use App\ProjectPageService;

$db         = new Database();
$pdo        = $db->getPDO();
$pageService = new ProjectPageService($pdo);

if (!isset($_GET['id'])) {
    redirect_error('404', 'ID de projet manquant.');
}
$projectId = (int) $_GET['id'];
$currentUser = $_SESSION['user'] ?? null;
$pageData = $pageService->buildPageData($projectId, $currentUser);
$project = $pageData['project'];
$avgRating = $pageData['avgRating'];
$ratingCount = $pageData['ratingCount'];
$userRating = $pageData['userRating'];
$isFavorite = $pageData['isFavorite'];
$comments = $pageData['comments'];
$downloadCount = $pageData['downloadCount'];
$userPlaylists = $pageData['userPlaylists'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_toggle'])) {
    if (!$currentUser) {
        header('Location: login.php');
        exit;
    }
    verify_csrf_or_fail($_POST['csrf_token'] ?? null);
    $pageService->toggleFavorite($projectId, $currentUser, $isFavorite);
    header("Location: project.php?id=$projectId&lang=$lang");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_submit'])) {
    verify_csrf_or_fail($_POST['csrf_token'] ?? null);
    if ($pageService->addComment(
        $projectId,
        $_POST['comment_author'] ?? '',
        $_POST['comment_content'] ?? ''
    )) {
        header("Location: project.php?id=$projectId&lang=$lang");
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>
<main class="project-detail">
  <div class="project-title-block">
    <h1><?= htmlspecialchars($project['title'], ENT_NOQUOTES, 'UTF-8', false) ?></h1>
    <div class="project-actions">
      <form method="post" class="project-favorite-form">
          <?php csrf_input(); ?>
          <button type="submit" name="favorite_toggle" class="btn-favorite" title="<?= $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>">
              <?= $isFavorite ? '★' : '☆' ?>
          </button>
      </form>
      <?php if ($currentUser && !empty($userPlaylists)): ?>
      <form method="post" action="playlist.php?id=<?= (int)($userPlaylists[0]['id']) ?>&lang=<?= $lang ?>" class="project-playlist-form">
        <?php csrf_input(); ?>
        <select name="target_playlist_id" class="project-playlist-select" onchange="this.form.action='playlist.php?id='+this.value+'&lang=<?= $lang ?>';">
          <?php foreach ($userPlaylists as $pl): ?>
            <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
        <button type="submit" name="add_item" class="btn-secondary btn-no-spinner" title="Ajouter à la liste">➕ Ajouter à la liste</button>
      </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Système de notation -->
  <div class="rating-section" data-project-id="<?= $projectId ?>">
    <div class="stars-display">
      <?php for ($i = 1; $i <= 5; $i++): ?>
        <span class="star <?= ($avgRating && $i <= round($avgRating)) ? 'filled' : '' ?>" data-value="<?= $i ?>">★</span>
      <?php endfor; ?>
    </div>
    <span class="rating-info">
      <?php if ($avgRating): ?>
        <?= $avgRating ?>/5 (<?= $ratingCount ?> avis)
      <?php else: ?>
        Pas encore noté
      <?php endif; ?>
    </span>
    <?php if ($currentUser): ?>
      <div class="user-rating">
        <span>Votre note :</span>
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <span class="star-btn <?= ($userRating && $i <= $userRating) ? 'active' : '' ?>" data-value="<?= $i ?>">★</span>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php
    $dt        = new DateTime($project['date_publication']);
    $fmt       = $t['project']['date_format'];
    $formatted = $dt->format($fmt);
  ?>
  <p class="project-date">
    <?= htmlspecialchars($t['project']['published_on']) ?>
    <?= htmlspecialchars($formatted) ?>
    — <?= htmlspecialchars($t['project']['by']) ?> 
    <strong><?= htmlspecialchars($project['publisher']) ?></strong>
  </p>

  <ul class="project-meta">
    <?php if ($project['author']):   ?>
      <li><strong><?= htmlspecialchars($t['project']['author']) ?></strong> 
        <?= htmlspecialchars($project['author'])   ?>
      </li>
    <?php endif; ?>
    <?php if ($project['arranger']): ?>
      <li><strong><?= htmlspecialchars($t['project']['arranger']) ?></strong>
        <?= htmlspecialchars($project['arranger']) ?>
      </li>
    <?php endif; ?>
    <?php if ($project['genre']):    ?>
      <li><strong><?= htmlspecialchars($t['project']['genre']) ?></strong>
        <?= htmlspecialchars($project['genre'])    ?>
      </li>
    <?php endif; ?>
    <?php if ($project['tonality']): ?>
      <li><strong><?= htmlspecialchars($t['project']['tonality']) ?></strong>
        <?= htmlspecialchars($project['tonality']) ?>
      </li>
    <?php endif; ?>
  </ul>

  <?php if ($project['moment_messe'] || $project['temps_liturgique'] || $project['voix']): ?>
  <div class="liturgical-tags">
    <?php if ($project['moment_messe']): ?>
      <a href="publications.php?moment=<?= urlencode(strtolower($project['moment_messe'])) ?>&lang=<?= $lang ?>" class="lit-tag moment">
        🎵 <?= htmlspecialchars($project['moment_messe']) ?>
      </a>
    <?php endif; ?>
    <?php if ($project['temps_liturgique']): ?>
      <a href="publications.php?temps=<?= urlencode(strtolower($project['temps_liturgique'])) ?>&lang=<?= $lang ?>" class="lit-tag temps">
        📅 <?= htmlspecialchars($project['temps_liturgique']) ?>
      </a>
    <?php endif; ?>
    <?php if ($project['voix']): ?>
      <span class="lit-tag voix">🎤 <?= htmlspecialchars($project['voix']) ?></span>
    <?php endif; ?>
    <?php if (!empty($project['is_liturgical'])): ?>
      <span class="lit-tag liturgical">✝️ Chant Liturgique</span>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php
    $thumb = $project['thumbnail'] ?? '';
    if ($thumb) {
        $isCloudinary = str_starts_with($thumb, 'http');
        $ext = strtolower(pathinfo($thumb, PATHINFO_EXTENSION));
        $url = $isCloudinary ? $thumb : "assets/img/{$thumb}";
        if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
          <a href="javascript:void(0)" onclick="openLightbox('<?= htmlspecialchars($url) ?>')">
            <img
              src="<?= htmlspecialchars($url) ?>"
              alt="Image de <?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?>"
              class="project-media">
          </a>
          <p style="text-align:center; margin-top:.5rem;"><a class="btn-secondary" href="<?= htmlspecialchars($url) ?>" download>Télécharger l'image</a></p>
        <?php elseif ($ext === 'pdf'): ?>
          <a href="javascript:void(0)" onclick="openPDF('<?= htmlspecialchars($url) ?>')">
            <img
              src="assets/static/file-pdf-solid.svg"
              alt="PDF de <?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?>"
              class="project-media pdf-icon">
          </a>
          <p style="text-align:center; margin-top:.5rem;"><a class="btn-primary" href="<?= htmlspecialchars($url) ?>" download>Télécharger le PDF</a></p>
        <?php endif;
    }
  ?>

  <?php if (!empty($project['media'])):
    $isMediaCloudinary = str_starts_with($project['media'], 'http');
    $mext = strtolower(pathinfo($project['media'], PATHINFO_EXTENSION));
    $murl = $isMediaCloudinary ? $project['media'] : "assets/img/{$project['media']}";
    if (in_array($mext, ['mp4','webm','ogg'])): ?>
      <video class="project-media" controls preload="metadata">
        <source src="<?= htmlspecialchars($murl) ?>" type="video/<?= $mext ?>">
        Votre navigateur ne supporte pas la vidéo.
      </video>
    <?php elseif (in_array($mext, ['mp3','wav','oga','ogg'])): ?>
      <!-- Lecteur Audio Amélioré -->
      <div class="audio-player-enhanced" data-project-id="<?= $projectId ?>">
        <div class="player-header">
          <div class="player-icon">🎵</div>
          <div class="player-info">
            <h4><?= htmlspecialchars($project['title']) ?></h4>
            <p><?= htmlspecialchars($project['author'] ?? 'Auteur inconnu') ?></p>
          </div>
        </div>
        
        <audio preload="metadata">
          <source src="<?= htmlspecialchars($murl) ?>" type="audio/<?= $mext ?>">
        </audio>
        
        <div class="player-controls">
          <button class="btn-play">▶</button>
          <div class="progress-container">
            <div class="progress-bar">
              <div class="progress-fill"></div>
            </div>
            <div class="time-display">
              <span class="current-time">0:00</span>
              <span class="duration">0:00</span>
            </div>
          </div>
          <div class="volume-control">
            <span>🔊</span>
            <input type="range" class="volume-slider" min="0" max="1" step="0.1" value="1">
          </div>
        </div>
        
        <div class="extra-controls">
          <div class="speed-controls">
            <span>Vitesse :</span>
            <button class="btn-speed" data-speed="0.75">0.75x</button>
            <button class="btn-speed active" data-speed="1">1x</button>
            <button class="btn-speed" data-speed="1.25">1.25x</button>
            <button class="btn-speed" data-speed="1.5">1.5x</button>
          </div>
          <div class="download-stats">
            <span>📥</span>
            <span class="download-count"><?= $downloadCount ?></span>
            <span>téléchargements</span>
          </div>
          <a href="api/download.php?id=<?= $projectId ?>&type=audio" class="btn-download" onclick="trackDownload(<?= $projectId ?>, 'audio')">
            ⬇️ Télécharger l'audio
          </a>
        </div>
      </div>
    <?php endif;
  endif; ?>

  <p><?= nl2br(htmlspecialchars($project['description'], ENT_NOQUOTES, 'UTF-8', false)) ?></p>

  <section id="commentsSection">
    <h3><?= htmlspecialchars($t['comments']['title']) ?></h3>

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
          <p class="content"><?= nl2br(htmlspecialchars($cm['content'], ENT_NOQUOTES, 'UTF-8', false)) ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" novalidate class="comment-form">
      <?php csrf_input(); ?>
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
      <button name="comment_submit" type="submit">
        <?= htmlspecialchars($t['comments']['submit']) ?>
      </button>
    </form>
  </section>
</main>

<script src="assets/js/audio-player.js"></script>
<script src="assets/js/rating.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
