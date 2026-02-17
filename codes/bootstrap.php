<?php

declare(strict_types=1);

use GlParade\Codes\Auth;
use GlParade\Codes\LinkRepository;
use GlParade\Codes\ScanTracker;

require_once __DIR__ . '/vendor/autoload.php';

$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo 'Missing codes/config.php. Copy config.example.php to config.php and update credentials.';
    exit;
}

$config = require $configFile;

session_name($config['app']['session_name'] ?? 'glp_codes_admin');
session_start();

$pdo = new PDO(
    $config['db']['dsn'],
    $config['db']['username'],
    $config['db']['password'],
    $config['db']['options'] ?? []
);

$auth = new Auth($pdo);
$links = new LinkRepository($pdo);
$tracker = new ScanTracker($pdo);
