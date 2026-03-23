<?php
// signIn.php
session_start();

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/assets/locales/trad.php';

use App\AuthService;
use App\Database;
use App\RegistrationService;
use App\User;

$error = '';
$firstName = $_POST['first_name'] ?? '';
$lastName  = $_POST['last_name'] ?? '';
$username  = $_POST['username'] ?? '';
$email     = $_POST['email']    ?? '';
$paroisse  = $_POST['paroisse'] ?? '';
$roleChoral = $_POST['role_choral'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        redirect_error('403', 'Action non autorisée (CSRF).');
    }
    $db = new Database();
    $pdo = $db->getPDO();
    $registrationService = new RegistrationService(new User($pdo), new AuthService($pdo));
    $result = $registrationService->registerFromInput($_POST);

    $firstName = $result['data']['first_name'];
    $lastName = $result['data']['last_name'];
    $username = $result['data']['username'];
    $email = $result['data']['email'];
    $paroisse = $result['data']['paroisse'];
    $roleChoral = $result['data']['role_choral'];

    if ($result['success']) {
        header('Location: admin.php');
        exit;
    }

    $error = implode('<br>', array_map('htmlspecialchars', $result['errors']));
    if ($error !== '') {
        $error .= '<br>';
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
      <?php csrf_input(); ?>
      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input
          type="text"
          id="first_name"
          name="first_name"
          placeholder=" "
          required
          value="<?= htmlspecialchars($firstName) ?>">
        <label for="first_name">Prénom :</label>
        <div class="form-line"></div>
      </div>

      <?php $delay += 0.1; ?>
      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input
          type="text"
          id="last_name"
          name="last_name"
          placeholder=" "
          required
          value="<?= htmlspecialchars($lastName) ?>">
        <label for="last_name">Nom :</label>
        <div class="form-line"></div>
      </div>

      <?php $delay += 0.1; ?>
      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input
          type="text"
          id="username"
          name="username"
          placeholder=" "
          required
          value="<?= htmlspecialchars($username) ?>">
        <label for="username">Nom d'utilisateur :</label>
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
        <label for="password"><?= htmlspecialchars($t['form']['password'] ?? 'Mot de passe') ?> :</label>
        <button type="button" class="toggle-password" onclick="togglePassword('password')">👁️</button>
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
            <?= htmlspecialchars($t['form']['password_confirm'] ?? 'Confirmer le mot de passe') ?>
        </label>
        <button type="button" class="toggle-password" onclick="togglePassword('password_confirm')">👁️</button>
        <div class="form-line"></div>
      </div>

      <?php $delay += 0.1; ?>
      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input
          type="text"
          id="paroisse"
          name="paroisse"
          placeholder=" "
          value="<?= htmlspecialchars($paroisse) ?>">
        <label for="paroisse">Paroisse / Chorale (optionnel)</label>
        <div class="form-line"></div>
      </div>

      <?php $delay += 0.1; ?>
      <div class="form-group" style="--delay:<?= $delay ?>s">
        <input
          type="text"
          id="role_choral"
          name="role_choral"
          placeholder=" "
          value="<?= htmlspecialchars($roleChoral) ?>">
        <label for="role_choral">Rôle (ex: Chef de chœur, Soprano...)</label>
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
<?php include __DIR__ . '/includes/footer.php'; ?>
