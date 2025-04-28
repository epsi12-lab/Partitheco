<?php
// contact.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$lang = $_GET['lang'] ?? 'fr';

require_once __DIR__ . '/assets/locales/trad.php';
$t = loadTranslations($lang);

$errors  = [];
$success = false;
$name    = $_POST['name']    ?? '';
$email   = $_POST['email']   ?? '';
$message = $_POST['message'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($name === '') {
        $errors[] = $t['contact']['error_name'];
    } elseif (strlen($name) > 100) {
        $errors[] = $t['contact']['error_name_length'];
    }
    if ($email === '') {
        $errors[] = $t['contact']['error_email'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $t['contact']['error_email_invalid'];
    }
    if ($message === '') {
        $errors[] = $t['contact']['error_message'];
    } elseif (strlen($message) > 1000) {
        $errors[] = $t['contact']['error_message_length'];
    }
    if (empty($errors)) {
        $success = true;
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<main>
  <section class="contact-section animated-form">
    <h1><?= htmlspecialchars($t['contact']['title']) ?></h1>

    <?php if ($success): ?>
      <p class="success-message"><?= htmlspecialchars($t['contact']['success']) ?></p>
    <?php else: ?>

      <?php if (!empty($errors)): ?>
        <div class="error-summary">
          <ul>
            <?php foreach ($errors as $err): ?>
              <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php $delay = 0.1; ?>

      <form id="contactForm"
            action="contact.php?lang=<?= htmlspecialchars($lang) ?>"
            method="post"
            novalidate>

        <div class="form-group" style="--delay: <?= $delay ?>s">
          <input type="text"
                 id="name"
                 name="name"
                 placeholder=" "
                 value="<?= htmlspecialchars($name) ?>"
                 required
                 maxlength="100">
          <label for="name"><?= htmlspecialchars($t['contact']['name']) ?></label>
          <div class="form-line"></div>
          <p class="error-message"></p>
        </div>

        <?php $delay += 0.1; ?>

        <div class="form-group" style="--delay: <?= $delay ?>s">
          <input type="email"
                 id="email"
                 name="email"
                 placeholder=" "
                 value="<?= htmlspecialchars($email) ?>"
                 required>
          <label for="email"><?= htmlspecialchars($t['contact']['email']) ?></label>
          <div class="form-line"></div>
          <p class="error-message"></p>
        </div>

        <?php $delay += 0.1; ?>

        <div class="form-group" style="--delay: <?= $delay ?>s">
          <textarea id="message"
                    name="message"
                    rows="4"
                    placeholder=" "
                    required
                    maxlength="1000"><?= htmlspecialchars($message) ?></textarea>
          <label for="message"><?= htmlspecialchars($t['contact']['message']) ?></label>
          <div class="form-line"></div>
          <p class="error-message"></p>
        </div>

        <?php $delay += 0.1; ?>

        <button type="submit" class="form-btn" style="--delay: <?= $delay ?>s">
          <?= htmlspecialchars($t['contact']['submit']) ?>
        </button>
      </form>
    <?php endif; ?>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
