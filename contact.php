<?php
// contact.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/assets/locales/trad.php';

use App\ContactFormService;

$errors  = [];
$success = false;
$name    = $_POST['name']    ?? '';
$email   = $_POST['email']   ?? '';
$message = $_POST['message'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        redirect_error('403', 'Action non autorisée (CSRF).');
    }
    $contactFormService = new ContactFormService();
    $result = $contactFormService->validate($_POST, $t['contact']);
    $errors = $result['errors'];
    $success = $result['success'];
    $name = $result['data']['name'];
    $email = $result['data']['email'];
    $message = $result['data']['message'];
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<main>
  <section class="contact-section animated-form" aria-labelledby="contact-title">
    <h1><?= htmlspecialchars($t['pages']['contact_title']) ?></h1>

    <?php if ($success): ?>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          showToast("<?= addslashes($t['contact']['success']) ?>", 'success');
        });
      </script>
      <p class="success-message"><?= htmlspecialchars($t['contact']['success']) ?></p>
    <?php else: ?>

      <?php if (!empty($errors)): ?>
        <script>
          document.addEventListener('DOMContentLoaded', () => {
            <?php foreach ($errors as $err): ?>
              showToast("<?= addslashes($err) ?>", 'error');
            <?php endforeach; ?>
          });
        </script>
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
            action="https://formspree.io/f/xreajwyp"
            method="post"
            novalidate>
        <?php csrf_input(); ?>

        <div class="form-group" style="--delay: <?= $delay ?>s">
          <input type="text"
                 id="name"
                 name="name"
                 placeholder=" "
                 value="<?= htmlspecialchars($name) ?>"
                 required
                 maxlength="100">
          <label for="name"><?= htmlspecialchars($t['form']['name']) ?></label>
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
          <label for="email"><?= htmlspecialchars($t['form']['email']) ?></label>
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
          <label for="message"><?= htmlspecialchars($t['form']['message']) ?></label>
          <div class="form-line"></div>
          <p class="error-message"></p>
        </div>

        <?php $delay += 0.1; ?>

        <button type="submit" class="form-btn" style="--delay: <?= $delay ?>s">
          <?= htmlspecialchars($t['buttons']['submit']) ?>
        </button>
      </form>
    <?php endif; ?>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
