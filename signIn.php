<?php
// signIn.php
session_start();

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/User.php';

$error = '';
$username = $_POST['username'] ?? '';
$email    = $_POST['email']    ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username         = trim($_POST['username']);
    $email            = trim($_POST['email']);
    $password         = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($username === '') {
        $error .= "Le nom d'utilisateur est requis.<br>";
    }
    if ($email === '') {
        $error .= "L'adresse e-mail est requise.<br>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error .= "L'adresse e-mail n'est pas valide.<br>";
    }
    if ($password === '') {
        $error .= "Le mot de passe est requis.<br>";
    }
    if ($password !== $password_confirm) {
        $error .= "Les mots de passe ne correspondent pas.<br>";
    }

    if ($error === '') {
        $db      = new Database();
        $userObj = new User($db->getPDO());
        try {
            $userObj->register($username, $email, $password);
            $user = $userObj->login($username, $password);
            if ($user) {
                $_SESSION['user'] = $user;
                header('Location: admin.php');
                exit;
            }
        } catch (\Exception $e) {
            $error .= htmlspecialchars($e->getMessage()) . '<br>';
        }
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <section class="animated-form">
    <h1 class="fade-in-up" style="--delay:0.1s;">
        <?= htmlspecialchars($t['nav']['register']) ?>
    </h1>
    <?php if ($error): ?>
      <div class="error-summary" style="max-width:400px; margin:1rem auto; color:red;">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <?php $delay = 0.2; ?>
    <form action="signIn.php" method="post" novalidate>
      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input
          type="text"
          id="username"
          name="username"
          placeholder=" "
          required
          value="<?= htmlspecialchars($username) ?>">
        
        <label for="username"><?= htmlspecialchars($t['form']['name']) ?> :</label>
        <div class="form-line"></div>
      </div>

      <?php $delay += 0.1; ?>
      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input
          type="email"
          id="email"
          name="email"
          placeholder=" "
          required
          value="<?= htmlspecialchars($email) ?>">
        <label for="email"><?= htmlspecialchars($t['form']['email']) ?> :</label>
        <div class="form-line"></div>
      </div>

      <?php $delay += 0.1; ?>
      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input
          type="password"
          id="password"
          name="password"
          placeholder=" "
          required>
        <label for="password"><?= htmlspecialchars($t['form']['password']) ?> :</label>
        <div class="form-line"></div>
      </div>

      <?php $delay += 0.1; ?>
      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input
          type="password"
          id="password_confirm"
          name="password_confirm"
          placeholder=" "
          required>
        <label for="password_confirm">
            <?= htmlspecialchars($t['form']['password_confirm']) ?>
        </label>
        <div class="form-line"></div>
      </div>

      <?php $delay += 0.1; ?>
      <div class="form-group" style="--delay:<?= $delay ?>s">
        <button type="submit" class="btn-primary form-btn">
            <?= htmlspecialchars($t['nav']['register']) ?>
        </button>
      </div>
    </form>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
