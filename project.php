<?php
// project.php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/assets/locales/trad.php';

use App\Database;
use App\Project;
use App\Comment;
use App\Favorite;
use App\Download;
use App\Rating;

$db         = new Database();
$pdo        = $db->getPDO();
$projectObj = new Project($pdo);
$commentObj = new Comment($pdo);
$favoriteObj = new Favorite($pdo);
$downloadObj = new Download($pdo);
$ratingObj = new Rating($pdo);

if (!isset($_GET['id'])) {
    redirect_error('404', 'ID de projet manquant.');
}
$projectId = (int) $_GET['id'];

$avgRating = $ratingObj->getAverageRating($projectId);
$ratingCount = $ratingObj->getRatingCount($projectId);
$userRating = isset($_SESSION['user']) ? $ratingObj->getUserRating($_SESSION['user']['id'], $projectId) : null;

$project    = $projectObj->getProjectById($projectId);
if (!$project) {
    redirect_error('404', 'Projet introuvable.');
}

$isFavorite = false;
if (isset($_SESSION['user'])) {
    $isFavorite = $favoriteObj->isFavorite($_SESSION['user']['id'], $projectId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_toggle'])) {
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
    if ($isFavorite) {
        $favoriteObj->remove($_SESSION['user']['id'], $projectId);
    } else {
        $favoriteObj->add($_SESSION['user']['id'], $projectId);
    }
    header("Location: project.php?id=$projectId&lang=$lang");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_submit'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        redirect_error('403', 'Action non autorisée (CSRF).');
    }
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
  <h1>
    <?= htmlspecialchars($project['title'], ENT_NOQUOTES, 'UTF-8', false) ?>
    <form method="post" style="display:inline;">
        <button type="submit" name="favorite_toggle" class="btn-favorite" title="<?= $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>">
            <?= $isFavorite ? '★' : '☆' ?>
        </button>
    </form>
    <?php if (isset($_SESSION['user'])): ?>
      <?php 
        $playlistObj = new App\Playlist($pdo);
        $userPlaylists = $playlistObj->getByUser($_SESSION['user']['id']);
      ?>
      <?php if (!empty($userPlaylists)): ?>
      <form method="post" action="playlist.php?id=<?= (int)($userPlaylists[0]['id']) ?>&lang=<?= $lang ?>" style="display:inline-block; margin-left: 10px;">
        <?php csrf_input(); ?>
        <select name="target_playlist_id" onchange="this.form.action='playlist.php?id='+this.value+'&lang=<?= $lang ?>';" style="padding:2px;">
          <?php foreach ($userPlaylists as $pl): ?>
            <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
        <button type="submit" name="add_item" class="btn-secondary btn-no-spinner" title="Ajouter à la liste">➕ Ajouter à la liste</button>
      </form>
      <?php endif; ?>
    <?php endif; ?>
  </h1>

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
    <?php if (isset($_SESSION['user'])): ?>
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
              alt="Image de <?= htmlspecialchars($project['title'], ENT_NOQUOTES, 'UTF-8', false) ?>"
              class="project-media">
          </a>
          <p style="text-align:center; margin-top:.5rem;"><a class="btn-secondary" href="<?= htmlspecialchars($url) ?>" download>Télécharger l'image</a></p>
        <?php elseif ($ext === 'pdf'): ?>
          <a href="javascript:void(0)" onclick="openPDF('<?= htmlspecialchars($url) ?>')">
            <img
              src="assets/static/file-pdf-solid.svg"
              alt="PDF de <?= htmlspecialchars($project['title'], ENT_NOQUOTES, 'UTF-8', false) ?>"
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
            <span class="download-count"><?= $downloadObj->getCountByProject($projectId) ?></span>
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
<?php include __DIR__ . '/includes/footer.php'; ?>
