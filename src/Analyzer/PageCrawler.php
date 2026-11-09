<?php
declare(strict_types=1);

namespace SeoSentinel\Analyzer;

use DOMDocument;
use DOMXPath;

class PageCrawler
{
    /**
     * Parse HTML string and extract complete SEO data
     *
     * @param string $html
     * @param string $url
     * @return array<string, mixed>
     */
    public function analyze(string $html, string $url): array
    {
        $dom = new DOMDocument();
        // Suppress HTML5 parsing warnings
        libxml_use_internal_errors(true);
        $encodedHtml = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $dom->loadHTML($encodedHtml, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // 1. Meta Tags
        $titleNode = $xpath->query('//title');
        $title = $titleNode && $titleNode->length > 0 ? trim($titleNode->item(0)->textContent) : '';

        $descNode = $xpath->query('//meta[@name="description"]/@content');
        $metaDescription = $descNode && $descNode->length > 0 ? trim($descNode->item(0)->nodeValue ?? '') : '';

        $canonicalNode = $xpath->query('//link[@rel="canonical"]/@href');
        $canonical = $canonicalNode && $canonicalNode->length > 0 ? trim($canonicalNode->item(0)->nodeValue ?? '') : '';

        $robotsNode = $xpath->query('//meta[@name="robots"]/@content');
        $robots = $robotsNode && $robotsNode->length > 0 ? trim($robotsNode->item(0)->nodeValue ?? '') : '';

        // 2. OpenGraph
        $og = [
            'title' => $this->getXpathValue($xpath, '//meta[@property="og:title"]/@content'),
            'description' => $this->getXpathValue($xpath, '//meta[@property="og:description"]/@content'),
            'image' => $this->getXpathValue($xpath, '//meta[@property="og:image"]/@content'),
            'url' => $this->getXpathValue($xpath, '//meta[@property="og:url"]/@content'),
            'type' => $this->getXpathValue($xpath, '//meta[@property="og:type"]/@content'),
        ];

        // 3. Headings structure
        $h1s = $this->extractTexts($xpath, '//h1');
        $h2s = $this->extractTexts($xpath, '//h2');
        $h3s = $this->extractTexts($xpath, '//h3');
        $h4Count = $xpath->query('//h4')->length;
        $h5Count = $xpath->query('//h5')->length;
        $h6Count = $xpath->query('//h6')->length;

        // 4. Content Analysis
        // Remove script, style, noscript tags to extract clean readable text
        $bodyNodes = $xpath->query('//body');
        $cleanText = '';
        if ($bodyNodes && $bodyNodes->length > 0) {
            $body = $bodyNodes->item(0)->cloneNode(true);
            $subDom = new DOMDocument();
            $importedBody = $subDom->importNode($body, true);
            $subDom->appendChild($importedBody);
            $subXpath = new DOMXPath($subDom);

            foreach ($subXpath->query('//script|//style|//noscript|//svg|//header|//footer|//nav') as $removeNode) {
                $removeNode->parentNode->removeChild($removeNode);
            }
            $cleanText = trim(preg_replace('/\s+/', ' ', $subDom->textContent ?? ''));
        }

        $wordCount = mb_strlen($cleanText) > 0 ? count(preg_split('/\s+/u', $cleanText)) : 0;
        $charCount = mb_strlen($cleanText);

        // 5. Images analysis
        $images = $xpath->query('//img');
        $totalImages = $images ? $images->length : 0;
        $missingAlt = 0;
        $sampleMissingAlt = [];

        if ($images) {
            foreach ($images as $img) {
                $alt = $img->getAttribute('alt');
                if (trim($alt) === '') {
                    $missingAlt++;
                    if (count($sampleMissingAlt) < 5) {
                        $src = $img->getAttribute('src');
                        $sampleMissingAlt[] = $src ?: '(inline/base64)';
                    }
                }
            }
        }

        // 6. Schema.org JSON-LD detection
        $scriptNodes = $xpath->query('//script[@type="application/ld+json"]');
        $existingSchemas = [];
        if ($scriptNodes) {
            foreach ($scriptNodes as $script) {
                $rawJson = trim($script->textContent);
                $decoded = json_decode($rawJson, true);
                if (is_array($decoded)) {
                    $type = $decoded['@type'] ?? 'Unknown';
                    if (is_array($type)) {
                        $type = implode(', ', $type);
                    }
                    $existingSchemas[] = [
                        'type' => $type,
                        'data' => $decoded,
                    ];
                }
            }
        }

        // 7. Lists and Tables (crucial for GEO & AI Answer Extraction)
        $tableCount = $xpath->query('//table')->length;
        $listCount = $xpath->query('//ul|//ol')->length;

        // 8. Links analysis
        $links = $xpath->query('//a[@href]');
        $internalLinks = 0;
        $externalLinks = 0;
        $host = parse_url($url, PHP_URL_HOST) ?? '';

        if ($links) {
            foreach ($links as $link) {
                $href = $link->getAttribute('href');
                if (str_starts_with($href, '#') || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                    continue;
                }
                $linkHost = parse_url($href, PHP_URL_HOST);
                if (empty($linkHost) || $linkHost === $host) {
                    $internalLinks++;
                } else {
                    $externalLinks++;
                }
            }
        }

        return [
            'url' => $url,
            'title' => [
                'text' => $title,
                'length' => mb_strlen($title),
                'status' => (mb_strlen($title) >= 40 && mb_strlen($title) <= 65) ? 'good' : (mb_strlen($title) === 0 ? 'critical' : 'warning'),
            ],
            'meta_description' => [
                'text' => $metaDescription,
                'length' => mb_strlen($metaDescription),
                'status' => (mb_strlen($metaDescription) >= 120 && mb_strlen($metaDescription) <= 165) ? 'good' : (mb_strlen($metaDescription) === 0 ? 'critical' : 'warning'),
            ],
            'canonical' => [
                'url' => $canonical,
                'is_present' => !empty($canonical),
            ],
            'robots' => $robots,
            'opengraph' => $og,
            'headings' => [
                'h1' => $h1s,
                'h2' => $h2s,
                'h3' => $h3s,
                'counts' => [
                    'h1' => count($h1s),
                    'h2' => count($h2s),
                    'h3' => count($h3s),
                    'h4_h6' => $h4Count + $h5Count + $h6Count,
                ],
            ],
            'content' => [
                'word_count' => $wordCount,
                'char_count' => $charCount,
                'clean_text_preview' => mb_substr($cleanText, 0, 400) . '...',
            ],
            'images' => [
                'total' => $totalImages,
                'missing_alt' => $missingAlt,
                'sample_missing' => $sampleMissingAlt,
            ],
            'schema' => [
                'has_schema' => count($existingSchemas) > 0,
                'schemas' => $existingSchemas,
                'count' => count($existingSchemas),
            ],
            'links' => [
                'internal' => $internalLinks,
                'external' => $externalLinks,
            ],
            'elements' => [
                'tables' => $tableCount,
                'lists' => $listCount,
            ],
        ];
    }

    private function getXpathValue(DOMXPath $xpath, string $query): string
    {
        $node = $xpath->query($query);
        return ($node && $node->length > 0) ? trim($node->item(0)->nodeValue ?? '') : '';
    }

    /**
     * @param DOMXPath $xpath
     * @param string $query
     * @return array<string>
     */
    private function extractTexts(DOMXPath $xpath, string $query): array
    {
        $nodes = $xpath->query($query);
        $texts = [];
        if ($nodes) {
            foreach ($nodes as $node) {
                $t = trim(preg_replace('/\s+/', ' ', $node->textContent));
                if ($t !== '') {
                    $texts[] = $t;
                }
            }
        }
        return $texts;
    }
}
