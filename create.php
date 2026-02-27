<?php
// create.php
require_once __DIR__ . '/assets/locales/trad.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/assets/locales/trad.php';

use App\Database;
use App\Project;

$errors      = [];
$title       = $_POST['title']       ?? '';
$description = $_POST['description'] ?? '';
$author      = $_POST['author']      ?? '';
$arranger    = $_POST['arranger']    ?? '';
$genre       = $_POST['genre']       ?? '';
$tonality    = $_POST['tonality']    ?? '';
$moment      = $_POST['moment_messe'] ?? null;
$temps       = $_POST['temps_liturgique'] ?? null;
$voix        = $_POST['voix']        ?? null;
$is_lit      = isset($_POST['is_liturgical']) ? 1 : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        redirect_error('403', 'Action non autorisée (CSRF).');
    }
    $thumbnail = '';
    if (!empty($_FILES['file']['tmp_name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['file']['tmp_name']);

        if (!in_array($mime, $allowedMimes)) {
            $errors[] = 'Type de fichier non autorisé (Seuls JPG, PNG, GIF et PDF sont acceptés).';
        } else {
            $fn     = basename($_FILES['file']['name']);
            $tgt    = 'assets/img/' . time() . "_{$fn}";
            if (move_uploaded_file($_FILES['file']['tmp_name'], $tgt)) {
                $thumbnail = basename($tgt);
            } else {
                $errors[] = 'Erreur lors de l’upload du fichier.';
            }
        }
    } else {
        $errors[] = 'Le fichier est requis.';
    }

    $media = null;
    if (!empty($_FILES['media']['tmp_name']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $allowedMediaMimes = [
            'audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/x-wav',
            'video/mp4', 'video/webm', 'video/ogg'
        ];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['media']['tmp_name']);

        if (!in_array($mime, $allowedMediaMimes)) {
            $errors[] = 'Type de média non autorisé (MP3, WAV, OGG, MP4, WEBM acceptés).';
        } else {
            $fn  = basename($_FILES['media']['name']);
            $tgt = 'assets/img/' . time() . "_media_{$fn}";
            if (move_uploaded_file($_FILES['media']['tmp_name'], $tgt)) {
                $media = basename($tgt);
            } else {
                $errors[] = 'Erreur lors de l’upload du média.';
            }
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
            $genre, $tonality,
            $moment, $temps, $is_lit, $voix
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
  <section class="animated-form create-section" aria-labelledby="create-title">
    <h1><?= htmlspecialchars($t['pages']['create_title']) ?></h1>

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
      <?php csrf_input(); ?>

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
        <label for="description"><?= htmlspecialchars($t['form']['description']) ?> :</label>
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
        <select id="moment_messe" name="moment_messe" class="lit-select">
            <option value="">-- Moment de la Messe --</option>
            <option value="Entrée" <?= $moment === 'Entrée' ? 'selected' : '' ?>>🚪 Entrée</option>
            <option value="Kyrie" <?= $moment === 'Kyrie' ? 'selected' : '' ?>>🙏 Kyrie</option>
            <option value="Gloria" <?= $moment === 'Gloria' ? 'selected' : '' ?>>✨ Gloria</option>
            <option value="Psaume" <?= $moment === 'Psaume' ? 'selected' : '' ?>>📖 Psaume</option>
            <option value="Acclamation" <?= $moment === 'Acclamation' ? 'selected' : '' ?>>🎵 Acclamation</option>
            <option value="Credo" <?= $moment === 'Credo' ? 'selected' : '' ?>>✝️ Credo</option>
            <option value="Offertoire" <?= $moment === 'Offertoire' ? 'selected' : '' ?>>🍞 Offertoire</option>
            <option value="Sanctus" <?= $moment === 'Sanctus' ? 'selected' : '' ?>>👼 Sanctus</option>
            <option value="Agnus Dei" <?= $moment === 'Agnus Dei' ? 'selected' : '' ?>>🐑 Agnus Dei</option>
            <option value="Communion" <?= $moment === 'Communion' ? 'selected' : '' ?>>🍷 Communion</option>
            <option value="Envoi" <?= $moment === 'Envoi' ? 'selected' : '' ?>>🕊️ Envoi</option>
            <option value="Marie" <?= $moment === 'Marie' ? 'selected' : '' ?>>💙 Chants à Marie</option>
        </select>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <select id="temps_liturgique" name="temps_liturgique" class="lit-select">
            <option value="">-- Temps Liturgique --</option>
            <option value="Avent" <?= $temps === 'Avent' ? 'selected' : '' ?>>Avent</option>
            <option value="Noël" <?= $temps === 'Noël' ? 'selected' : '' ?>>Noël</option>
            <option value="Carême" <?= $temps === 'Carême' ? 'selected' : '' ?>>Carême</option>
            <option value="Pâques" <?= $temps === 'Pâques' ? 'selected' : '' ?>>Pâques</option>
            <option value="Ordinaire" <?= $temps === 'Ordinaire' ? 'selected' : '' ?>>Ordinaire</option>
        </select>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <select id="voix" name="voix" class="lit-select">
            <option value="">-- Type de voix --</option>
            <option value="Unisson" <?= $voix === 'Unisson' ? 'selected' : '' ?>>Unisson</option>
            <option value="2 voix" <?= $voix === '2 voix' ? 'selected' : '' ?>>2 voix</option>
            <option value="3 voix" <?= $voix === '3 voix' ? 'selected' : '' ?>>3 voix</option>
            <option value="SATB" <?= $voix === 'SATB' ? 'selected' : '' ?>>SATB (4 voix)</option>
            <option value="Solo" <?= $voix === 'Solo' ? 'selected' : '' ?>>Solo</option>
        </select>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <label>
            <input type="checkbox" name="is_liturgical" value="1" <?= $is_lit ? 'checked' : '' ?>>
            Chant Liturgique
        </label>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input type="file" id="file" name="file" placeholder=" ">
        <label for="file"><?= htmlspecialchars($t['form']['file']) ?> :</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input type="file" id="media" name="media" placeholder=" ">
        <label for="media">Vidéo / Audio</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <div class="progress-container" id="uploadProgressContainer">
        <div class="progress-bar" id="uploadProgressBar"></div>
        <div class="progress-text" id="uploadProgressText">0%</div>
      </div>

      <?php $delay += 0.1; ?>

      <button type="submit" class="form-btn" style="--delay:<?= $delay ?>s">
        <?= htmlspecialchars($t['buttons']['publish']) ?>
      </button>
    </form>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
