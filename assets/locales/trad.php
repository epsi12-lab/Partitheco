<?php
// assets/locales/trad.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$supported = ['fr', 'en'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'] ?? 'fr';
if (!in_array($lang, $supported, true)) {
    $lang = 'fr';
}
$_SESSION['lang'] = $lang;

$transFile = __DIR__ . "/{$lang}.php";
if (!file_exists($transFile)) {
    $transFile = __DIR__ . "/fr.php";
}

$t = include $transFile;
