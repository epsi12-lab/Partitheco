<?php
// login.php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $db      = new Database();
    $userObj = new User($db->getPDO());
    $user    = $userObj->login($username, $password);

    if ($user) {
        $_SESSION['user'] = $user;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Identifiants invalides.";
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<main>
  <section class="animated-form contact-section">
    <h1><?= htmlspecialchars($t['nav']['login']) ?></h1>

    <?php if ($error): ?>
      <div class="error-summary">
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
      </div>
    <?php endif; ?>

    <?php $delay = 0.1; ?>

    <form action="login.php" method="post" novalidate>

      <div class="form-group" style="--delay: <?= $delay ?>s">
        <input
          type="text"
          id="username"
          name="username"
          placeholder=" "
          required>
        <label for="username">Nom d’utilisateur</label>
        <div class="form-line"></div>
        <p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <div class="form-group" style="--delay: <?= $delay ?>s">
        <input
          type="password"
          id="password"
          name="password"
          placeholder=" "
          required>
        <label for="password"><?= htmlspecialchars($t['form']['password']) ?> :</label>
        <div class="form-line"></div>
        <p class="error-message"></p>
      </div>

      <?php $delay += 0.1; ?>

      <button type="submit" class="form-btn" style="--delay: <?= $delay ?>s">
        <?= htmlspecialchars($t['nav']['login']) ?>
      </button>
    </form>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
