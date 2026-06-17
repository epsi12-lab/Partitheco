<?php
// create.php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../lang/trad.php';

use App\Database;
use App\ProjectFormService;
use App\ProjectRepository;

require_auth_redirect();

$db = new Database();
$projectRepository = new ProjectRepository($db->getPDO());
$formService = new ProjectFormService($projectRepository);
$formData = $formService->getEmptyFormData($_POST);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_fail($_POST['csrf_token'] ?? null);
    $result = $formService->prepareFromRequest($_POST, $_FILES);
    $formData = $result['data'];
    $errors = $result['errors'];

    if (empty($errors)) {
        $userId     = $_SESSION['user']['id'];

        if ($formService->create($userId, $formData)) {
            header("Location: admin.php?lang={$lang}");
            exit;
        } else {
            $errors[] = 'Erreur base de donnees a l insertion.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
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
               placeholder=" " value="<?= htmlspecialchars($formData['title']) ?>"
               required maxlength="100">
        <label for="title">Titre</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <textarea id="description" name="description"
                  rows="4" placeholder=" "
                  required><?= htmlspecialchars($formData['description']) ?></textarea>
        <label for="description"><?= htmlspecialchars($t['form']['description']) ?> :</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input type="text" id="author" name="author"
               placeholder=" " value="<?= htmlspecialchars($formData['author']) ?>">
        <label for="author">Auteur</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input type="text" id="arranger" name="arranger"
               placeholder=" " value="<?= htmlspecialchars($formData['arranger']) ?>">
        <label for="arranger">Arrangeur</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input type="text" id="genre" name="genre"
               placeholder=" " value="<?= htmlspecialchars($formData['genre']) ?>">
        <label for="genre">Genre</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input type="text" id="tonality" name="tonality"
               placeholder=" " value="<?= htmlspecialchars($formData['tonality']) ?>">
        <label for="tonality">Tonalité</label>
        <div class="form-line"></div><p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <select id="moment_messe" name="moment_messe" class="lit-select">
            <option value="">-- Moment de la Messe --</option>
            <option value="Entrée" <?= $formData['moment_messe'] === 'Entrée' ? 'selected' : '' ?>>🚪 Entrée</option>
            <option value="Kyrie" <?= $formData['moment_messe'] === 'Kyrie' ? 'selected' : '' ?>>🙏 Kyrie</option>
            <option value="Gloria" <?= $formData['moment_messe'] === 'Gloria' ? 'selected' : '' ?>>✨ Gloria</option>
            <option value="Psaume" <?= $formData['moment_messe'] === 'Psaume' ? 'selected' : '' ?>>📖 Psaume</option>
            <option value="Acclamation" <?= $formData['moment_messe'] === 'Acclamation' ? 'selected' : '' ?>>🎵 Acclamation</option>
            <option value="Credo" <?= $formData['moment_messe'] === 'Credo' ? 'selected' : '' ?>>✝️ Credo</option>
            <option value="Offertoire" <?= $formData['moment_messe'] === 'Offertoire' ? 'selected' : '' ?>>🍞 Offertoire</option>
            <option value="Sanctus" <?= $formData['moment_messe'] === 'Sanctus' ? 'selected' : '' ?>>👼 Sanctus</option>
            <option value="Agnus Dei" <?= $formData['moment_messe'] === 'Agnus Dei' ? 'selected' : '' ?>>🐑 Agnus Dei</option>
            <option value="Communion" <?= $formData['moment_messe'] === 'Communion' ? 'selected' : '' ?>>🍷 Communion</option>
            <option value="Envoi" <?= $formData['moment_messe'] === 'Envoi' ? 'selected' : '' ?>>🕊️ Envoi</option>
            <option value="Marie" <?= $formData['moment_messe'] === 'Marie' ? 'selected' : '' ?>>💙 Chants à Marie</option>
        </select>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <select id="temps_liturgique" name="temps_liturgique" class="lit-select">
            <option value="">-- Temps Liturgique --</option>
            <option value="Avent" <?= $formData['temps_liturgique'] === 'Avent' ? 'selected' : '' ?>>Avent</option>
            <option value="Noël" <?= $formData['temps_liturgique'] === 'Noël' ? 'selected' : '' ?>>Noël</option>
            <option value="Carême" <?= $formData['temps_liturgique'] === 'Carême' ? 'selected' : '' ?>>Carême</option>
            <option value="Pâques" <?= $formData['temps_liturgique'] === 'Pâques' ? 'selected' : '' ?>>Pâques</option>
            <option value="Ordinaire" <?= $formData['temps_liturgique'] === 'Ordinaire' ? 'selected' : '' ?>>Ordinaire</option>
        </select>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <select id="voix" name="voix" class="lit-select">
            <option value="">-- Type de voix --</option>
            <option value="Unisson" <?= $formData['voix'] === 'Unisson' ? 'selected' : '' ?>>Unisson</option>
            <option value="2 voix" <?= $formData['voix'] === '2 voix' ? 'selected' : '' ?>>2 voix</option>
            <option value="3 voix" <?= $formData['voix'] === '3 voix' ? 'selected' : '' ?>>3 voix</option>
            <option value="SATB" <?= $formData['voix'] === 'SATB' ? 'selected' : '' ?>>SATB (4 voix)</option>
            <option value="Solo" <?= $formData['voix'] === 'Solo' ? 'selected' : '' ?>>Solo</option>
        </select>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay:<?= $delay ?>s">
        <label>
            <input type="checkbox" name="is_liturgical" value="1" <?= $formData['is_liturgical'] ? 'checked' : '' ?>>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
