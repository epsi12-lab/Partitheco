<?php
// login.php
session_start();
require_once __DIR__ . '/bootstrap.php';
use App\Database;
use App\User;


if (isset($_SESSION['user'])) {
  header('Location: admin.php');
  exit;
}

if (empty($_SESSION['user']) && !empty($_COOKIE['remember_user'])) {
  $db = new Database();
  $u  = new User($db->getPDO());
  
  $user = $u->findByUsername($_COOKIE['remember_user']);
  if ($user) {
     $_SESSION['user'] = $user;
     header('Location: admin.php');
     exit;
  }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        redirect_error('403', 'Action non autorisée (CSRF).');
    }
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $rememberMe   = !empty($_POST['remember_me']);

    $db      = new Database();
    $userObj = new User($db->getPDO());
    $user    = $userObj->login($username, $password);

    if ($user) {
        $_SESSION['user'] = $user;

        if ($rememberMe) {
          setcookie(
              'remember_user',
              $user['username'],
              [
                'expires'  => time() + 60*60, // 1 heure
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
              ]
          );
        }
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
  <section class="animated-form contact-section" aria-labelledby="login-title">
    <h1><?= htmlspecialchars($t['nav']['login']) ?></h1>

    <?php if ($error): ?>
      <div class="error-summary">
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
      </div>
    <?php endif; ?>

    <?php $delay = 0.1; ?>

    <form action="login.php" method="post" novalidate>
      <?php csrf_input(); ?>

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
      <label>
        <input type="checkbox" name="remember_me"> 
        <?= htmlspecialchars($t['nav']['remember_me']) ?>   
      </label>
      <button type="submit" class="form-btn" style="--delay: <?= $delay ?>s">
        <?= htmlspecialchars($t['nav']['login']) ?>
      </button>
    </form>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
