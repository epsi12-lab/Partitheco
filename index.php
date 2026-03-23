<?php
// index.php - Page d'accueil Partithéco (Style Chantons en Église)
session_start(); 

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/assets/locales/trad.php';

use App\Database;
use App\CatalogService;
use App\ProjectRepository;
use App\Subscriber;

// Newsletter
$successNL = false;
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['newsletter_submit'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        redirect_error('403', 'Action non autorisée (CSRF).');
    }
    $sub       = new Subscriber((new Database())->getPDO());
    $successNL = $sub->insert($_POST['newsletter_email']);
}

$db         = new Database();
$pdo        = $db->getPDO();
$projectRepository = new ProjectRepository($pdo);
$catalogService = new CatalogService();
$homeData = $catalogService->buildHomePageData($projectRepository);
extract($homeData, EXTR_SKIP);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <!-- HERO SECTION -->
  <section class="hero-section">
    <div class="hero-content">
      <h1>Partithéco</h1>
      <p class="tagline">Votre bibliothèque de partitions liturgiques</p>
      
      <div class="hero-search">
        <form action="publications.php" method="get">
          <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
          <input type="text" name="q" placeholder="Rechercher un chant par titre, auteur...">
          
          <div class="search-filters-row">
            <select name="moment">
              <option value="">Moment de la messe</option>
              <?php foreach ($moments as $m): ?>
                <option value="<?= $m['slug'] ?>"><?= $m['nom'] ?></option>
              <?php endforeach; ?>
            </select>
            
            <select name="temps">
              <option value="">Temps liturgique</option>
              <?php foreach ($tempsLiturgiques as $temps): ?>
                <option value="<?= $temps['slug'] ?>"><?= $temps['nom'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <button type="submit" class="btn-search">🔍 Rechercher</button>
        </form>
      </div>
    </div>
  </section>

  <!-- NAVIGATION PAR MOMENTS LITURGIQUES -->
  <section class="moments-nav">
    <h2>Parcourir par moment de la célébration</h2>
    <div class="moments-grid">
      <?php foreach ($moments as $m): ?>
        <a href="publications.php?moment=<?= $m['slug'] ?>&lang=<?= $lang ?>" class="moment-card <?= $m['slug'] ?>">
          <span class="icon"><?= $m['icon'] ?></span>
          <span><?= $m['nom'] ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- CHANT DU JOUR -->
  <?php if ($chantDuJour): ?>
  <section class="chant-jour">
    <h2>🎶 Chant du jour</h2>
    <div class="chant-jour-card">
      <div class="titre"><?= htmlspecialchars($chantDuJour['title']) ?></div>
      <div class="auteur"><?= htmlspecialchars($chantDuJour['author'] ?? 'Auteur inconnu') ?></div>
      <div class="moment-tag">Partition</div>
      <div>
        <a href="project.php?id=<?= $chantDuJour['id'] ?>&lang=<?= $lang ?>" class="btn-voir-partition">
          📄 Voir la partition
        </a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- CALENDRIER LITURGIQUE -->
  <section class="calendar-section">
    <h2>📅 Préparer les prochaines célébrations</h2>
    <p class="subtitle">Temps liturgique actuel : <strong><?= htmlspecialchars($season) ?></strong></p>
    
    <div class="calendar-cards">
      <?php foreach ($prochainsDimanches as $dim): ?>
        <div class="calendar-card">
          <div class="date"><?= $dim['date'] ?></div>
          <div class="title">Dimanche</div>
          <span class="temps"><?= htmlspecialchars($dim['temps']) ?></span>
          <div>
            <a href="publications.php?temps=<?= strtolower($dim['temps']) ?>&lang=<?= $lang ?>" class="btn-voir">
              Voir les chants suggérés →
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- TEMPS LITURGIQUES -->
  <section class="temps-section">
    <h2>Explorer par temps liturgique</h2>
    <div class="temps-grid">
      <?php foreach ($tempsLiturgiques as $temps): ?>
        <a href="publications.php?temps=<?= $temps['slug'] ?>&lang=<?= $lang ?>" class="temps-card <?= $temps['slug'] ?>">
          <?= $temps['nom'] ?>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- DERNIÈRES PUBLICATIONS -->
  <section class="publications-section">
    <h2>📚 Dernières partitions ajoutées</h2>
    <div id="projectsContainer">
      <?php $i = 0; ?>
      <?php foreach ($projects as $project): ?>
        <?php
          $file    = $project['thumbnail'] ?? '';
          $ext     = strtolower(pathinfo($file, PATHINFO_EXTENSION));
          $fileUrl = "assets/img/{$file}";
          $detail  = "project.php?id={$project['id']}&lang={$lang}";
        ?>
        <div class="project-item" style="--order: <?= $i++ ?>">
          <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
            <a href="javascript:void(0)" onclick="openLightbox('<?= htmlspecialchars($fileUrl) ?>')">
              <img
                src="<?= htmlspecialchars($fileUrl) ?>"
                alt="<?= htmlspecialchars($project['title'], ENT_NOQUOTES, 'UTF-8', false) ?>"
                class="project-thumbnail">
            </a>
          <?php elseif ($ext === 'pdf'): ?>
            <a href="javascript:void(0)" onclick="openPDF('<?= htmlspecialchars($fileUrl) ?>')">
              <img
                src="/assets/static/file-pdf-solid.svg"
                alt="PDF : <?= htmlspecialchars($project['title'],ENT_NOQUOTES, 'UTF-8', false) ?>"
                class="project-thumbnail pdf-icon">
            </a>
          <?php endif; ?>

          <h3>
            <a href="<?= htmlspecialchars($detail) ?>">
              <?= htmlspecialchars($project['title'], ENT_NOQUOTES, 'UTF-8', false) ?>
            </a>
          </h3>
          <p>
            <?= nl2br(htmlspecialchars(substr($project['description'], 0, 80), ENT_NOQUOTES, 'UTF-8', false)) ?>…
          </p>
        </div>
      <?php endforeach; ?>
    </div>

    <button
      type="button"
      class="btn-load-more"
      onclick="location.href='publications.php?lang=<?= htmlspecialchars($lang) ?>'">
      Voir toutes les partitions →
    </button>
  </section>

  <!-- STATISTIQUES -->
  <section class="stats-section">
    <div class="stats-grid">
      <div class="stat-item">
        <span class="number"><?= $totalPartitions ?></span>
        <span class="label">Partitions disponibles</span>
      </div>
      <div class="stat-item">
        <span class="number"><?= count($moments) ?></span>
        <span class="label">Moments liturgiques</span>
      </div>
      <div class="stat-item">
        <span class="number"><?= count($tempsLiturgiques) ?></span>
        <span class="label">Temps liturgiques</span>
      </div>
    </div>
  </section>

  <!-- NEWSLETTER -->
  <section id="newsletter" class="animated-form centered-section">
    <h2 class="fade-in-up" style="--delay:0.1s;">
      <?= htmlspecialchars($t['newsletter']['title']) ?>
    </h2>

    <?php if (!empty($successNL)): ?>
      <p class="fade-in-up" style="--delay:0.2s;">
        <?= htmlspecialchars($t['newsletter']['success']) ?>
      </p>
    <?php else: ?>
      <?php $delay = 0.2; ?>
      <form method="post" class="newsletter-form" novalidate>
        <?php csrf_input(); ?>
        <div class="form-group" style="--delay:<?= $delay ?>s">
          <input
            type="email"
            id="newsletterEmail"
            name="newsletter_email"
            placeholder=" "
            required>
          <label for="newsletterEmail">
            <?= htmlspecialchars($t['form']['email']) ?>
          </label>
          <div class="form-line"></div>
        </div>

        <?php $delay += 0.1; ?>
        <button
          type="submit"
          name="newsletter_submit"
          class="form-btn btn-primary"
          style="--delay:<?= $delay ?>s;">
          <?= htmlspecialchars($t['buttons']['subscribe']) ?>
        </button>
      </form>
    <?php endif; ?>
  </section>

</main>

<script src="assets/js/base.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
