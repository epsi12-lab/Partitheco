<?php
// create.php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$lang        = $_GET['lang'] ?? 'fr';
require_once 'classes/Database.php';
require_once 'classes/Project.php';

$errors      = [];
$title       = $_POST['title']       ?? '';
$description = $_POST['description'] ?? '';
$author      = $_POST['author']      ?? null;
$arranger    = $_POST['arranger']    ?? null;
$genre       = $_POST['genre']       ?? null;
$tonality    = $_POST['tonality']    ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $thumbnail = '';
    if (!empty($_FILES['file']['tmp_name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fn     = basename($_FILES['file']['name']);
        $tgt    = 'assets/img/' . time() . "_{$fn}";
        if (move_uploaded_file($_FILES['file']['tmp_name'], $tgt)) {
            $thumbnail = basename($tgt);
        } else {
            $errors[] = 'Erreur lors de l’upload du fichier.';
        }
    } else {
        $errors[] = 'Le fichier est requis.';
    }

    $media = null;
    if (!empty($_FILES['media']['tmp_name']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $fn  = basename($_FILES['media']['name']);
        $tgt = 'assets/img/' . time() . "_media_{$fn}";
        if (move_uploaded_file($_FILES['media']['tmp_name'], $tgt)) {
            $media = basename($tgt);
        } else {
            $errors[] = 'Erreur lors de l’upload du média.';
        }
    }

    if ($title === '' || strlen($title) > 100) {
        $errors[] = 'Le titre doit contenir entre 1 et 100 caractères.';
    }
    if ($description === '') {
        $errors[] = 'La description est obligatoire.';
    }

    if (empty($errors)) {
        $db         = new Database();
        $projectObj = new Project($db->getPDO());
        $userId     = $_SESSION['user']['id'];

        if ($projectObj->insert(
            $userId, $title, $description,
            $thumbnail, $media,
            $author, $arranger,
            $genre, $tonality
        )) {
            header("Location: admin.php?lang={$lang}");
            exit;
        } else {
            $errors[] = 'Erreur base de données à l’insertion.';
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
      <div class="error-summary"><ul>
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul></div>
    <?php endif; ?>

    <?php $delay = 0.1; ?>
    <form action="create.php?lang=<?= htmlspecialchars($lang) ?>"
          method="post"
          enctype="multipart/form-data"
          novalidate>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input type="text" id="title" name="title"
               placeholder=" " value="<?= htmlspecialchars($title) ?>"
               required maxlength="100">
        <label for="title">Titre</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <textarea id="description" name="description"
                  rows="4" placeholder=" "
                  required><?= htmlspecialchars($description) ?></textarea>
        <label for="description">Description</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input type="text" id="author" name="author"
               placeholder=" " value="<?= htmlspecialchars($author) ?>">
        <label for="author">Auteur</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input type="text" id="arranger" name="arranger"
               placeholder=" " value="<?= htmlspecialchars($arranger) ?>">
        <label for="arranger">Arrangeur</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input type="text" id="genre" name="genre"
               placeholder=" " value="<?= htmlspecialchars($genre) ?>">
        <label for="genre">Genre</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input type="text" id="tonality" name="tonality"
               placeholder=" " value="<?= htmlspecialchars($tonality) ?>">
        <label for="tonality">Tonalité</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input type="file" id="file" name="file" placeholder=" ">
        <label for="file">Fichier (PDF, image…)</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input type="file" id="media" name="media" placeholder=" ">
        <label for="media">Vidéo / Audio</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <button type="submit" class="form-btn" style="--delay:<?= $delay ?>s">
        Publier
      </button>
    </form>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
