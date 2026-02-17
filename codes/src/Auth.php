<?php

declare(strict_types=1);

namespace GlParade\Codes;

use PDO;

class Auth
{
    public function __construct(private PDO $pdo)
    {
    }

    public function login(string $email, string $password): bool
    {
        $stmt = $this->pdo->prepare('SELECT id, email, password_hash, is_approved FROM qr_admin_users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => strtolower(trim($email))]);
        $user = $stmt->fetch();

        if (!$user || !(int)$user['is_approved']) {
            return false;
        }

        if (!password_verify($password, (string)$user['password_hash'])) {
            return false;
        }

        $_SESSION['admin_user_id'] = (int)$user['id'];
        $_SESSION['admin_email'] = (string)$user['email'];

        $this->pdo->prepare('UPDATE qr_admin_users SET last_login_at = NOW() WHERE id = :id')->execute(['id' => $user['id']]);

        return true;
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
    }

    public function check(): bool
    {
        return !empty($_SESSION['admin_user_id']);
    }

    public function id(): ?int
    {
        return isset($_SESSION['admin_user_id']) ? (int)$_SESSION['admin_user_id'] : null;
    }
}
