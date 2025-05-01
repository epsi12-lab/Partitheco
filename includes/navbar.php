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
<nav class="main_navbar">


  <div class="navbar-brand">
    <a href="index.php?lang=<?= htmlspecialchars($lang) ?>">
      <img
        src="assets/static/partitheco.gif"
        alt="Logo PARTITHéCO"
        class="navbar-logo">
    </a>
  </div>

  <ul class="navbar-menu">
    <li>
      <a href="index.php?lang=<?= htmlspecialchars($lang) ?>">
        <?= htmlspecialchars($t['nav']['home']) ?>
      </a>
    </li>
    <li>
      <a href="about.php?lang=<?= htmlspecialchars($lang) ?>">
        <?= htmlspecialchars($t['nav']['about']) ?>
      </a>
    </li>

    <?php if (isset($_SESSION['user'])): ?>
      <li>
        <a href="admin.php?lang=<?= htmlspecialchars($lang) ?>">
          <?= htmlspecialchars($t['nav']['admin']) ?>
        </a>
      </li>
      <li>
        <a href="logout.php?lang=<?= htmlspecialchars($lang) ?>">
          <?= htmlspecialchars($t['nav']['logout']) ?>
        </a>
      </li>
    <?php else: ?>
      <li class="dropdown">
        <span class="dropbtn">
          <?= htmlspecialchars($t['nav']['login']) ?>
        </span>
        <ul class="dropdown-content">
          <li>
            <a href="login.php?lang=<?= htmlspecialchars($lang) ?>">
              <?= htmlspecialchars($t['nav']['login']) ?>
            </a>
          </li>
          <li>
            <a href="signIn.php?lang=<?= htmlspecialchars($lang) ?>">
              <?= htmlspecialchars($t['nav']['register']) ?>
            </a>
          </li>
        </ul>
      </li>
    <?php endif; ?>
  </ul>

  <div class="navbar-lang">
    <a href="<?= htmlspecialchars($urlFr) ?>" aria-label="Passer en français">🇫🇷</a>
    <a href="<?= htmlspecialchars($urlEn) ?>" aria-label="Switch to English">🇬🇧</a>
  </div>
</nav>
