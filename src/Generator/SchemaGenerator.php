<?php
declare(strict_types=1);

namespace SeoSentinel\Generator;

class SchemaGenerator
{
    /**
     * Generate complete ready-to-use JSON-LD markup
     *
     * @param string $url
     * @param string $pageTitle
     * @param string $description
     * @param string $keyword
     * @param array<string, mixed> $customFaq
     * @return array<string, mixed>
     */
    public function generate(
        string $url,
        string $pageTitle,
        string $description,
        string $keyword,
        array $customFaq = []
    ): array {
        $parsed = parse_url($url);
        $domain = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? 'example.com');
        $siteName = preg_replace('/^www\./', '', $parsed['host'] ?? 'My Company');

        // 1. Service Schema
        $serviceSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => !empty($keyword) ? mb_convert_case($keyword, MB_CASE_TITLE, 'UTF-8') : $pageTitle,
            'description' => !empty($description) ? $description : "Профессиональные услуги: {$pageTitle}",
            'url' => $url,
            'provider' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'url' => $domain,
            ],
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'Россия',
            ],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'RUB',
                'availability' => 'https://schema.org/InStock',
            ],
        ];

        // 2. FAQ Schema (Rich Snippet in Google/Yandex)
        $faqItems = !empty($customFaq) ? $customFaq : $this->getPrebuiltFaqItems($keyword ?: $pageTitle);
        $faqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static fn($qa) => [
                '@type' => 'Question',
                'name' => $qa['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $qa['a'],
                ],
            ], $faqItems),
        ];

        // 3. BreadcrumbList Schema
        $pathParts = array_filter(explode('/', trim($parsed['path'] ?? '', '/')));
        $breadcrumbItems = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Главная',
                'item' => $domain,
            ],
        ];
        $currentPath = $domain;
        $pos = 2;
        foreach ($pathParts as $part) {
            $currentPath .= '/' . $part;
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'name' => mb_convert_case(str_replace(['-', '_'], ' ', $part), MB_CASE_TITLE, 'UTF-8'),
                'item' => $currentPath,
            ];
        }

        $breadcrumbsSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbItems,
        ];

        return [
            'service' => [
                'type' => 'Service',
                'json' => json_encode($serviceSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                'snippet' => '<script type="application/ld+json">' . "\n" . json_encode($serviceSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n" . '</script>',
            ],
            'faq' => [
                'type' => 'FAQPage',
                'items_count' => count($faqItems),
                'json' => json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                'snippet' => '<script type="application/ld+json">' . "\n" . json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n" . '</script>',
            ],
            'breadcrumbs' => [
                'type' => 'BreadcrumbList',
                'json' => json_encode($breadcrumbsSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                'snippet' => '<script type="application/ld+json">' . "\n" . json_encode($breadcrumbsSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n" . '</script>',
            ],
        ];
    }

    /**
     * @param string $topic
     * @return array<int, array{q: string, a: string}>
     */
    private function getPrebuiltFaqItems(string $topic): array
    {
        return [
            [
                'q' => "Сколько стоит {$topic}?",
                'a' => "Стоимость рассчитывается индивидуально и зависит от объема и сложности работ. Свяжитесь с нами для получения точного расчета и коммерческого предложения.",
            ],
            [
                'q' => "Какие сроки выполнения работ по направлению {$topic}?",
                'a' => "В среднем выполнение занимает от нескольких дней до нескольких недель в зависимости от утвержденного технического задания. Все сроки фиксируются в договоре.",
            ],
            [
                'q' => "Предоставляется ли официальная гарантия на работы?",
                'a' => "Да, мы работаем по официальному договору и предоставляем гарантию качества на все оказанные услуги.",
            ],
        ];
    }
}
