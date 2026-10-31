<?php

class TopicRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // SELECT * FROM topics
    public function getAll(): array
    {
        return $this->pdo->query('SELECT * FROM topics ORDER BY id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM topics WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function listByAuthor(string $author): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM topics WHERE author = :author ORDER BY id DESC');
        $stmt->execute([':author' => $author]);
        return $stmt->fetchAll();
    }

    public function listPinned(): array
    {
        return $this->pdo->query('SELECT * FROM topics WHERE is_pinned = 1 ORDER BY id DESC')->fetchAll();
    }

    // INSERT
    public function addTopic(string $title, string $author, bool $isPinned): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO topics (title, author, is_pinned) VALUES (:title, :author, :is_pinned)'
        );
        $stmt->execute([
            ':title' => $title,
            ':author' => $author,
            ':is_pinned' => $isPinned ? 1 : 0,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    // UPDATE
    public function updateTopic(int $id, string $title, string $author, bool $isPinned): int
    {
        $stmt = $this->pdo->prepare(
            'UPDATE topics SET title = :title, author = :author, is_pinned = :is_pinned WHERE id = :id'
        );
        $stmt->execute([
            ':title' => $title,
            ':author' => $author,
            ':is_pinned' => $isPinned ? 1 : 0,
            ':id' => $id,
        ]);
        return $stmt->rowCount();
    }

    // DELETE
    public function deleteTopic(int $id): int
    {
        $stmt = $this->pdo->prepare('DELETE FROM topics WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
}