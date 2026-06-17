<?php
// classes/Comment.php

declare(strict_types=1);

namespace App;

use PDO;

class Comment {
  private CommentRepository $repository;
  public function __construct(PDO $pdo) { $this->repository = new CommentRepository($pdo); }

  public function getByProject(int $pid): array {
    return $this->repository->getByProject($pid);
  }

  public function insert(int $pid, string $author, string $content): bool {
    return $this->repository->insert($pid, $author, $content);
  }
}
