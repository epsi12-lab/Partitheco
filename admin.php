<?php
// admin.php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$lang   = $_GET['lang'] ?? 'fr';
$userId = $_SESSION['user']['id'];

require_once 'classes/Database.php';
require_once 'classes/Project.php';

$db         = new Database();
$projectObj = new Project($db->getPDO());
$projects   = $projectObj->getByUser($userId);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<main class="admin-dashboard">
  <h1>Tableau de bord - Mes projets</h1>
  <p style="text-align: center; margin: 2rem 0;">
  <button
    type="button"
    class="btn-primary"
    onclick="location.href='create.php?lang=<?= htmlspecialchars($lang) ?>'"
  >
    Publier un nouveau projet
  </button>
</p>


  <?php if (empty($projects)): ?>
    <p>Vous n'avez encore publié aucun projet.</p>
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
              <?= htmlspecialchars($p['title']) ?>
            </a>
          </td>
          <td><?= htmlspecialchars($p['date_publication']) ?></td>
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
</main>

<?php include 'includes/footer.php'; ?>
