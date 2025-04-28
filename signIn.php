<?php
// signIn.php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($username)) {
        $error .= "Le nom d'utilisateur est requis.<br>";
    }
    if (empty($email)) {
        $error .= "L'adresse e-mail est requise.<br>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error .= "L'adresse e-mail n'est pas valide.<br>";
    }
    if (empty($password)) {
        $error .= "Le mot de passe est requis.<br>";
    }
    if ($password !== $password_confirm) {
        $error .= "Les mots de passe ne correspondent pas.<br>";
    }

    if (empty($error)) {
        $db = new Database();
        $userObj = new User($db->getPDO());
        try {
            $userObj->register($username, $email, $password);
            
            $user = $userObj->login($username, $password);
            if ($user) {
                $_SESSION['user'] = $user;
                header('Location: admin.php');
                exit;
            }
        } catch (Exception $e) {
            $error .= $e->getMessage();
        }
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>
<main>
    <h1>Créer un compte</h1>
    <?php if ($error): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    <form action="signIn.php" method="post">
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" required>
        
        <label for="email">Adresse e-mail :</label>
        <input type="email" id="email" name="email" required>
        
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
        
        <label for="password_confirm">Confirmer le mot de passe :</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
        
        <button type="submit">S'inscrire</button>
    </form>
</main>
<?php include 'includes/footer.php'; ?>
