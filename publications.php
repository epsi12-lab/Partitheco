<?php
// publications.php
require_once __DIR__ . '/assets/locales/trad.php';

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Project.php';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <h1 class="fade-in-up" style="--delay:0.1s; text-align:center;">
    <?= htmlspecialchars($t['pages']['publications_title']) ?>
  </h1>

  <section class="animated-form">
    <?php $delay = 0.2; ?>
    <form id="searchForm" novalidate>
      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input
          type="text"
          id="searchInput"
          name="q"
          placeholder=" "
          autocomplete="off">
        <label for="searchInput">Rechercher une publication</label>
        <div class="form-line"></div>
      </div>
    </form>
  </section>

  <div id="projectsContainer"></div>
</main>

<script src="assets/js/base.js"></script>
<script src="assets/js/allProjects.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
