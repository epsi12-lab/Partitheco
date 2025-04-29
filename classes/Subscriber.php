<?php
class Subscriber {
  private PDO $pdo;
  public function __construct(PDO $pdo){ $this->pdo=$pdo; }
  public function insert(string $email): bool {
    $stmt=$this->pdo->prepare("
      INSERT INTO subscribers (email) VALUES (:email)
    ");
    return $stmt->execute([
      ':email'=>filter_var($email,FILTER_VALIDATE_EMAIL)
        ? $email
        : die('Email invalide.')
    ]);
  }
}
