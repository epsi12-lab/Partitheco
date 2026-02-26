<?php
// publications.php - Page des publications (Style Chantons en Église)
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/assets/locales/trad.php';

use App\Database;
use App\Project;

$db = new Database();
$pdo = $db->getPDO();
$projectObj = new Project($pdo);

// Récupérer les paramètres de filtrage
$searchQuery = $_GET['q'] ?? '';
$momentFilter = $_GET['moment'] ?? '';
$tempsFilter = $_GET['temps'] ?? '';

// Moments de la messe
$moments = [
    ['slug' => 'entree', 'nom' => 'Entrée', 'icon' => '🚪'],
    ['slug' => 'kyrie', 'nom' => 'Kyrie', 'icon' => '🙏'],
    ['slug' => 'gloria', 'nom' => 'Gloria', 'icon' => '✨'],
    ['slug' => 'psaume', 'nom' => 'Psaume', 'icon' => '📖'],
    ['slug' => 'acclamation', 'nom' => 'Acclamation', 'icon' => '🎵'],
    ['slug' => 'credo', 'nom' => 'Credo', 'icon' => '✝️'],
    ['slug' => 'offertoire', 'nom' => 'Offertoire', 'icon' => '🍞'],
    ['slug' => 'sanctus', 'nom' => 'Sanctus', 'icon' => '👼'],
    ['slug' => 'agnus', 'nom' => 'Agnus Dei', 'icon' => '🐑'],
    ['slug' => 'communion', 'nom' => 'Communion', 'icon' => '🍷'],
    ['slug' => 'envoi', 'nom' => 'Envoi', 'icon' => '🕊️'],
    ['slug' => 'marie', 'nom' => 'Chants à Marie', 'icon' => '💙'],
];

// Temps liturgiques
$tempsLiturgiques = [
    ['slug' => 'avent', 'nom' => 'Avent'],
    ['slug' => 'noel', 'nom' => 'Noël'],
    ['slug' => 'careme', 'nom' => 'Carême'],
    ['slug' => 'paques', 'nom' => 'Pâques'],
    ['slug' => 'ordinaire', 'nom' => 'Temps Ordinaire'],
];

// Types de voix
$voixOptions = [
    ['slug' => 'unisson', 'nom' => 'Unisson'],
    ['slug' => 'satb', 'nom' => 'SATB'],
    ['slug' => 'solo', 'nom' => 'Solo'],
    ['slug' => '2voix', 'nom' => '2 voix'],
    ['slug' => '3voix', 'nom' => '3 voix'],
];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
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
              <option value="<?= $v['slug'] ?>"><?= $v['nom'] ?></option>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
