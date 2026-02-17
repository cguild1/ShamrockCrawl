<?php

declare(strict_types=1);

namespace GlParade\Codes;

use PDO;

class LinkRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        $sql = 'SELECT ql.*, bs.business_name FROM qr_links ql LEFT JOIN biz_stpats bs ON bs.id = ql.business_id ORDER BY ql.created_at DESC';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function bySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM qr_links WHERE slug = :slug AND is_active = 1 LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function byId(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM qr_links WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function slugAvailable(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM qr_links WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($ignoreId) {
            $sql .= ' AND id <> :id';
            $params['id'] = $ignoreId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() === 0;
    }

    public function upsert(array $data, ?int $id = null): int
    {
        if ($id) {
            $sql = 'UPDATE qr_links SET business_id=:business_id,title=:title,slug=:slug,destination_url=:destination_url,qr_payload=:qr_payload,qr_settings_json=:qr_settings_json,is_active=:is_active,updated_at=NOW() WHERE id=:id';
            $data['id'] = $id;
            $this->pdo->prepare($sql)->execute($data);
            return $id;
        }

        $sql = 'INSERT INTO qr_links (business_id,title,slug,destination_url,qr_payload,qr_settings_json,is_active,created_by_admin_id,created_at) VALUES (:business_id,:title,:slug,:destination_url,:qr_payload,:qr_settings_json,:is_active,:created_by_admin_id,NOW())';
        $this->pdo->prepare($sql)->execute($data);
        return (int)$this->pdo->lastInsertId();
    }

    public function businesses(): array
    {
        $sql = 'SELECT id,business_name,slug,web_site_home_page_url FROM biz_stpats WHERE is_active = 1 ORDER BY business_name';
        return $this->pdo->query($sql)->fetchAll();
    }
}
