<?php

class Topic
{
    protected string $title;
    protected string $author;
    protected string $createdAt;

    public function __construct(string $title, string $author, string $createdAt)
    {
        $this->title = $title;
        $this->author = $author;
        $this->createdAt = $createdAt;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getInfo(): string
    {
        return "{$this->title} (автор: {$this->author})";
    }
}