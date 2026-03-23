<?php
// classes/SubscriberRepository.php

namespace App;

use PDO;
use PDOException;

class SubscriberRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function insert(string $email): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO subscribers (email)
            VALUES (:email)
        ");

        try {
            return $stmt->execute([':email' => $email]);
        } catch (PDOException $e) {
            if (in_array($e->getCode(), ['23000', '19', '23505'], true)) {
                return false;
            }
            throw $e;
        }
    }
}
