<?php
// classes/Subscriber.php

namespace App;

use PDO;
use PDOException;

class Subscriber {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    
    public function insert(string $email): bool {

        $clean = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (!filter_var($clean, FILTER_VALIDATE_EMAIL)) {

            return false;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO subscribers (email)
            VALUES (:email)
        ");

        try {
            $stmt->bindValue(':email', $clean, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {

            if ($e->getCode() === '23000' || $e->getCode() === '19') {
                return false;
            }

            throw $e;
        }
    }
}
