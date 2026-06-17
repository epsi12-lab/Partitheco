<?php
// admin.php

$lang = $_GET['lang'] ?? 'fr';

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../lang/trad.php';

use App\Database;
use App\AdminDashboardService;

require_auth_redirect();

$userId = $_SESSION['user']['id'];

$db          = new Database();
$pdo         = $db->getPDO();
$dashboardService = new AdminDashboardService($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    verify_csrf_or_fail($_POST['csrf_token'] ?? null);
    $dashboardService->updateProfile(
        $_SESSION['user'],
        $_POST['paroisse'] ?? '',
        $_POST['role_choral'] ?? ''
    );
    header("Location: admin.php?lang=$lang");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_playlist'])) {
    verify_csrf_or_fail($_POST['csrf_token'] ?? null);
    if ($dashboardService->createPlaylist(
        $userId,
        $_POST['playlist_name'] ?? '',
        $_POST['event_date'] ?? null
    )) {
        header("Location: admin.php?lang=$lang");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_playlist'])) {
    verify_csrf_or_fail($_POST['csrf_token'] ?? null);
    $dashboardService->deletePlaylist((int) $_POST['playlist_id'], $userId);
    header("Location: admin.php?lang=$lang");
    exit;
}

$dashboardData = $dashboardService->buildDashboardData($_SESSION['user']);
$projects  = $dashboardData['projects'];
$favorites = $dashboardData['favorites'];
$playlists = $dashboardData['playlists'];
$userData  = $dashboardData['userData'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<main class="admin-dashboard">
  <h1><?= htmlspecialchars($t['pages']['admin_title'] ?? 'Tableau de bord') ?> (<?= htmlspecialchars($userData['username']) ?>)</h1>

  <section class="animated-form admin-section">
    <h2><?= htmlspecialchars($t['admin']['my_profile'] ?? 'Mon Profil') ?></h2>
    <p class="profile-info"><strong><?= htmlspecialchars(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? '')) ?></strong> (<?= htmlspecialchars($userData['email'] ?? '') ?>)</p>
    <form method="post" class="profile-form">
        <?php csrf_input(); ?>
        <?php $delay = 0.1; ?>
        <div class="form-group" style="--delay:<?= $delay ?>s">
            <input type="text" name="paroisse" id="paroisse" placeholder=" " value="<?= htmlspecialchars($userData['paroisse'] ?? '') ?>">
            <label for="paroisse"><?= htmlspecialchars($t['form']['paroisse'] ?? 'Paroisse / Chorale') ?></label>
            <div class="form-line"></div>
        </div>
        <?php $delay += 0.1; ?>
        <div class="form-group" style="--delay:<?= $delay ?>s">
            <input type="text" name="role_choral" id="role_choral" placeholder=" " value="<?= htmlspecialchars($userData['role_choral'] ?? '') ?>">
            <label for="role_choral"><?= htmlspecialchars($t['form']['role_choral'] ?? 'Rôle (ex: Chef de chœur, Soprano...)') ?></label>
            <div class="form-line"></div>
        </div>
        <?php $delay += 0.1; ?>
        <button type="submit" name="update_profile" class="btn-primary form-btn" style="--delay:<?= $delay ?>s"><?= htmlspecialchars($t['buttons']['update_profile'] ?? 'Mettre à jour le profil') ?></button>
    </form>
  </section>

  <p class="text-center">
    <button type="button" class="btn-primary" onclick="location.href='create.php?lang=<?= htmlspecialchars($lang) ?>'">
      <?= htmlspecialchars($t['buttons']['new_publication'] ?? 'Publier une nouvelle partition') ?>
    </button>
  </p>

  <h2><?= htmlspecialchars($t['admin']['my_publications'] ?? 'Mes Publications') ?></h2>
  <?php if (empty($projects)): ?>
    <p><?= htmlspecialchars($t['admin']['no_publications'] ?? 'Vous n\'avez encore publié aucune partition.') ?></p>
  <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th><?= htmlspecialchars($t['table']['title'] ?? 'Titre') ?></th>
          <th><?= htmlspecialchars($t['table']['date'] ?? 'Date') ?></th>
          <th><?= htmlspecialchars($t['table']['actions'] ?? 'Actions') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($projects as $p): ?>
        <tr>
          <td data-label="<?= htmlspecialchars($t['table']['title'] ?? 'Titre') ?>">
            <a href="project.php?id=<?= $p['id'] ?>&lang=<?= $lang ?>">
              <?= htmlspecialchars($p['title'], ENT_NOQUOTES, 'UTF-8', false) ?>
            </a>
          </td>
          <td data-label="<?= htmlspecialchars($t['table']['date'] ?? 'Date') ?>"><?= (new DateTime($p['date_publication']))->format('d/m/Y') ?></td>
          <td data-label="<?= htmlspecialchars($t['table']['actions'] ?? 'Actions') ?>" class="admin-publications-actions">
            <a href="edit.php?id=<?= $p['id'] ?>&lang=<?= $lang ?>"><?= htmlspecialchars($t['buttons']['edit'] ?? 'Modifier') ?></a> |
            <form method="post" action="delete.php?lang=<?= htmlspecialchars($lang) ?>" class="admin-delete-inline" onsubmit="return confirm('<?= htmlspecialchars($t['confirm']['delete_project'] ?? 'Voulez-vous vraiment supprimer ce projet ?') ?>');">
              <?php csrf_input(); ?>
              <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <button type="submit" class="btn-link"><?= htmlspecialchars($t['buttons']['delete'] ?? 'Supprimer') ?></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <h2><?= htmlspecialchars($t['admin']['my_playlists'] ?? 'Mes Listes (Ma Chorale)') ?></h2>
  <section class="animated-form admin-section">
    <form method="post" class="playlist-form">
        <?php csrf_input(); ?>
        <div class="form-group" style="--delay:0.1s; flex:1;">
            <input type="text" name="playlist_name" id="playlist_name" placeholder=" " required>
            <label for="playlist_name"><?= htmlspecialchars($t['form']['playlist_name'] ?? 'Nom de la célébration (ex: Messe du 15 Août)') ?></label>
            <div class="form-line"></div>
        </div>
        <div class="form-group playlist-form-date" style="--delay:0.2s;">
            <input type="date" name="event_date" id="event_date" placeholder=" ">
            <label for="event_date"><?= htmlspecialchars($t['form']['event_date'] ?? 'Date') ?></label>
            <div class="form-line"></div>
        </div>
        <button type="submit" name="create_playlist" class="btn-primary"><?= htmlspecialchars($t['buttons']['create'] ?? 'Créer') ?></button>
    </form>

    <?php if (empty($playlists)): ?>
        <p><?= htmlspecialchars($t['admin']['no_playlists'] ?? 'Aucune liste de chants créée.') ?></p>
    <?php else: ?>
        <ul class="playlist-list">
            <?php foreach ($playlists as $pl): ?>
                <li class="playlist-item">
                    <div>
                        <strong><?= htmlspecialchars($pl['name']) ?></strong>
                        <?php if ($pl['event_date']): ?>
                            <span class="playlist-date"> - <?= (new DateTime($pl['event_date']))->format('d/m/Y') ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="playlist-actions">
                        <a href="playlist.php?id=<?= $pl['id'] ?>&lang=<?= $lang ?>" class="btn-secondary btn-sm"><?= htmlspecialchars($t['buttons']['manage_songs'] ?? 'Gérer les chants') ?></a>
                        <form method="post" onsubmit="return confirm('<?= htmlspecialchars($t['confirm']['delete_playlist'] ?? 'Supprimer cette liste ?') ?>');">
                            <?php csrf_input(); ?>
                            <input type="hidden" name="playlist_id" value="<?= $pl['id'] ?>">
                            <button type="submit" name="delete_playlist" class="btn-danger btn-sm"><?= htmlspecialchars($t['buttons']['delete'] ?? 'Supprimer') ?></button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
  </section>

  <h2><?= htmlspecialchars($t['admin']['my_favorites'] ?? 'Mes Favoris') ?></h2>
  <?php if (empty($favorites)): ?>
    <p><?= htmlspecialchars($t['admin']['no_favorites'] ?? 'Vous n\'avez aucune partition en favoris.') ?></p>
  <?php else: ?>
    <div id="projectsContainer">
        <?php foreach ($favorites as $f): ?>
            <div class="project-item">
                <h3><a href="project.php?id=<?= $f['id'] ?>&lang=<?= $lang ?>"><?= htmlspecialchars($f['title']) ?></a></h3>
                <p>Par <?= htmlspecialchars($f['publisher']) ?></p>
                <?php if (!empty($playlists)): ?>
                <form method="post" action="playlist.php?id=<?= (int)($playlists[0]['id']) ?>&lang=<?= $lang ?>" class="favorite-add-form">
                  <?php csrf_input(); ?>
                  <select name="target_playlist_id" class="favorite-add-select" onchange="this.form.action='playlist.php?id='+this.value+'&lang=<?= $lang ?>';">
                    <?php foreach ($playlists as $pl): ?>
                      <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <input type="hidden" name="project_id" value="<?= (int) $f['id'] ?>">
                  <button type="submit" name="add_item" class="btn-secondary btn-no-spinner favorite-add-button">➕ Liste</button>
                </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
