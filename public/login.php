<?php
// login.php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../lang/trad.php';
use App\AuthService;
use App\Database;
use App\LoginThrottle;

if (isset($_SESSION['user'])) {
  header('Location: admin.php');
  exit;
}

if (empty($_SESSION['user']) && !empty($_COOKIE['remember_token'])) {
  $db = new Database();
  $authService = new AuthService($db->getPDO());

  $user = $authService->loginFromRememberToken($_COOKIE['remember_token']);
  if ($user) {
     $authService->startAuthenticatedSession($user);
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
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $db       = new Database();
    $throttle = new LoginThrottle($db->getPDO());

    if ($throttle->isLocked($username, $ip)) {
        $error = "Trop de tentatives de connexion. Réessayez dans quelques minutes.";
    } else {
        $authService = new AuthService($db->getPDO());
        $user    = $authService->login($username, $password);

        if ($user) {
            $throttle->clear($username, $ip);
            $authService->startAuthenticatedSession($user);

            if ($rememberMe) {
              $rememberToken = $authService->issueRememberToken((int) $user['id']);
              setcookie(
                  'remember_token',
                  $rememberToken,
                  [
                    'expires'  => time() + 60 * 60 * 24 * 30,
                    'path'     => '/',
                    'secure'   => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax',
                  ]
              );
            } elseif (!empty($_COOKIE['remember_token'])) {
              $authService->revokeRememberToken($_COOKIE['remember_token']);
              setcookie('remember_token', '', time() - 3600, '/');
            }
            header('Location: admin.php');
            exit;
        } else {
            $throttle->recordFailure($username, $ip);
            $error = "Identifiants invalides.";
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
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
        <label for="password"><?= htmlspecialchars($t['form']['password'] ?? 'Mot de passe') ?> :</label>
        <button type="button" class="toggle-password" onclick="togglePassword('password')">👁️</button>
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

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const btn = field.parentElement.querySelector('.toggle-password');
    if (field.type === 'password') {
        field.type = 'text';
        btn.textContent = '🙈';
    } else {
        field.type = 'password';
        btn.textContent = '👁️';
    }
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
