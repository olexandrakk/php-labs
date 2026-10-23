<?php

require_once __DIR__ . '/Topic.php';
require_once __DIR__ . '/PinnedTopic.php';

class Forum
{
    /** @var Topic[] */
    private array $topics = [];

    public function addTopic(Topic $topic): void
    {
        $this->topics[] = $topic;
    }

    /** @return Topic[] */
    public function getAll(): array
    {
        return $this->topics;
    }

    /** @return Topic[] */
    public function listByAuthor(string $author): array
    {
        return array_values(array_filter(
            $this->topics,
            fn(Topic $t) => $t->getAuthor() === $author
        ));
    }

    /** @return PinnedTopic[] */
    public function listPinned(): array
    {
        return array_values(array_filter(
            $this->topics,
            fn(Topic $t) => $t instanceof PinnedTopic && $t->isPinnedNow()
        ));
    }
}