<?php
// classes/Subscriber.php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Subscriber {
    private SubscriberRepository $repository;

    public function __construct(PDO $pdo) {
        $this->repository = new SubscriberRepository($pdo);
    }

    
    public function insert(string $email): bool {
        $clean = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($clean, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return $this->repository->insert($clean);
    }
}
