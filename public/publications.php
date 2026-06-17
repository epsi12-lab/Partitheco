<?php
// publications.php - Page des publications (Style Chantons en Église)
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../lang/trad.php';

use App\Database;
use App\CatalogService;
use App\ProjectRepository;

$db = new Database();
$pdo = $db->getPDO();
$projectRepository = new ProjectRepository($pdo);
$catalogService = new CatalogService();

// Récupérer les paramètres de filtrage
$searchQuery = $_GET['q'] ?? '';
$momentFilter = $_GET['moment'] ?? '';
$tempsFilter = $_GET['temps'] ?? '';
$voixFilter = $_GET['voix'] ?? '';

$moments = $catalogService->getPublicationMoments();
$tempsLiturgiques = $catalogService->getTempsLiturgiques();
$voixOptions = $catalogService->getVoixOptions();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<main class="publications-page">
  <!-- En-tête de la page -->
  <section class="publications-header">
    <h1>📚 Bibliothèque de Partitions</h1>
    <p class="subtitle">Explorez notre collection de chants liturgiques</p>
  </section>

  <!-- Filtres de recherche avancés -->
  <section class="search-section">
    <form id="searchForm" action="publications.php" method="get" class="search-form-advanced">
      <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
      
      <div class="search-main">
        <input 
          type="text" 
          id="searchInput" 
          name="q" 
          placeholder="Rechercher par titre, compositeur, auteur..."
          value="<?= htmlspecialchars($searchQuery) ?>"
          autocomplete="off">
        <button type="submit" class="btn-search-main">🔍</button>
      </div>

      <div class="filters-grid">
        <div class="filter-group">
          <label for="momentFilter">Moment de la messe</label>
          <select id="momentFilter" name="moment">
            <option value="">Tous les moments</option>
            <?php foreach ($moments as $m): ?>
              <option value="<?= $m['slug'] ?>" <?= $momentFilter === $m['slug'] ? 'selected' : '' ?>>
                <?= $m['icon'] ?> <?= $m['nom'] ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-group">
          <label for="tempsFilter">Temps liturgique</label>
          <select id="tempsFilter" name="temps">
            <option value="">Tous les temps</option>
            <?php foreach ($tempsLiturgiques as $t): ?>
              <option value="<?= $t['slug'] ?>" <?= $tempsFilter === $t['slug'] ? 'selected' : '' ?>>
                <?= $t['nom'] ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-group">
          <label for="voixFilter">Type de voix</label>
          <select id="voixFilter" name="voix">
            <option value="">Toutes les voix</option>
            <?php foreach ($voixOptions as $v): ?>
              <option value="<?= $v['slug'] ?>" <?= $voixFilter === $v['slug'] ? 'selected' : '' ?>><?= $v['nom'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="filter-actions">
        <button type="submit" class="btn-filter">Appliquer les filtres</button>
        <a href="publications.php?lang=<?= $lang ?>" class="btn-reset">Réinitialiser</a>
      </div>
    </form>
  </section>

  <!-- Recherche dans les paroles -->
  <section class="search-section lyrics-search-section">
    <h3>Rechercher dans les paroles</h3>
    <div class="search-main">
      <input
        type="text"
        id="lyricsSearchInput"
        placeholder="Rechercher un mot ou une phrase dans les paroles..."
        autocomplete="off">
      <button type="button" id="lyricsSearchBtn" class="btn-search-main">🔍</button>
    </div>
    <div id="lyricsSearchResults"></div>
  </section>

  <!-- Navigation rapide par moments -->
  <section class="quick-nav">
    <h3>Accès rapide par moment</h3>
    <div class="quick-nav-pills">
      <?php foreach ($moments as $m): ?>
        <a href="publications.php?moment=<?= $m['slug'] ?>&lang=<?= $lang ?>" 
           class="pill <?= $momentFilter === $m['slug'] ? 'active' : '' ?>">
          <?= $m['icon'] ?> <?= $m['nom'] ?>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Résultats -->
  <section class="results-section">
    <?php if ($searchQuery || $momentFilter || $tempsFilter): ?>
      <div class="results-info">
        <span>Résultats pour : </span>
        <?php if ($searchQuery): ?>
          <span class="filter-tag">« <?= htmlspecialchars($searchQuery) ?> »</span>
        <?php endif; ?>
        <?php if ($momentFilter): ?>
          <span class="filter-tag"><?= htmlspecialchars($momentFilter) ?></span>
        <?php endif; ?>
        <?php if ($tempsFilter): ?>
          <span class="filter-tag"><?= htmlspecialchars($tempsFilter) ?></span>
        <?php endif; ?>
        <?php if ($voixFilter): ?>
          <span class="filter-tag"><?= htmlspecialchars($voixFilter) ?></span>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div id="projectsContainer">
      <?php for ($s = 0; $s < 6; $s++): ?>
        <div class="skeleton-card">
          <div class="skeleton skeleton-img"></div>
          <div class="skeleton skeleton-title"></div>
          <div class="skeleton skeleton-text"></div>
          <div class="skeleton skeleton-text" style="width:60%"></div>
        </div>
      <?php endfor; ?>
    </div>
  </section>
</main>

<script src="assets/js/base.js"></script>
<script src="assets/js/allProjects.js"></script>
<script src="assets/js/lyrics-search.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
