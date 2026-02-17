<?php

declare(strict_types=1);

use function GlParade\Codes\slugify;

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/src/helpers.php';

$slug = slugify((string)($_GET['slug'] ?? ''));
$link = $links->bySlug($slug);
if (!$link) {
    http_response_code(404);
    exit;
}

$body = file_get_contents('php://input') ?: '{}';
$tracker->track((int)$link['id'], [
    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'referer' => $_SERVER['HTTP_REFERER'] ?? '',
    'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
    'query_string' => $body,
    'scan_source' => 'interstitial-beacon',
]);

http_response_code(204);
