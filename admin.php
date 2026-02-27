<?php
// admin.php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$lang   = $_GET['lang'] ?? 'fr';
$userId = $_SESSION['user']['id'];

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/assets/locales/trad.php';

use App\Database;
use App\Project;
use App\Favorite;
use App\User;
use App\Playlist;

$db          = new Database();
$pdo         = $db->getPDO();
$projectObj  = new Project($pdo);
$favoriteObj = new Favorite($pdo);
$userObj     = new User($pdo);
$playlistObj = new Playlist($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $paroisse = trim($_POST['paroisse'] ?? '');
    $role     = trim($_POST['role_choral'] ?? '');
    $userObj->updateProfile($userId, $paroisse, $role);
    $_SESSION['user']['paroisse'] = $paroisse;
    $_SESSION['user']['role_choral'] = $role;
    header("Location: admin.php?lang=$lang");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_playlist'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        redirect_error('403', 'Action non autorisée (CSRF).');
    }
    $name = trim($_POST['playlist_name'] ?? '');
    $date = trim($_POST['event_date'] ?? null) ?: null;
    if ($name !== '') {
        $playlistObj->create($userId, $name, null, $date);
        header("Location: admin.php?lang=$lang");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_playlist'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        redirect_error('403', 'Action non autorisée (CSRF).');
    }
    $plId = (int) $_POST['playlist_id'];
    $playlistObj->delete($plId, $userId);
    header("Location: admin.php?lang=$lang");
    exit;
}

$projects  = $projectObj->getByUser($userId);
$favorites = $favoriteObj->getByUser($userId);
$playlists = $playlistObj->getByUser($userId);
$userData  = $userObj->findByUsername($_SESSION['user']['username']);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<main class="admin-dashboard">
  <h1><?= htmlspecialchars($t['pages']['admin_title']) ?> (<?= htmlspecialchars($userData['username']) ?>)</h1>

  <section class="profile-section" style="max-width: 600px; margin: 2rem auto; padding: 1rem; border: 1px solid var(--border-color);">
    <h2>Mon Profil</h2>
    <p style="margin-bottom:1rem;"><strong><?= htmlspecialchars(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? '')) ?></strong> (<?= htmlspecialchars($userData['email'] ?? '') ?>)</p>
    <form method="post" class="profile-form">
        <?php csrf_input(); ?>
        <div class="form-group" style="--delay:0.1s">
            <input type="text" name="paroisse" id="paroisse" placeholder=" " value="<?= htmlspecialchars($userData['paroisse'] ?? '') ?>">
            <label for="paroisse">Paroisse / Chorale</label>
        </div>
        <div class="form-group" style="--delay:0.2s">
            <input type="text" name="role_choral" id="role_choral" placeholder=" " value="<?= htmlspecialchars($userData['role_choral'] ?? '') ?>">
            <label for="role_choral">Rôle (ex: Chef de chœur, Soprano...)</label>
        </div>
        <button type="submit" name="update_profile" class="btn-primary">Mettre à jour le profil</button>
    </form>
  </section>

  <p style="text-align: center; margin: 2rem 0;">
  <button
    type="button"
    class="btn-primary"
    onclick="location.href='create.php?lang=<?= htmlspecialchars($lang) ?>'"
  >
  Publier une nouvelle partition
  </button>
</p>

  <h2>Mes Publications</h2>
  <?php if (empty($projects)): ?>
    <p>Vous n'avez encore publié aucune partition.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Titre</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($projects as $p): ?>
        <tr>
          <td>
            <a href="project.php?id=<?= $p['id'] ?>&lang=<?= $lang ?>">
              <?= htmlspecialchars($p['title'], ENT_NOQUOTES, 'UTF-8', false) ?>
            </a>
          </td>
          <td><?= (new DateTime($p['date_publication']))->format('d/m/Y') ?></td>
          <td>
            <a href="edit.php?id=<?= $p['id'] ?>&lang=<?= $lang ?>">Modifier</a> |
            <a href="delete.php?id=<?= $p['id'] ?>&lang=<?= $lang ?>"
               onclick="return confirm('Voulez-vous vraiment supprimer ce projet ?');">
              Supprimer
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <h2 style="margin-top: 3rem;">Mes Listes (Ma Chorale)</h2>
  <section class="playlists-section" style="max-width: 800px; margin: 2rem auto; padding: 1rem; border: 1px solid var(--border-color); background: var(--card-bg);">
    <form method="post" style="display:flex; gap:10px; margin-bottom:1.5rem; align-items: flex-end;">
        <?php csrf_input(); ?>
        <div class="form-group" style="margin-bottom:0; flex:1;">
            <input type="text" name="playlist_name" id="playlist_name" placeholder=" " required>
            <label for="playlist_name">Nom de la célébration (ex: Messe du 15 Août)</label>
        </div>
        <div class="form-group" style="margin-bottom:0; width:150px;">
            <input type="date" name="event_date" id="event_date">
        </div>
        <button type="submit" name="create_playlist" class="btn-primary" style="padding: 0.5rem 1rem;">Créer</button>
    </form>

    <?php if (empty($playlists)): ?>
        <p>Aucune liste de chants créée.</p>
    <?php else: ?>
        <ul style="list-style:none; padding:0;">
            <?php foreach ($playlists as $pl): ?>
                <li style="display:flex; justify-content:space-between; align-items:center; padding:10px; border-bottom:1px solid var(--border-color);">
                    <div>
                        <strong><?= htmlspecialchars($pl['name']) ?></strong>
                        <?php if ($pl['event_date']): ?>
                            <span style="font-size:0.8rem; color:#666;"> - <?= (new DateTime($pl['event_date']))->format('d/m/Y') ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <a href="playlist.php?id=<?= $pl['id'] ?>&lang=<?= $lang ?>" class="btn-secondary" style="font-size:0.8rem;">Gérer les chants</a>
                        <form method="post" onsubmit="return confirm('Supprimer cette liste ?');">
                            <?php csrf_input(); ?>
                            <input type="hidden" name="playlist_id" value="<?= $pl['id'] ?>">
                            <button type="submit" name="delete_playlist" class="btn-danger" style="font-size:0.8rem; background:transparent; color:red; border:none; cursor:pointer;">Supprimer</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
  </section>

  <h2 style="margin-top: 3rem;">Mes Favoris</h2>
  <?php if (empty($favorites)): ?>
    <p>Vous n'avez aucune partition en favoris.</p>
  <?php else: ?>
    <div id="projectsContainer">
        <?php foreach ($favorites as $f): ?>
            <div class="project-item">
                <h3><a href="project.php?id=<?= $f['id'] ?>&lang=<?= $lang ?>"><?= htmlspecialchars($f['title']) ?></a></h3>
                <p>Par <?= htmlspecialchars($f['publisher']) ?></p>
                <?php if (!empty($playlists)): ?>
                <form method="post" action="playlist.php?id=<?= (int)($playlists[0]['id']) ?>&lang=<?= $lang ?>" style="margin-top:5px;">
                  <?php csrf_input(); ?>
                  <select name="target_playlist_id" onchange="this.form.action='playlist.php?id='+this.value+'&lang=<?= $lang ?>';" style="padding:2px; font-size:0.8rem;">
                    <?php foreach ($playlists as $pl): ?>
                      <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <input type="hidden" name="project_id" value="<?= (int) $f['id'] ?>">
                  <button type="submit" name="add_item" class="btn-secondary btn-no-spinner" style="font-size:0.8rem;">➕ Liste</button>
                </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
