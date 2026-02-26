<?php
// classes/Comment.php

namespace App;

use PDO;

class Comment {
  private PDO $pdo;
  public function __construct(PDO $pdo) { $this->pdo = $pdo; }

  public function getByProject(int $pid): array {
    $stmt = $this->pdo->prepare("
      SELECT * FROM comments 
      WHERE project_id = :pid 
      ORDER BY created_at DESC
    ");
    $stmt->execute([':pid'=>$pid]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function insert(int $pid, string $author, string $content): bool {
    $stmt = $this->pdo->prepare("
      INSERT INTO comments (project_id, author, content)
      VALUES (:pid, :author, :content)
    ");
    return $stmt->execute([
      ':pid'=>$pid,
      ':author'=>$author,
      ':content'=>$content
    ]);
  }
}
