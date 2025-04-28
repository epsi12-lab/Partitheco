<?php
// create.php

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$lang = $_GET['lang'] ?? 'fr';

require_once 'classes/Database.php';
require_once 'classes/Project.php';

$errors      = [];
$title       = $_POST['title']       ?? '';
$description = $_POST['description'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Upload du fichier
    $thumbnail = '';
    if (!empty($_FILES['file']['tmp_name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'assets/img/';
        $original  = basename($_FILES['file']['name']);
        $target    = $uploadDir . time() . '_' . $original;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $thumbnail = basename($target);
        } else {
            $errors[] = 'Erreur lors de l’upload du fichier.';
        }
    } else {
        $errors[] = 'Le fichier est requis.';
    }

    if ($title === '' || strlen($title) > 100) {
        $errors[] = 'Le titre doit contenir entre 1 et 100 caractères.';
    }
    if ($description === '') {
        $errors[] = 'La description est obligatoire.';
    }

    if (empty($errors)) {
        try {
            $db         = new Database();
            $projectObj = new Project($db->getPDO());
            $userId     = $_SESSION['user']['id'];
            $projectObj->insert($userId, $title, $description, $thumbnail);

            header("Location: admin.php?lang={$lang}");
            exit;
        } catch (Exception $e) {
            $errors[] = 'Erreur base de données : ' . $e->getMessage();
        }
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<main>
  <section class="animated-form create-section">
    <h1>Publier un nouveau projet</h1>

    <?php if (!empty($errors)): ?>
      <div class="error-summary">
        <ul>
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php $delay = 0.1; ?>

    <form id="uploadForm"
          action="create.php?lang=<?= htmlspecialchars($lang) ?>"
          method="post"
          enctype="multipart/form-data"
          novalidate>

      <div class="form-group" style="--delay: <?= $delay ?>s">
        <input type="text"
               id="title"
               name="title"
               placeholder=" "
               value="<?= htmlspecialchars($title) ?>"
               required
               maxlength="100">
        <label for="title">Titre</label>
        <div class="form-line"></div>
        <p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay: <?= $delay ?>s">
        <textarea id="description"
                  name="description"
                  rows="4"
                  placeholder=" "
                  required><?= htmlspecialchars($description) ?></textarea>
        <label for="description">Description</label>
        <div class="form-line"></div>
        <p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay: <?= $delay ?>s">
        <input type="file"
               id="file"
               name="file"
               placeholder=" "
               required>
        <label for="file">Fichier (PDF, image, vidéo, etc.)</label>
        <div class="form-line"></div>
        <p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <button type="submit" class="form-btn" style="--delay: <?= $delay ?>s">
        Publier
      </button>
    </form>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
