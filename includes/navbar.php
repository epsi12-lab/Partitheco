<?php
// includes/navbar.php

$query = $_GET;

$query['lang'] = 'fr';
$urlFr = $_SERVER['PHP_SELF'] . '?' . http_build_query($query);
$query['lang'] = 'en';
$urlEn = $_SERVER['PHP_SELF'] . '?' . http_build_query($query);

if (!isset($lang)) {
  $lang = $_SESSION['lang'] ?? ($_GET['lang'] ?? 'fr');
}
?>
<nav class="main_navbar" role="navigation" aria-label="Main menu">


  <div class="navbar-brand">
    <a href="index.php?lang=<?= htmlspecialchars($lang) ?>">
      <img
        src="assets/static/partitheco.gif"
        alt="Logo PARTITHéCO"
        class="navbar-logo">
    </a>
  </div>

  <ul class="navbar-menu" id="navbarMenu">
    <li>
      <a href="index.php?lang=<?= $lang ?>" aria-label="<?= htmlspecialchars($t['nav']['home'] ?? 'Accueil') ?>">
        <?= htmlspecialchars($t['nav']['home'] ?? 'Accueil') ?>
      </a>
    </li>
    <li>
      <a href="publications.php?lang=<?= htmlspecialchars($lang) ?>">📚 Publications</a>
    </li>
    <li class="dropdown">
      <span class="dropbtn">🎵 Outils</span>
      <ul class="dropdown-content">
        <li><a href="calendrier.php?lang=<?= htmlspecialchars($lang) ?>">📅 Calendrier</a></li>
        <li><a href="livrets.php?lang=<?= htmlspecialchars($lang) ?>">📖 Livrets</a></li>
      </ul>
    </li>

    <?php if (isset($_SESSION['user'])): ?>
      <li>
        <a href="admin.php?lang=<?= htmlspecialchars($lang) ?>">
          <?= htmlspecialchars($t['nav']['admin'] ?? 'Tableau de bord') ?>
        </a>
      </li>
      <li>
        <a href="logout.php?lang=<?= htmlspecialchars($lang) ?>">
          <?= htmlspecialchars($t['nav']['logout'] ?? 'Déconnexion') ?>
        </a>
      </li>
    <?php else: ?>
      <li class="dropdown">
        <span class="dropbtn">
          <?= htmlspecialchars($t['nav']['login'] ?? 'Connexion') ?>
        </span>
        <ul class="dropdown-content">
          <li>
            <a href="login.php?lang=<?= htmlspecialchars($lang) ?>">
              <?= htmlspecialchars($t['nav']['login'] ?? 'Connexion') ?>
            </a>
          </li>
          <li>
            <a href="signIn.php?lang=<?= htmlspecialchars($lang) ?>">
              <?= htmlspecialchars($t['nav']['register'] ?? "S'inscrire") ?>
            </a>
          </li>
        </ul>
      </li>
    <?php endif; ?>
  </ul>

  <div class="navbar-lang">
    <button class="theme-toggle" id="themeToggle" title="Changer de thème">
      <span class="sun">☀️</span>
      <span class="moon">🌙</span>
    </button>
    <a href="<?= htmlspecialchars($urlFr) ?>" aria-label="Passer en français">🇫🇷</a>
    <a href="<?= htmlspecialchars($urlEn) ?>" aria-label="Switch to English">🇬🇧</a>
  </div>

  <button class="burger-menu" id="burgerMenu" aria-label="Ouvrir le menu">
    ☰
  </button>
</nav>
