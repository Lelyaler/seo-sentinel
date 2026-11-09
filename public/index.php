<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';

use SeoSentinel\Service\AuditService;
use SeoSentinel\Exception\SeoException;

$targetUrl = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL) ?: filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL) ?: '';
$keyword = filter_input(INPUT_POST, 'keyword', FILTER_DEFAULT) ?: filter_input(INPUT_GET, 'keyword', FILTER_DEFAULT) ?: '';
$isApi = isset($_GET['api']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

$auditResult = null;
$error = null;

if (!empty($targetUrl)) {
    try {
        $service = new AuditService();
        $auditResult = $service->runAudit($targetUrl, (string)$keyword);

        if ($isApi) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($auditResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
    } catch (SeoException $e) {
        $error = $e->getMessage();
        if ($isApi) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => $error], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO Sentinel — AI Search & GEO Copilot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #0f172a; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
</head>
<body class="min-h-full flex flex-col font-sans antialiased selection:bg-brand-500 selection:text-white">

    <!-- Header Navigation -->
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 via-indigo-500 to-emerald-400 p-[1px] shadow-lg shadow-brand-500/20">
                    <div class="w-full h-full bg-slate-950 rounded-[11px] flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-base font-bold tracking-tight text-white flex items-center gap-2">
                        SEO Sentinel
                        <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">AI Search & GEO</span>
                    </h1>
                    <p class="text-xs text-slate-400">Мультиагентный SEO-аудитор & Генератор Schema.org</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-1.5 text-xs text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    PHP 8.2 Engine Active
                </span>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <!-- Input Form Card -->
        <section class="bg-gradient-to-b from-slate-900 to-slate-900/40 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="max-w-3xl space-y-3">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Глубокий аудит готовности к <span class="bg-gradient-to-r from-indigo-400 to-emerald-400 bg-clip-text text-transparent">Google & AI-поиску</span>
                </h2>
                <p class="text-sm text-slate-400 leading-relaxed">
                    Проверьте страницу на соответствие алгоритмам поисковых систем и готовность к цитированию в Perplexity, ChatGPT и Google AI Overviews.
                </p>
            </div>

            <form method="POST" action="/" class="mt-6 grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-7">
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">Целевой URL страницы</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </div>
                        <input type="url" name="url" required placeholder="https://example.com/services/web-dev" value="<?= htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8') ?>"
                               class="w-full pl-10 pr-4 py-3 bg-slate-950/80 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">Ключевой запрос (SEO)</label>
                    <input type="text" name="keyword" placeholder="разработка сайтов" value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-3 bg-slate-950/80 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                </div>

                <div class="md:col-span-2 flex items-end">
                    <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-brand-500/25 transition active:scale-[0.98] flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Анализ
                    </button>
                </div>
            </form>

            <!-- Quick presets -->
            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                <span>Быстрый тест:</span>
                <button onclick="setSample('https://wordpress.org', 'content management system')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 transition">WordPress.org</button>
                <button onclick="setSample('https://github.com', 'developer platform git')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 transition">GitHub.com</button>
            </div>
        </section>

        <?php if ($error): ?>
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if ($auditResult): ?>
            <!-- Score Cards Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Overall SEO Score -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">SEO Health Score</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-xs">SEO</div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold text-white"><?= $auditResult['scores']['seo_health_score'] ?></span>
                        <span class="text-slate-500 font-medium text-sm">/ 100</span>
                    </div>
                    <div class="mt-3 w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full rounded-full transition-all duration-700" style="width: <?= $auditResult['scores']['seo_health_score'] ?>%"></div>
                    </div>
                </div>

                <!-- Card 2: GEO & AI Search Score -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Индекс AI Search (GEO)</span>
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold text-xs">AI</div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold text-white"><?= $auditResult['scores']['geo_ai_score'] ?></span>
                        <span class="text-slate-500 font-medium text-sm">/ 100</span>
                        <span class="ml-auto text-xs font-semibold px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300"><?= $auditResult['scores']['geo_grade'] ?></span>
                    </div>
                    <div class="mt-3 w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-brand-500 to-indigo-400 h-full rounded-full transition-all duration-700" style="width: <?= $auditResult['scores']['geo_ai_score'] ?>%"></div>
                    </div>
                </div>

                <!-- Card 3: Content Depth -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Объем текста</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-xs">TXT</div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold text-white"><?= number_format($auditResult['page']['content']['word_count']) ?></span>
                        <span class="text-slate-500 text-sm">слов</span>
                    </div>
                    <p class="mt-3 text-xs text-slate-400">Бенчмарк ТОП-3: <span class="text-slate-200 font-medium"><?= $auditResult['competitors_gap']['benchmark']['target_word_count'] ?> слов</span></p>
                </div>

                <!-- Card 4: Load Speed -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Отклик сервера</span>
                        <div class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center font-bold text-xs">SEC</div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold text-white"><?= $auditResult['meta']['load_time_seconds'] ?></span>
                        <span class="text-slate-500 text-sm">сек</span>
                    </div>
                    <p class="mt-3 text-xs <?= $auditResult['meta']['load_time_seconds'] < 1.5 ? 'text-emerald-400' : 'text-amber-400' ?>">
                        <?= $auditResult['meta']['load_time_seconds'] < 1.5 ? '⚡ Высокая скорость загрузки' : '⚠️ Рекомендуется оптимизация TTFB' ?>
                    </p>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-slate-800 flex gap-4 overflow-x-auto custom-scrollbar">
                <button onclick="switchTab('tab-meta')" id="btn-tab-meta" class="tab-btn px-4 py-3 font-semibold text-sm border-b-2 border-brand-500 text-brand-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Мета-теги и структура
                </button>
                <button onclick="switchTab('tab-gap')" id="btn-tab-gap" class="tab-btn px-4 py-3 font-semibold text-sm border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Content Gap & LSI
                </button>
                <button onclick="switchTab('tab-schema')" id="btn-tab-schema" class="tab-btn px-4 py-3 font-semibold text-sm border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    Микроразметка Schema.org
                </button>
                <button onclick="switchTab('tab-geo')" id="btn-tab-geo" class="tab-btn px-4 py-3 font-semibold text-sm border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    GEO: Готовность к AI-поиску
                </button>
            </div>

            <!-- Tab 1: Meta Tags & Structure Content -->
            <div id="tab-meta" class="tab-pane space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Title & Description Inspection -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span>Основные мета-теги</span>
                        </h3>
                        <!-- Title -->
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-400 uppercase tracking-wider">Тег &lt;title&gt;</span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold <?= $auditResult['page']['title']['status'] === 'good' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' ?>">
                                    <?= $auditResult['page']['title']['length'] ?> симв.
                                </span>
                            </div>
                            <p class="text-sm font-medium text-white"><?= htmlspecialchars($auditResult['page']['title']['text'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <!-- Meta Description -->
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-400 uppercase tracking-wider">Meta Description</span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold <?= $auditResult['page']['meta_description']['status'] === 'good' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' ?>">
                                    <?= $auditResult['page']['meta_description']['length'] ?> симв.
                                </span>
                            </div>
                            <p class="text-sm text-slate-300"><?= htmlspecialchars($auditResult['page']['meta_description']['text'] ?: '— Отсутствует (рекомендуется добавить 120-160 симв.)', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <!-- Canonical & Robots -->
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800">
                                <span class="text-slate-500 font-medium block">Canonical:</span>
                                <span class="text-slate-200 truncate block mt-0.5"><?= htmlspecialchars($auditResult['page']['canonical']['url'] ?: 'Не указан', ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800">
                                <span class="text-slate-500 font-medium block">Robots:</span>
                                <span class="text-slate-200 truncate block mt-0.5"><?= htmlspecialchars($auditResult['page']['robots'] ?: 'all / index, follow', ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Headings & Media Inspection -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                        <h3 class="text-base font-bold text-white">Структура заголовков (H1-H3)</h3>
                        <!-- H1 -->
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-indigo-400 uppercase tracking-wider">Заголовок H1</span>
                                <span class="text-slate-400 text-[11px]"><?= $auditResult['page']['headings']['counts']['h1'] ?> на странице</span>
                            </div>
                            <?php if (empty($auditResult['page']['headings']['h1'])): ?>
                                <p class="text-sm text-rose-400 font-medium">⚠️ H1 не найден на странице!</p>
                            <?php else: ?>
                                <?php foreach ($auditResult['page']['headings']['h1'] as $h1): ?>
                                    <p class="text-sm font-semibold text-white"><?= htmlspecialchars($h1, ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Image ALTs check -->
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Изображения (ALT-теги)</span>
                                <p class="text-xs text-slate-400 mt-1">Всего: <span class="text-white font-medium"><?= $auditResult['page']['images']['total'] ?></span> | Без описания ALT: <span class="<?= $auditResult['page']['images']['missing_alt'] > 0 ? 'text-amber-400 font-semibold' : 'text-emerald-400' ?>"><?= $auditResult['page']['images']['missing_alt'] ?></span></p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $auditResult['page']['images']['missing_alt'] === 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' ?>">
                                <?= $auditResult['page']['images']['missing_alt'] === 0 ? 'Все ALT заполнены' : 'Требует внимания' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Content Gap & LSI Content -->
            <div id="tab-gap" class="tab-pane hidden space-y-6">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-white">Анализ упущенного контента (Content Gap vs ТОП выдачи)</h3>
                        <p class="text-sm text-slate-400 mt-1">Сравнение вашей страницы с обязательными смысловыми блоками лидеров поисковой выдачи.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($auditResult['competitors_gap']['content_gap']['missing_sections'] as $sec): ?>
                            <div class="p-4 rounded-xl bg-rose-500/5 border border-rose-500/20 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-rose-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Блок отсутствует: <?= strtoupper($sec['key']) ?>
                                    </span>
                                    <span class="text-[11px] text-slate-400"><?= $sec['importance'] ?></span>
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed"><?= $sec['desc'] ?></p>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach ($auditResult['competitors_gap']['content_gap']['present_sections'] as $sec): ?>
                            <div class="p-4 rounded-xl bg-emerald-500/5 border border-emerald-500/20 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Блок найден: <?= strtoupper($sec['key']) ?>
                                    </span>
                                    <span class="text-[11px] text-emerald-400/80">Присутствует</span>
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed"><?= $sec['desc'] ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- LSI Suggestions -->
                    <div class="pt-4 border-t border-slate-800 space-y-3">
                        <h4 class="text-sm font-bold text-slate-200">Рекомендуемые LSI-фразы для включения в подзаголовки и текст:</h4>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($auditResult['competitors_gap']['lsi_suggestions'] as $lsi): ?>
                                <span onclick="copyText('<?= htmlspecialchars($lsi, ENT_QUOTES, 'UTF-8') ?>')" class="cursor-pointer px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-700/60 rounded-lg text-xs text-indigo-300 font-mono transition flex items-center gap-1.5" title="Нажмите, чтобы скопировать">
                                    <?= htmlspecialchars($lsi, ENT_QUOTES, 'UTF-8') ?>
                                    <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Schema.org Generator Content -->
            <div id="tab-schema" class="tab-pane hidden space-y-6">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-white">Сгенерированная семантическая микроразметка Schema.org</h3>
                            <p class="text-sm text-slate-400 mt-1">Вставьте этот готовый код в раздел &lt;head&gt; или перед закрывающим тегом &lt;/body&gt;.</p>
                        </div>
                    </div>

                    <!-- Subtabs for Schemas -->
                    <div class="space-y-4">
                        <!-- FAQ Schema -->
                        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    <span class="text-sm font-bold text-white">FAQPage (Вопросы и ответы для Rich Snippets)</span>
                                </div>
                                <button onclick="copySnippet('faq-code')" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    Копировать JSON-LD
                                </button>
                            </div>
                            <pre id="faq-code" class="p-4 bg-slate-900/90 rounded-xl text-xs font-mono text-indigo-300 overflow-x-auto custom-scrollbar border border-slate-800"><?= htmlspecialchars($auditResult['schema_generated']['faq']['snippet'], ENT_QUOTES, 'UTF-8') ?></pre>
                        </div>

                        <!-- Service Schema -->
                        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                                    <span class="text-sm font-bold text-white">Service (Разметка коммерческой услуги)</span>
                                </div>
                                <button onclick="copySnippet('service-code')" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    Копировать JSON-LD
                                </button>
                            </div>
                            <pre id="service-code" class="p-4 bg-slate-900/90 rounded-xl text-xs font-mono text-indigo-300 overflow-x-auto custom-scrollbar border border-slate-800"><?= htmlspecialchars($auditResult['schema_generated']['service']['snippet'], ENT_QUOTES, 'UTF-8') ?></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: GEO & AI Search Content -->
            <div id="tab-geo" class="tab-pane hidden space-y-6">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-white">GEO (Generative Engine Optimization) — Готовность к AI-поиску</h3>
                        <p class="text-sm text-slate-400 mt-1">Как Perplexity, ChatGPT Search и Google AI Overviews воспринимают вашу страницу.</p>
                    </div>

                    <!-- Strengths -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-bold text-emerald-400 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Сильные стороны (положительные сигналы):
                        </h4>
                        <div class="space-y-2">
                            <?php foreach ($auditResult['geo_readiness']['strengths'] as $str): ?>
                                <div class="p-3.5 rounded-xl bg-emerald-500/5 border border-emerald-500/20 text-xs text-slate-200">
                                    <?= htmlspecialchars($str, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Actionable Checklist -->
                    <div class="space-y-3 pt-3 border-t border-slate-800">
                        <h4 class="text-sm font-bold text-indigo-400 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            План действий для повышения вероятности цитирования нейросетями:
                        </h4>
                        <div class="space-y-2">
                            <?php foreach ($auditResult['geo_readiness']['recommendations'] as $rec): ?>
                                <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-300 flex items-start gap-2.5">
                                    <span class="text-brand-400 font-bold">👉</span>
                                    <span><?= htmlspecialchars($rec, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 bg-slate-950/80 py-6 text-center text-xs text-slate-500">
        <p>SEO Sentinel • Полнофункциональный AI & GEO аудит на чистом PHP 8.2 • Разработано для портфолио</p>
    </footer>

    <!-- Scripts -->
    <script>
        function setSample(url, kw) {
            document.querySelector('input[name="url"]').value = url;
            document.querySelector('input[name="keyword"]').value = kw;
        }

        function switchTab(tabId) {
            document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-brand-500', 'text-brand-400');
                btn.classList.add('border-transparent', 'text-slate-400');
            });

            const activeTab = document.getElementById(tabId);
            if (activeTab) activeTab.classList.remove('hidden');

            const activeBtn = document.getElementById('btn-' + tabId);
            if (activeBtn) {
                activeBtn.classList.remove('border-transparent', 'text-slate-400');
                activeBtn.classList.add('border-brand-500', 'text-brand-400');
            }
        }

        function copySnippet(elementId) {
            const text = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('Скопировано в буфер обмена!');
            });
        }

        function copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Фраза скопирована: ' + text);
            });
        }
    </script>
</body>
</html>
