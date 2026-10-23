<?php

require_once __DIR__ . '/Topic.php';

class PinnedTopic extends Topic
{
    private string $pinnedUntil;

    public function __construct(string $title, string $author, string $createdAt, string $pinnedUntil)
    {
        parent::__construct($title, $author, $createdAt);
        $this->pinnedUntil = $pinnedUntil;
    }

    public function getPinnedUntil(): string
    {
        return $this->pinnedUntil;
    }

    public function isPinnedNow(): bool
    {
        return strtotime($this->pinnedUntil) >= time();
    }

    public function getInfo(): string
    {
        return parent::getInfo() . " — закріплено до {$this->pinnedUntil}";
    }
}