<?php
declare(strict_types=1);

namespace SeoSentinel\Service;

use SeoSentinel\Analyzer\CompetitorAnalyzer;
use SeoSentinel\Analyzer\PageCrawler;
use SeoSentinel\Evaluator\GeoScorer;
use SeoSentinel\Generator\SchemaGenerator;
use SeoSentinel\Http\Client;
use SeoSentinel\Exception\SeoException;

class AuditService
{
    private Client $httpClient;
    private PageCrawler $crawler;
    private CompetitorAnalyzer $competitorAnalyzer;
    private SchemaGenerator $schemaGenerator;
    private GeoScorer $geoScorer;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->crawler = new PageCrawler();
        $this->competitorAnalyzer = new CompetitorAnalyzer();
        $this->schemaGenerator = new SchemaGenerator();
        $this->geoScorer = new GeoScorer();
    }

    /**
     * Run full end-to-end SEO & GEO audit by URL
     *
     * @param string $url
     * @param string $keyword
     * @return array<string, mixed>
     * @throws SeoException
     */
    public function runAudit(string $url, string $keyword): array
    {
        $response = $this->httpClient->get($url);
        $html = $response['content'];
        $finalUrl = $response['final_url'];
        $loadTime = $response['total_time'];

        return $this->processAudit($html, $url, $finalUrl, $keyword, $loadTime, 'live_crawler');
    }

    /**
     * Run audit from raw HTML string (for Cloudflare bypass or local staging)
     *
     * @param string $html
     * @param string $url
     * @param string $keyword
     * @return array<string, mixed>
     */
    public function runAuditFromHtml(string $html, string $url, string $keyword): array
    {
        $cleanUrl = !empty($url) ? $url : 'https://example.com/custom-page';
        return $this->processAudit($html, $cleanUrl, $cleanUrl, $keyword, 0.05, 'manual_html');
    }

    /**
     * @param string $html
     * @param string $targetUrl
     * @param string $finalUrl
     * @param string $keyword
     * @param float $loadTime
     * @param string $sourceMode
     * @return array<string, mixed>
     */
    private function processAudit(
        string $html,
        string $targetUrl,
        string $finalUrl,
        string $keyword,
        float $loadTime,
        string $sourceMode
    ): array {
        // 1. Parse Page DOM
        $pageData = $this->crawler->analyze($html, $finalUrl);
        $pageData['load_time'] = $loadTime;

        // 2. Competitor & Content Gap Analysis
        $competitorData = $this->competitorAnalyzer->analyze($pageData, $keyword);

        // 3. Generate Schema.org JSON-LD
        $schemaData = $this->schemaGenerator->generate(
            $finalUrl,
            $pageData['title']['text'] ?? '',
            $pageData['meta_description']['text'] ?? '',
            $keyword
        );

        // 4. GEO & AI Readiness Scoring
        $geoData = $this->geoScorer->evaluate($pageData, $keyword);

        // 5. Overall SEO Health Score calculation (0 - 100)
        $seoScore = $this->calculateOverallScore($pageData, $competitorData);

        return [
            'meta' => [
                'target_url' => $targetUrl,
                'final_url' => $finalUrl,
                'keyword' => $keyword,
                'analyzed_at' => date('Y-m-d H:i:s'),
                'load_time_seconds' => $loadTime,
                'source_mode' => $sourceMode,
            ],
            'scores' => [
                'seo_health_score' => $seoScore,
                'geo_ai_score' => $geoData['score'],
                'geo_grade' => $geoData['grade'],
            ],
            'page' => $pageData,
            'competitors_gap' => $competitorData,
            'schema_generated' => $schemaData,
            'geo_readiness' => $geoData,
        ];
    }

    /**
     * @param array<string, mixed> $pageData
     * @param array<string, mixed> $competitorData
     * @return int
     */
    private function calculateOverallScore(array $pageData, array $competitorData): int
    {
        $score = 0;

        // Title score (max 15)
        if (($pageData['title']['status'] ?? '') === 'good') {
            $score += 15;
        } elseif (!empty($pageData['title']['text'])) {
            $score += 8;
        }

        // Meta Description (max 15)
        if (($pageData['meta_description']['status'] ?? '') === 'good') {
            $score += 15;
        } elseif (!empty($pageData['meta_description']['text'])) {
            $score += 8;
        }

        // H1 check (max 15)
        $h1Count = $pageData['headings']['counts']['h1'] ?? 0;
        if ($h1Count === 1) {
            $score += 15;
        } elseif ($h1Count > 1) {
            $score += 8;
        }

        // Images ALT check (max 15)
        $totalImg = $pageData['images']['total'] ?? 0;
        $missingAlt = $pageData['images']['missing_alt'] ?? 0;
        if ($totalImg === 0 || $missingAlt === 0) {
            $score += 15;
        } elseif ($missingAlt <= ($totalImg / 2)) {
            $score += 8;
        }

        // Keyword in Title & H1 (max 20)
        if (!empty($competitorData['keyword_in_title'])) {
            $score += 10;
        }
        if (!empty($competitorData['keyword_in_h1'])) {
            $score += 10;
        }

        // Schema.org presence (max 10)
        if (!empty($pageData['schema']['has_schema'])) {
            $score += 10;
        }

        // Load speed (max 10)
        $loadTime = $pageData['load_time'] ?? 2.0;
        if ($loadTime < 1.0) {
            $score += 10;
        } elseif ($loadTime < 2.5) {
            $score += 6;
        }

        return min(100, $score);
    }
}
