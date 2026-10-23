<?php

function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);

    if ($diff < 60) {
        return 'щойно';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . ' хв тому';
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . ' год тому';
    }
    return floor($diff / 86400) . ' дн. тому';
}

function sanitizeText(string $text): string
{
    return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
}