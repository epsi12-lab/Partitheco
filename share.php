<?php
// share.php — Vue publique (lecture seule) d'une liste de chants partagée
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/assets/locales/trad.php';

use App\Database;
use App\Playlist;

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    redirect_error('404', 'Lien de partage invalide.');
}

$db          = new Database();
$pdo         = $db->getPDO();
$playlistObj = new Playlist($pdo);

$playlist = $playlistObj->getByShareToken($token);
if (!$playlist) {
    redirect_error('404', 'Liste introuvable ou lien expiré.');
}

$items = $playlistObj->getItems($playlist['id']);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="admin-dashboard">
  <h1>🎵 <?= htmlspecialchars($playlist['name']) ?>
    <?php if ($playlist['event_date']): ?>
      — <small><?= (new DateTime($playlist['event_date']))->format('d/m/Y') ?></small>
    <?php endif; ?>
  </h1>
  <p style="text-align:center; opacity:0.7;">Liste partagée en lecture seule</p>

  <?php if (empty($items)): ?>
    <p style="text-align:center;">Cette liste ne contient aucun chant.</p>
  <?php else: ?>
    <section style="max-width: 900px; margin: 1rem auto;">
      <table style="width:100%; border-collapse: collapse;">
        <thead>
          <tr>
            <th style="text-align:left; padding:8px; border-bottom:2px solid var(--border-color);">#</th>
            <th style="text-align:left; padding:8px; border-bottom:2px solid var(--border-color);">Titre</th>
            <th style="text-align:left; padding:8px; border-bottom:2px solid var(--border-color);">Moment</th>
            <th style="text-align:left; padding:8px; border-bottom:2px solid var(--border-color);">Note</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $idx => $it): ?>
            <tr>
              <td style="padding:8px;"><?= $idx + 1 ?></td>
              <td style="padding:8px;">
                <a href="project.php?id=<?= $it['id'] ?>&lang=<?= $lang ?>">
                  <?= htmlspecialchars($it['title']) ?>
                </a>
                <?php if (!empty($it['author'])): ?>
                  <br><small style="opacity:0.6;"><?= htmlspecialchars($it['author']) ?></small>
                <?php endif; ?>
              </td>
              <td style="padding:8px;"><?= htmlspecialchars($it['moment_messe'] ?? '') ?></td>
              <td style="padding:8px;"><?= htmlspecialchars($it['note'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <p style="text-align:center; margin-top:2rem;">
      <button type="button" class="btn-primary" onclick="window.print();">🖨️ Imprimer cette liste</button>
    </p>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
