<?php
// classes/User.php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register($username, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindParam(':username', htmlspecialchars($username, ENT_QUOTES, 'UTF-8'), PDO::PARAM_STR);
        $stmt->bindParam(':email', htmlspecialchars($email, ENT_QUOTES, 'UTF-8'), PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function login($username, $password) {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, password
            FROM users
            WHERE username = :username
            LIMIT 1
        ");
        $stmt->bindValue(':username', htmlspecialchars($username, ENT_QUOTES, 'UTF-8'), PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return false;
    }

    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email
            FROM users
            WHERE username = :username
            LIMIT 1
        ");
        $stmt->bindValue(':username', htmlspecialchars($username, ENT_QUOTES, 'UTF-8'), PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
