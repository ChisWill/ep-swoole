<?php

declare(strict_types=1);

namespace Ep\Tests\App\Service;

use Ep\Helper\Curl;

final class CurlService
{
    private const DOMAIN = 'http://localhost:9501';
    // private const DOMAIN = 'http://ep.swoole.cc';

    public function getLoginInfo(int $id): array
    {
        $ch = $this->request(self::DOMAIN . '/demo/login', ['id' => $id]);
        $result = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $headerSize);
        $content = json_decode(substr($result, $headerSize), true) ?: [];

        $cookie = '';
        foreach (explode("\n", $header) as $row) {
            if (strpos($row, ': ') !== false) {
                [$header, $value] = explode(': ', $row);
                if ($header === 'Set-Cookie') {
                    $cookie = $value;
                }
            }
        }

        $errno = (int) ($content['errno'] ?? 500);
        return [
            $errno === 0,
            $cookie
        ];
    }

    public function getUserId(string $cookie): int
    {
        $r =  Curl::get(self::DOMAIN . '/demo/getUser', '', [
            'header' => [
                'Cookie: ' . $cookie
            ]
        ]);
        $result = json_decode($r, true);
        return (int) ($result['body'] ?? 0);
    }

    private function request(string $url, array $params)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $ch;
    }
}
