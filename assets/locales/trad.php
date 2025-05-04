<?php
// assets/locales/trad.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$supported = ['fr','en'];

// 1) Si on vient avec un ?lang=xx valide...
if (isset($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;

    // **ON CRÉE/MISE À JOUR** du cookie pour 30 jours
    setcookie(
      'lang',
      $lang,
      [
        'expires'  => time() + 60*60*24*30,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
      ]
    );
}

// 2) Sinon on regarde si on a déjà un $_SESSION
$lang = $_SESSION['lang'] 
         ?? ($_COOKIE['lang'] ?? 'fr');

// 3) Sécurité : si jamais on trafique le cookie
if (!in_array($lang, $supported, true)) {
    $lang = 'fr';
    $_SESSION['lang'] = 'fr';
}

$transFile = __DIR__ . "/{$lang}.php";
if (!file_exists($transFile)) {
    $transFile = __DIR__ . "/fr.php";
}

$t = include $transFile;
