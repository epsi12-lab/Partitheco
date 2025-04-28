<?php
// edit.php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$projectId = (int) ($_GET['id'] ?? 0);
$lang      = $_GET['lang'] ?? 'fr';
$userId    = $_SESSION['user']['id'];

require_once 'classes/Database.php';
require_once 'classes/Project.php';

$db         = new Database();
$projectObj = new Project($db->getPDO());
$project    = $projectObj->getProjectById($projectId);

if (!$project || $project['user_id'] != $userId) {
    die("Accès refusé.");
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $thumb       = $project['thumbnail'];

    if (!empty($_FILES['file']['tmp_name'])) {
        $fn        = basename($_FILES['file']['name']);
        $target    = 'assets/img/' . time() . "_$fn";
        move_uploaded_file($_FILES['file']['tmp_name'], $target);
        $thumb     = basename($target);
    }

    if (empty($title) || strlen($title) > 100) {
        $errors[] = "Titre invalide.";
    }
    if (empty($description)) {
        $errors[] = "Description requise.";
    }

    if (empty($errors)) {
        $projectObj->updateByUser($projectId, $userId, $title, $description, $thumb);
        header("Location: admin.php?lang=$lang");
        exit;
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>
<main>
  <h1>Modifier le projet</h1>
  <?php if ($errors): ?>
    <ul style="color:red;">
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form action="edit.php?id=<?= $projectId ?>&lang=<?= $lang ?>" method="post" enctype="multipart/form-data">
    <label for="title">Titre :</label>
    <input type="text" name="title" id="title"
           value="<?= htmlspecialchars($project['title']) ?>" required maxlength="100">

    <label for="description">Description :</label>
    <textarea name="description" id="description" required><?= htmlspecialchars($project['description']) ?></textarea>

    <p>Miniature actuelle :</p>
    <?php if ($project['thumbnail']): ?>
      <img src="assets/img/<?= htmlspecialchars($project['thumbnail']) ?>" alt="" style="max-width:200px;">
    <?php endif; ?>

    <label for="file">Changer le fichier :</label>
    <input type="file" name="file" id="file">

    <button type="submit">Enregistrer</button>
  </form>
</main>
<?php include 'includes/footer.php'; ?>
