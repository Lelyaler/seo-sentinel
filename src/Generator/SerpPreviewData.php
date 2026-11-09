<?php
declare(strict_types=1);

namespace SeoSentinel\Generator;

class SerpPreviewData
{
    /**
     * Generate visual snippet simulation for Google and Yandex
     *
     * @param string $url
     * @param string $title
     * @param string $description
     * @return array{google: array<string, mixed>, yandex: array<string, mixed>}
     */
    public function generate(string $url, string $title, string $description): array
    {
        $parsed = parse_url($url);
        $domain = $parsed['host'] ?? 'example.com';
        $pathParts = array_filter(explode('/', trim($parsed['path'] ?? '', '/')));
        $breadcrumb = $domain . ($pathParts ? ' › ' . implode(' › ', $pathParts) : '');

        // Google Snippet (Title max ~60 chars, Desc max ~160 chars)
        $googleMaxTitle = 60;
        $googleMaxDesc = 155;
        $googleTitleTruncated = mb_strlen($title) > $googleMaxTitle;
        $googleDescTruncated = mb_strlen($description) > $googleMaxDesc;

        $googleTitle = $googleTitleTruncated ? mb_substr($title, 0, $googleMaxTitle) . '...' : $title;
        $googleDesc = $googleDescTruncated ? mb_substr($description, 0, $googleMaxDesc) . '...' : $description;

        // Yandex Snippet (Title max ~56 chars, Desc max ~165 chars)
        $yandexMaxTitle = 56;
        $yandexMaxDesc = 165;
        $yandexTitleTruncated = mb_strlen($title) > $yandexMaxTitle;
        $yandexDescTruncated = mb_strlen($description) > $yandexMaxDesc;

        $yandexTitle = $yandexTitleTruncated ? mb_substr($title, 0, $yandexMaxTitle) . '...' : $title;
        $yandexDesc = $yandexDescTruncated ? mb_substr($description, 0, $yandexMaxDesc) . '...' : $description;

        return [
            'google' => [
                'title' => $googleTitle ?: 'Заголовок страницы не указан',
                'description' => $googleDesc ?: 'Мета-описание отсутствует. Google выберет случайный фрагмент текста со страницы.',
                'breadcrumb' => $breadcrumb,
                'is_title_truncated' => $googleTitleTruncated,
                'is_desc_truncated' => $googleDescTruncated,
                'orig_title_len' => mb_strlen($title),
                'orig_desc_len' => mb_strlen($description),
            ],
            'yandex' => [
                'title' => $yandexTitle ?: 'Заголовок страницы не указан',
                'description' => $yandexDesc ?: 'Мета-описание отсутствует.',
                'breadcrumb' => $domain,
                'is_title_truncated' => $yandexTitleTruncated,
                'is_desc_truncated' => $yandexDescTruncated,
            ],
        ];
    }
}
