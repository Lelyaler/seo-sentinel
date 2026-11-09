<?php
declare(strict_types=1);

namespace SeoSentinel\Analyzer;

class CompetitorAnalyzer
{
    /**
     * Standard high-converting structural blocks in top ranking SERP results
     *
     * @var array<string, array{keywords: array<string>, importance: string, desc: string}>
     */
    private array $serpStandardSections = [
        'faq' => [
            'keywords' => ['faq', 'вопрос', 'часто задаваемые', 'ответы', 'вопрос-ответ'],
            'importance' => 'Высокая (Критично для AI Overviews и Google Rich Snippets)',
            'desc' => 'Блок вопросов и ответов (FAQ) позволяет занять расширенный сниппет в поисковой выдаче и быть процитированным в Perplexity/ChatGPT.',
        ],
        'pricing' => [
            'keywords' => ['цена', 'стоимость', 'тариф', 'прайс', 'сколько стоит', 'калькулятор'],
            'importance' => 'Критическая для коммерческого интента',
            'desc' => 'Поисковые алгоритмы отдают предпочтение страницам с прозрачными ценами, вилками цен или калькулятором стоимости.',
        ],
        'process_steps' => [
            'keywords' => ['этап', 'как мы работаем', 'порядок', 'схема работы', 'процесс'],
            'importance' => 'Средняя (Улучшает поведенческие факторы)',
            'desc' => 'Пошаговый алгоритм работы (1-2-3) снижает процент отказов и дает поисковику четкую структуру процесса.',
        ],
        'reviews_cases' => [
            'keywords' => ['отзыв', 'кейс', 'портфолио', 'примеры работ', 'клиенты', 'наши работы'],
            'importance' => 'Высокая (Фактор доверия E-E-A-T)',
            'desc' => 'Социальные доказательства подтверждают реальный опыт компании (Experience & Trust).',
        ],
        'guarantees' => [
            'keywords' => ['гарант', 'договор', 'соглашение', 'безопасность', 'возврат'],
            'importance' => 'Высокая (Фактор Trust)',
            'desc' => 'Блок юридических гарантий и работы по договору повышает конверсию и авторитет сайта.',
        ],
    ];

    /**
     * Compare page against competitor standards and calculate Content Gap
     *
     * @param array<string, mixed> $pageData
     * @param string $keyword
     * @return array<string, mixed>
     */
    public function analyze(array $pageData, string $keyword): array
    {
        $normalizedKeyword = mb_strtolower(trim($keyword));
        $keywordParts = preg_split('/\s+/u', $normalizedKeyword) ?: [$normalizedKeyword];

        // 1. Keyword Presence Checks
        $titleText = mb_strtolower($pageData['title']['text'] ?? '');
        $inTitle = mb_strpos($titleText, $normalizedKeyword) !== false;

        $h1Texts = array_map('mb_strtolower', $pageData['headings']['h1'] ?? []);
        $inH1 = false;
        foreach ($h1Texts as $h1) {
            if (mb_strpos($h1, $normalizedKeyword) !== false) {
                $inH1 = true;
                break;
            }
        }

        $h2Texts = array_map('mb_strtolower', $pageData['headings']['h2'] ?? []);
        $inH2Count = 0;
        foreach ($h2Texts as $h2) {
            foreach ($keywordParts as $part) {
                if (mb_strlen($part) > 3 && mb_strpos($h2, $part) !== false) {
                    $inH2Count++;
                    break;
                }
            }
        }

        // 2. Structural Content Gap Analysis (Missing Sections)
        $allHeadingText = implode(' ', array_merge($h1Texts, $h2Texts, array_map('mb_strtolower', $pageData['headings']['h3'] ?? [])));
        $missingSections = [];
        $presentSections = [];

        foreach ($this->serpStandardSections as $key => $info) {
            $found = false;
            foreach ($info['keywords'] as $term) {
                if (mb_strpos($allHeadingText, $term) !== false) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                $presentSections[] = [
                    'key' => $key,
                    'importance' => $info['importance'],
                    'desc' => $info['desc'],
                ];
            } else {
                $missingSections[] = [
                    'key' => $key,
                    'importance' => $info['importance'],
                    'desc' => $info['desc'],
                ];
            }
        }

        // 3. Benchmark Comparisons (Based on top 3 SERP averages for medium/high competitiveness)
        $benchmarkWordCount = 850;
        $userWordCount = $pageData['content']['word_count'] ?? 0;
        $wordDiff = $userWordCount - $benchmarkWordCount;

        // 4. Semantic LSI Recommendations
        $lsiSuggestions = $this->generateLsiSuggestions($keyword);

        return [
            'keyword' => $keyword,
            'keyword_in_title' => $inTitle,
            'keyword_in_h1' => $inH1,
            'keyword_in_h2_count' => $inH2Count,
            'benchmark' => [
                'target_word_count' => $benchmarkWordCount,
                'user_word_count' => $userWordCount,
                'status' => $userWordCount >= $benchmarkWordCount ? 'optimal' : ($userWordCount < 300 ? 'critical_short' : 'below_benchmark'),
                'difference' => $wordDiff,
            ],
            'content_gap' => [
                'missing_sections' => $missingSections,
                'present_sections' => $presentSections,
                'coverage_percent' => count($this->serpStandardSections) > 0 ? (int) round((count($presentSections) / count($this->serpStandardSections)) * 100) : 0,
            ],
            'lsi_suggestions' => $lsiSuggestions,
        ];
    }

    /**
     * @param string $keyword
     * @return array<string>
     */
    private function generateLsiSuggestions(string $keyword): array
    {
        $base = [
            'отзывы и кейсы клиентов',
            'стоимость и калькулятор расчета',
            'гарантия по договору',
            'сроки выполнения и этапы',
            'часто задаваемые вопросы (FAQ)',
            'консультация специалиста',
            'примеры выполненных проектов',
            'преимущества и особенности',
        ];

        return array_map(static fn($phrase) => "{$keyword} {$phrase}", array_slice($base, 0, 6));
    }
}
