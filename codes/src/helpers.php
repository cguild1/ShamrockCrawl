<?php

declare(strict_types=1);

namespace GlParade\Codes;

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function request_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return '/' . ltrim($uri, '/');
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = str_replace(['&', "'", '.'], ['and', '', ''], $value);
    $value = preg_replace('/[^a-z0-9\-\s]/', '', $value) ?? '';
    $value = preg_replace('/\s+/', '-', $value) ?? '';
    $value = preg_replace('/-+/', '-', $value) ?? '';
    return trim($value, '-');
}
