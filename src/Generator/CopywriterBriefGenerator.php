<?php
declare(strict_types=1);

namespace SeoSentinel\Generator;

class CopywriterBriefGenerator
{
    /**
     * Generate complete technical brief and ChatGPT/Claude prompt
     *
     * @param string $keyword
     * @param string $pageTitle
     * @param array<string, mixed> $pageData
     * @param array<string, mixed> $competitorData
     * @return array{brief_markdown: string, ai_prompt: string, target_words: int, words_needed: int}
     */
    public function generate(
        string $keyword,
        string $pageTitle,
        array $pageData,
        array $competitorData
    ): array {
        $currentWords = $pageData['content']['word_count'] ?? 0;
        $targetWords = $competitorData['benchmark']['target_word_count'] ?? 850;
        $wordsNeeded = max(0, $targetWords - $currentWords);

        $missingSections = $competitorData['content_gap']['missing_sections'] ?? [];
        $missingSectionTitles = array_map(static fn($s) => strtoupper($s['key']), $missingSections);
        $lsiList = $competitorData['lsi_suggestions'] ?? [];

        // 1. Markdown Brief for human copywriter
        $brief = "# ТЕХНИЧЕСКОЕ ЗАДАНИЕ НА SEO-ТЕКСТ\n\n";
        $brief .= "**Основной ключевой запрос:** {$keyword}\n";
        $brief .= "**Целевой объем:** {$targetWords} слов (текущий объем: {$currentWords} слов, дефицит: {$wordsNeeded} слов)\n";
        $brief .= "**Текущий заголовок:** {$pageTitle}\n\n";

        $brief .= "## 1. Обязательные разделы структуры (H2/H3):\n";
        if (!empty($missingSections)) {
            foreach ($missingSections as $s) {
                $brief .= "- [H2] " . $this->getRussianSectionTitle($s['key']) . " (" . $s['importance'] . ")\n";
            }
        } else {
            $brief .= "- Все базовые разделы присутствуют, требуется углубление фактуры.\n";
        }

        $brief .= "\n## 2. Обязательные LSI-фразы для текста:\n";
        foreach ($lsiList as $lsi) {
            $brief .= "- {$lsi}\n";
        }

        $brief .= "\n## 3. Требования GEO & E-E-A-T:\n";
        $brief .= "- Использовать минимум 1 маркированный список (ul) и 1 таблицу (цены или сравнение).\n";
        $brief .= "- Сформулировать 2-3 подзаголовка в виде вопросов («Сколько стоит...?», «Как заказать...?»).\n";
        $brief .= "- Сразу под вопросом давать четкий ответ в 1-2 предложениях (для сниппетов нейросетей).\n";

        // 2. AI Prompt for ChatGPT / Claude / Gemini
        $sectionsStr = implode(', ', array_map(fn($s) => $this->getRussianSectionTitle($s['key']), $missingSections));
        $lsiStr = implode(', ', $lsiList);

        $aiPrompt = "Ты — Senior SEO Copywriter и специалист по Generative Engine Optimization (GEO).\n";
        $aiPrompt .= "Напиши экспертный, конверсионный текст для посадочной страницы по запросу: «{$keyword}».\n\n";
        $aiPrompt .= "ТРЕБОВАНИЯ К КОНТЕНТУ:\n";
        $aiPrompt .= "1. Общий объем: около {$targetWords} слов.\n";
        if (!empty($sectionsStr)) {
            $aiPrompt .= "2. Обязательно включи структурные разделы: {$sectionsStr}.\n";
        }
        $aiPrompt .= "3. Естественно используй LSI-фразы: {$lsiStr}.\n";
        $aiPrompt .= "4. Стиль: информативный, без «воды» и шаблонных фраз. Используй цифры, конкретику и выгоды для клиента.\n";
        $aiPrompt .= "5. Форматирование: структурируй текст с помощью H2, H3, маркированных списков и сравнительной таблицы.\n";
        $aiPrompt .= "6. Для каждого вопроса в H2 давай прямой краткий ответ в первом же предложении (фактор цитирования Perplexity/ChatGPT).\n";

        return [
            'brief_markdown' => $brief,
            'ai_prompt' => $aiPrompt,
            'target_words' => $targetWords,
            'words_needed' => $wordsNeeded,
        ];
    }

    private function getRussianSectionTitle(string $key): string
    {
        return match (strtolower($key)) {
            'faq' => 'Часто задаваемые вопросы (FAQ)',
            'pricing' => 'Стоимость и тарифы',
            'process_steps' => 'Этапы и схема работы',
            'reviews_cases' => 'Кейсы и отзывы клиентов',
            'guarantees' => 'Гарантии и работа по договору',
            default => ucfirst($key),
        };
    }
}
