<?php
declare(strict_types=1);

namespace SeoSentinel\Evaluator;

class GeoScorer
{
    /**
     * Calculate GEO (Generative Engine Optimization) Citation Readiness Score
     *
     * @param array<string, mixed> $pageData
     * @param string $keyword
     * @return array<string, mixed>
     */
    public function evaluate(array $pageData, string $keyword): array
    {
        $score = 0;
        $maxScore = 100;
        $strengths = [];
        $recommendations = [];

        // 1. Q&A Headings (Max 25 pts)
        $h2Texts = $pageData['headings']['h2'] ?? [];
        $h3Texts = $pageData['headings']['h3'] ?? [];
        $allHeadings = array_merge($h2Texts, $h3Texts);

        $questionHeadings = 0;
        $questionWords = ['как', 'сколько', 'почему', 'что такое', 'какой', 'где', 'зачем', 'в чем разница', 'когда', 'faq'];

        foreach ($allHeadings as $heading) {
            $hLower = mb_strtolower($heading);
            if (str_contains($hLower, '?')) {
                $questionHeadings++;
                continue;
            }
            foreach ($questionWords as $qw) {
                if (str_starts_with($hLower, $qw . ' ') || str_contains($hLower, ' ' . $qw . ' ')) {
                    $questionHeadings++;
                    break;
                }
            }
        }

        if ($questionHeadings >= 3) {
            $score += 25;
            $strengths[] = "Отличная вопросно-ответная структура ({$questionHeadings} заголовков-вопросов). Нейросети легко выделяют фрагменты для цитирования.";
        } elseif ($questionHeadings >= 1) {
            $score += 15;
            $strengths[] = "Есть вопросительные подзаголовки ({$questionHeadings} шт.).";
            $recommendations[] = "Добавьте еще 2-3 подзаголовка в форме вопросов (например: «Сколько стоит {$keyword}?», «Как заказать...»).";
        } else {
            $recommendations[] = "Критично для AI-поиска: переформулируйте 2-3 подзаголовка H2/H3 в виде прямых вопросов пользователей.";
        }

        // 2. Structured Lists & Tables (Max 25 pts)
        $tables = $pageData['elements']['tables'] ?? 0;
        $lists = $pageData['elements']['lists'] ?? 0;

        if ($tables >= 1 && $lists >= 3) {
            $score += 25;
            $strengths[] = "Высокая концентрация структурированных данных ({$tables} таблиц, {$lists} списков). LLM-модели цитируют списки и таблицы в 3 раза чаще сплошного текста.";
        } elseif ($lists >= 2 || $tables >= 1) {
            $score += 15;
            $strengths[] = "Найдены списки ({$lists} шт.) или таблицы.";
            $recommendations[] = "Добавьте сравнительную таблицу характеристик или тарифов (тег <table>). Модели Perplexity и Google AI обожают табличные данные.";
        } else {
            $recommendations[] = "На странице не хватает списков (ul/ol) и таблиц. Превратите сплошные абзацы в списки преимуществ или этапов.";
        }

        // 3. Schema.org JSON-LD (Max 25 pts)
        $hasSchema = $pageData['schema']['has_schema'] ?? false;
        $schemaCount = $pageData['schema']['count'] ?? 0;

        if ($hasSchema && $schemaCount >= 2) {
            $score += 25;
            $strengths[] = "Обнаружена развитая семантическая микроразметка Schema.org ({$schemaCount} схемы). Это облегчает краулерам понимание сущностей.";
        } elseif ($hasSchema) {
            $score += 15;
            $strengths[] = "Есть базовая микроразметка Schema.org.";
            $recommendations[] = "Внедрите разметку FAQPage и Service (готовый код доступен во вкладке 'Микроразметка').";
        } else {
            $recommendations[] = "Отсутствует микроразметка Schema.org JSON-LD. Поисковым ботам сложнее распознать цены, FAQ и тип организации.";
        }

        // 4. Content Volume & E-E-A-T depth (Max 25 pts)
        $wordCount = $pageData['content']['word_count'] ?? 0;
        if ($wordCount >= 700) {
            $score += 25;
            $strengths[] = "Хороший объем фактологического контента ({$wordCount} слов). Достаточная смысловая глубина для обучения RAG-моделей.";
        } elseif ($wordCount >= 350) {
            $score += 15;
            $strengths[] = "Умеренный объем текста ({$wordCount} слов).";
            $recommendations[] = "Увеличьте экспертную глубину до 700+ слов: добавьте блок «Критерии выбора», «Типичные ошибки» или разбор нюансов.";
        } else {
            $recommendations[] = "Критически мало текста ({$wordCount} слов). Нейросеть посчитает страницу «thin content» и не включит в ответы.";
        }

        // Grade calculation
        $grade = match (true) {
            $score >= 85 => 'A (Отлично)',
            $score >= 65 => 'B (Хорошо)',
            $score >= 45 => 'C (Удовлетворительно)',
            default => 'D (Требуется доработка)',
        };

        return [
            'score' => $score,
            'max_score' => $maxScore,
            'grade' => $grade,
            'strengths' => $strengths,
            'recommendations' => $recommendations,
            'summary' => "Индекс готовности к цитированию в AI-поиске: {$score}/100. Оценка: {$grade}.",
        ];
    }
}
