<?php
// includes/navbar.php
if (!isset($lang)) {
    $lang = isset($_GET['lang']) ? $_GET['lang'] : 'fr';
}
?>
<nav class="main_navbar">

  <div class="navbar-brand">
    <a href="index.php?lang=<?= $lang ?>">
      <img src="assets/static/partitheco.gif"
           alt="Logo Mon Partitheco"
           class="navbar-logo">
    </a>
  </div>

  <ul class="navbar-menu">
    <li>
      <a href="index.php?lang=<?= $lang ?>">Accueil</a>
    </li>

    <?php if (isset($_SESSION['user'])): ?>
      <li>
        <a href="admin.php?lang=<?= $lang ?>">Admin</a>
      </li>
      <li>
        <a href="logout.php?lang=<?= $lang ?>">Déconnexion</a>
      </li>
    <?php else: ?>
      <li class="dropdown">
        <a href="#" class="dropbtn">Connexion</a>
        <ul class="dropdown-content">
          <li>
            <a href="login.php?lang=<?= $lang ?>">Se connecter</a>
          </li>
          <li>
            <a href="signIn.php?lang=<?= $lang ?>">S'inscrire</a>
          </li>
        </ul>
      </li>
    <?php endif; ?>

     <li><a href="about.php?lang=<?= $lang ?>">À propos de nous</a></li>
     
  </ul>

  <div class="navbar-lang">
    <a href="?lang=fr">🇫🇷</a>
    <a href="?lang=en">🇬🇧</a>
  </div>
</nav>
