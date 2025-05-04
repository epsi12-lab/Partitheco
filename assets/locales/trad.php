<?php
// assets/locales/trad.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$supported = ['fr','en'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;

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

$lang = $_SESSION['lang'] 
         ?? ($_COOKIE['lang'] ?? 'fr');

if (!in_array($lang, $supported, true)) {
    $lang = 'fr';
    $_SESSION['lang'] = 'fr';
}

$transFile = __DIR__ . "/{$lang}.php";
if (!file_exists($transFile)) {
    $transFile = __DIR__ . "/fr.php";
}

$t = include $transFile;
