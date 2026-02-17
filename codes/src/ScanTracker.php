<?php

declare(strict_types=1);

namespace GlParade\Codes;

use PDO;

class ScanTracker
{
    public function __construct(private PDO $pdo)
    {
    }

    public function track(int $linkId, array $client = []): void
    {
        $sql = 'INSERT INTO qr_scans (qr_link_id,ip_hash,user_agent,referer,accept_language,query_string,device_type,scan_source,created_at) VALUES (:qr_link_id,:ip_hash,:user_agent,:referer,:accept_language,:query_string,:device_type,:scan_source,NOW())';

        $this->pdo->prepare($sql)->execute([
            'qr_link_id' => $linkId,
            'ip_hash' => hash('sha256', (string)($client['ip'] ?? '')),
            'user_agent' => substr((string)($client['user_agent'] ?? ''), 0, 255),
            'referer' => substr((string)($client['referer'] ?? ''), 0, 255),
            'accept_language' => substr((string)($client['accept_language'] ?? ''), 0, 120),
            'query_string' => (string)($client['query_string'] ?? ''),
            'device_type' => $this->deviceType((string)($client['user_agent'] ?? '')),
            'scan_source' => substr((string)($client['scan_source'] ?? 'short-url'), 0, 50),
        ]);

        $this->pdo->prepare('UPDATE qr_links SET scan_count = scan_count + 1, last_scanned_at = NOW() WHERE id = :id')->execute(['id' => $linkId]);
    }

    private function deviceType(string $ua): string
    {
        $ua = strtolower($ua);
        return match (true) {
            str_contains($ua, 'ipad'), str_contains($ua, 'tablet') => 'tablet',
            str_contains($ua, 'mobile'), str_contains($ua, 'iphone'), str_contains($ua, 'android') => 'mobile',
            default => 'desktop',
        };
    }
}
