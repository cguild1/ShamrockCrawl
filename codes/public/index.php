<?php

declare(strict_types=1);

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelQuartile;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use function GlParade\Codes\e;
use function GlParade\Codes\request_path;
use function GlParade\Codes\slugify;

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/src/helpers.php';

$path = request_path();
$codesPath = $config['app']['codes_path'];

if ($path === $codesPath || $path === $codesPath . '/') {
    header('Location: ' . $codesPath . '/admin');
    exit;
}

if ($path === $codesPath . '/admin/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = $auth->login($_POST['email'] ?? '', $_POST['password'] ?? '');
    header('Location: ' . ($ok ? $codesPath . '/admin' : $codesPath . '/admin/login?e=1'));
    exit;
}

if ($path === $codesPath . '/admin/logout') {
    $auth->logout();
    header('Location: ' . $codesPath . '/admin/login');
    exit;
}

if ($path === $codesPath . '/admin/login') {
    include dirname(__DIR__) . '/src/views/login.php';
    exit;
}

if (str_starts_with($path, $codesPath . '/admin')) {
    if (!$auth->check()) {
        header('Location: ' . $codesPath . '/admin/login');
        exit;
    }

    if ($path === $codesPath . '/admin/api/slug-check') {
        $slug = slugify((string)($_GET['slug'] ?? ''));
        $ignoreId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        header('Content-Type: application/json');
        echo json_encode(['slug' => $slug, 'available' => $slug !== '' && $links->slugAvailable($slug, $ignoreId)]);
        exit;
    }

    if ($path === $codesPath . '/admin/save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $slug = slugify((string)($_POST['slug'] ?? ''));
        if (!$links->slugAvailable($slug, $id)) {
            header('Location: ' . $codesPath . '/admin?error=slug');
            exit;
        }

        $settings = [
            'size' => max(100, min(1500, (int)($_POST['size'] ?? 600))),
            'margin' => max(0, min(30, (int)($_POST['margin'] ?? 10))),
            'format' => in_array($_POST['format'] ?? 'png', ['png', 'svg'], true) ? $_POST['format'] : 'png',
            'foreground' => $_POST['foreground'] ?? '#000000',
            'background' => $_POST['background'] ?? '#ffffff',
            'error_correction' => $_POST['error_correction'] ?? 'M',
        ];

        $payload = trim((string)($_POST['qr_payload'] ?? ($_POST['destination_url'] ?? '')));
        $linkId = $links->upsert([
            'business_id' => !empty($_POST['business_id']) ? (int)$_POST['business_id'] : null,
            'title' => trim((string)$_POST['title']),
            'slug' => $slug,
            'destination_url' => trim((string)$_POST['destination_url']),
            'qr_payload' => $payload,
            'qr_settings_json' => json_encode($settings, JSON_UNESCAPED_SLASHES),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'created_by_admin_id' => $auth->id(),
        ], $id);

        header('Location: ' . $codesPath . '/admin?ok=' . $linkId);
        exit;
    }

    if ($path === $codesPath . '/admin/qr-image') {
        $id = (int)($_GET['id'] ?? 0);
        $link = $links->byId($id);
        if (!$link) {
            http_response_code(404);
            exit('Not found');
        }

        $settings = json_decode((string)$link['qr_settings_json'], true) ?: [];
        $format = $settings['format'] ?? 'png';
        $writer = $format === 'svg' ? new SvgWriter() : new PngWriter();

        $fg = sscanf($settings['foreground'] ?? '#000000', '#%02x%02x%02x');
        $bg = sscanf($settings['background'] ?? '#ffffff', '#%02x%02x%02x');

        $errorCorrection = match ($settings['error_correction'] ?? 'M') {
            'L' => new ErrorCorrectionLevelLow(),
            'Q' => new ErrorCorrectionLevelQuartile(),
            'H' => new ErrorCorrectionLevelHigh(),
            default => new ErrorCorrectionLevelMedium(),
        };

        $result = Builder::create()
            ->writer($writer)
            ->data((string)$link['qr_payload'])
            ->encoding(new Encoding('UTF-8'))
            ->size((int)($settings['size'] ?? 600))
            ->margin((int)($settings['margin'] ?? 10))
            ->errorCorrectionLevel($errorCorrection)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->foregroundColor(new Endroid\QrCode\Color\Color($fg[0], $fg[1], $fg[2]))
            ->backgroundColor(new Endroid\QrCode\Color\Color($bg[0], $bg[1], $bg[2]))
            ->build();

        header('Content-Type: ' . $result->getMimeType());
        echo $result->getString();
        exit;
    }

    $edit = isset($_GET['edit']) ? $links->byId((int)$_GET['edit']) : null;
    $all = $links->all();
    $businesses = $links->businesses();
    include dirname(__DIR__) . '/src/views/admin.php';
    exit;
}

if (preg_match('#^/go/([a-z0-9\-]+)$#', $path, $m)) {
    $link = $links->bySlug($m[1]);
    if (!$link) {
        http_response_code(404);
        echo 'Short URL not found.';
        exit;
    }
    $tracker->track((int)$link['id'], [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'referer' => $_SERVER['HTTP_REFERER'] ?? '',
        'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
        'query_string' => $_SERVER['QUERY_STRING'] ?? '',
        'scan_source' => 'redirect-301',
    ]);
    header('Location: ' . $link['destination_url'], true, 301);
    exit;
}

$slug = ltrim($path, '/');
if ($slug !== '' && !str_starts_with($slug, 'codes')) {
    $link = $links->bySlug($slug);
    if ($link) {
        include dirname(__DIR__) . '/src/views/interstitial.php';
        exit;
    }
}

http_response_code(404);
echo 'Not found';
