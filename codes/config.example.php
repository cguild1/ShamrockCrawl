<?php
return [
    'app' => [
        'base_url' => 'https://glparade.com',
        'codes_path' => '/codes',
        'session_name' => 'glp_codes_admin',
    ],
    'db' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=glparade;charset=utf8mb4',
        'username' => 'db_user',
        'password' => 'db_pass',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    ],
];
