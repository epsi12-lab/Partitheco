<?php
// error.php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/assets/locales/trad.php';

$code = $_GET['code'] ?? '500';
$message = "Une erreur inattendue est survenue.";

if ($code === '403') {
    $message = "Accès refusé.";
} elseif ($code === '404') {
    $message = "Page ou ressource introuvable.";
} elseif (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
    <section class="centered-section" style="padding: 5rem 1rem; text-align: center;">
        <h1 style="font-size: 3rem; color: var(--text-action);"><?= htmlspecialchars($code) ?></h1>
        <p style="font-size: 1.2rem; margin-top: 1rem;"><?= htmlspecialchars($message) ?></p>
        <div style="margin-top: 2rem;">
            <a href="index.php?lang=<?= htmlspecialchars($lang) ?>" class="btn-primary" style="text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 4px;">
                Retour à l'accueil
            </a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
