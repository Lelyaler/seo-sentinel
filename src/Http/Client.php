<?php
declare(strict_types=1);

namespace SeoSentinel\Http;

use SeoSentinel\Exception\SeoException;

class Client
{
    private string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36';
    private int $timeout = 10;

    /**
     * Fetch HTML from URL with safety and error handling
     *
     * @param string $url
     * @return array{content: string, status: int, total_time: float, final_url: string}
     * @throws SeoException
     */
    public function get(string $url): array
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new SeoException("Некорректный URL: {$url}");
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new SeoException("Поддерживаются только http и https протоколы.");
        }

        $ch = curl_init();
        $responseHeaders = [];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_ENCODING => '', // supports gzip, deflate, br
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
                'Sec-Ch-Ua: "Chromium";v="128", "Not;A=Brand";v="24", "Google Chrome";v="128"',
                'Sec-Ch-Ua-Mobile: ?0',
                'Sec-Ch-Ua-Platform: "Windows"',
                'Sec-Fetch-Dest: document',
                'Sec-Fetch-Mode: navigate',
                'Sec-Fetch-Site: none',
                'Sec-Fetch-User: ?1',
                'Upgrade-Insecure-Requests: 1',
            ],
            CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$responseHeaders) {
                $len = strlen($header);
                $parts = explode(':', $header, 2);
                if (count($parts) === 2) {
                    $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }
                return $len;
            },
        ]);

        $content = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $totalTime = (float) curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $finalUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $error = curl_error($ch);
        curl_close($ch);

        if ($content === false || !is_string($content)) {
            throw new SeoException("Ошибка соединения с сайтом: {$error}");
        }

        // Check for Cloudflare / Anti-Bot blocks
        $isCloudflare = isset($responseHeaders['server']) && str_contains(strtolower($responseHeaders['server']), 'cloudflare');
        $isCfMitigated = isset($responseHeaders['cf-mitigated']) || isset($responseHeaders['cf-ray']);

        if ($status === 403 && ($isCloudflare || $isCfMitigated || str_contains($content, 'challenges.cloudflare.com'))) {
            throw new SeoException(
                "Сайт защищен системой Cloudflare WAF (антибот-проверка 'Managed Challenge').\n" .
                "Сервер блокирует автоматические сканеры. Воспользуйтесь режимом 'Вставить HTML код вручную'."
            );
        }

        if ($status >= 400) {
            throw new SeoException("Сервер вернул ошибку HTTP {$status}");
        }

        return [
            'content' => $content,
            'status' => $status,
            'total_time' => round($totalTime, 2),
            'final_url' => $finalUrl,
        ];
    }
}
