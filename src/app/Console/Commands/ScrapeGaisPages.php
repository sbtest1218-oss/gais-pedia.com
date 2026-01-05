<?php

namespace App\Console\Commands;

use App\Models\GaisPage;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapeGaisPages extends Command
{
    protected $signature = 'gais:scrape {--force : 既存データを上書き}';
    protected $description = 'gais.jpのサイトマップからページ情報をスクレイピング';

    // 除外するURLパターン（不要ページ）
    private array $excludePatterns = [
        'archive',       // アーカイブページ（/archive, -archive両方）
        '-digest',       // ダイジェスト版
        '/login',        // ログインページ
        '/register',     // 登録ページ
        '/mypage',       // マイページ
        '/account',      // アカウント
        '/bk2_',         // バックアップ
        '/sitemap.html', // サイトマップHTML
        '/for-members-only', // 会員限定
        '/ai-news',      // 生成AIニュース（トークン節約）
        'category/ai-news', // 生成AIニュースカテゴリ
    ];

    public function handle(): int
    {
        $this->info('サイトマップからURL取得中...');

        $urls = $this->fetchSitemapUrls();
        $this->info("取得したURL数: " . count($urls));

        $bar = $this->output->createProgressBar(count($urls));
        $bar->start();

        $saved = 0;
        $skipped = 0;

        foreach ($urls as $url) {
            // 除外パターンチェック
            if ($this->shouldExclude($url)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // 既存チェック（forceオプションなしの場合）
            if (!$this->option('force') && GaisPage::where('url', $url)->exists()) {
                $bar->advance();
                continue;
            }

            // ページ情報取得
            $pageInfo = $this->scrapePageInfo($url);

            if ($pageInfo) {
                GaisPage::updateOrCreate(
                    ['url' => $url],
                    [
                        'title' => $pageInfo['title'],
                        'content' => $pageInfo['content'],
                        'published_at' => $pageInfo['published_at'],
                        'event_date' => $pageInfo['event_date'],
                        'category' => $pageInfo['category'],
                        'tags' => $pageInfo['tags'],
                        'is_active' => true,
                    ]
                );
                $saved++;
            }

            $bar->advance();

            // レート制限対策
            usleep(300000); // 0.3秒待機
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("保存: {$saved}件, スキップ: {$skipped}件");

        return Command::SUCCESS;
    }

    /**
     * サイトマップからURL取得
     */
    private function fetchSitemapUrls(): array
    {
        $urls = [];
        $sitemapIndexUrl = 'https://gais.jp/sitemap.xml';

        try {
            $indexResponse = Http::timeout(30)->get($sitemapIndexUrl);
            if (!$indexResponse->successful()) {
                $this->error('サイトマップインデックスの取得に失敗');
                return [];
            }

            $indexXml = simplexml_load_string($indexResponse->body());
            if (!$indexXml) {
                return [];
            }

            foreach ($indexXml->sitemap as $sitemap) {
                $sitemapUrl = (string) $sitemap->loc;
                $response = Http::timeout(30)->get($sitemapUrl);

                if (!$response->successful()) {
                    continue;
                }

                $xml = simplexml_load_string($response->body());
                if (!$xml) {
                    continue;
                }

                foreach ($xml->url as $url) {
                    $urls[] = (string) $url->loc;
                }
            }

        } catch (\Exception $e) {
            $this->error('サイトマップ取得エラー: ' . $e->getMessage());
        }

        return $urls;
    }

    /**
     * ページ情報をスクレイピング
     */
    private function scrapePageInfo(string $url): ?array
    {
        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                Log::warning("ページ取得失敗: {$url}");
                return null;
            }

            $html = $response->body();

            return [
                'title' => $this->extractTitle($html),
                'content' => $this->extractContent($html),
                'published_at' => $this->extractPublishedDate($html),
                'event_date' => $this->extractEventDate($html),
                'category' => $this->extractCategory($html),
                'tags' => $this->extractTags($html),
            ];

        } catch (\Exception $e) {
            Log::warning("スクレイピングエラー: {$url} - " . $e->getMessage());
            return null;
        }
    }

    /**
     * タイトル抽出
     */
    private function extractTitle(string $html): string
    {
        // og:title優先
        if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return mb_substr(trim($matches[1]), 0, 255);
        }

        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $matches)) {
            $title = trim($matches[1]);
            // " | GAIS" などのサフィックスを削除
            $title = preg_replace('/\s*[\|│\-]\s*(GAIS|生成AI協会).*$/u', '', $title);
            return mb_substr($title, 0, 255);
        }
        return 'タイトルなし';
    }

    /**
     * コンテンツ抽出（詳しい内容）
     */
    private function extractContent(string $html): ?string
    {
        // まずmeta descriptionを試す
        $desc = '';
        if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            $desc = trim($matches[1]);
        } elseif (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            $desc = trim($matches[1]);
        }

        // 本文から追加情報を抽出
        $bodyContent = $this->extractBodyText($html);

        // 開催情報を抽出
        $eventInfo = $this->extractEventInfo($html);

        // 組み合わせ
        $content = '';
        if (!empty($desc)) {
            $content = $desc;
        }
        if (!empty($eventInfo)) {
            $content .= ($content ? ' ' : '') . $eventInfo;
        }
        if (empty($content) && !empty($bodyContent)) {
            $content = $bodyContent;
        }

        return $content ? mb_substr($content, 0, 1000) : null;
    }

    /**
     * 本文テキスト抽出
     */
    private function extractBodyText(string $html): string
    {
        // article または main タグ内を優先
        if (preg_match('/<article[^>]*>(.*?)<\/article>/is', $html, $matches)) {
            $text = strip_tags($matches[1]);
        } elseif (preg_match('/<main[^>]*>(.*?)<\/main>/is', $html, $matches)) {
            $text = strip_tags($matches[1]);
        } else {
            $text = strip_tags($html);
        }

        // 改行・空白を整理
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        return mb_substr($text, 0, 600);
    }

    /**
     * イベント情報抽出（日時、場所など）
     */
    private function extractEventInfo(string $html): string
    {
        $info = [];

        // 日時パターン
        if (preg_match('/(\d{4}年\d{1,2}月\d{1,2}日[（(][月火水木金土日][）)]\s*\d{1,2}:\d{2})/u', $html, $matches)) {
            $info[] = "日時:{$matches[1]}";
        }

        // 場所パターン
        if (preg_match('/(?:会場|場所)[：:]\s*([^<\n]+)/u', $html, $matches)) {
            $info[] = "場所:" . mb_substr(trim($matches[1]), 0, 50);
        }

        // 講演者パターン
        if (preg_match_all('/(?:講演|登壇)[者]?[：:]\s*([^<\n]+)/u', $html, $matches)) {
            $speakers = array_slice($matches[1], 0, 3);
            $info[] = "登壇:" . implode(',', array_map('trim', $speakers));
        }

        return implode(' / ', $info);
    }

    /**
     * 公開日抽出
     */
    private function extractPublishedDate(string $html): ?string
    {
        // JSON-LDから抽出
        if (preg_match('/"datePublished"\s*:\s*"([^"]+)"/', $html, $matches)) {
            return $this->parseDate($matches[1]);
        }

        // meta article:published_time
        if (preg_match('/<meta[^>]+property=["\']article:published_time["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return $this->parseDate($matches[1]);
        }

        // 日本語日付パターン（投稿日: 2025年1月30日）
        if (preg_match('/(?:投稿日|公開日|掲載日)[：:]\s*(\d{4}年\d{1,2}月\d{1,2}日)/u', $html, $matches)) {
            return $this->parseDate($matches[1]);
        }

        return null;
    }

    /**
     * イベント開催日抽出
     */
    private function extractEventDate(string $html): ?string
    {
        // 開催日パターン
        if (preg_match('/(?:開催日|日時)[：:]\s*(\d{4}年\d{1,2}月\d{1,2}日)/u', $html, $matches)) {
            return $this->parseDate($matches[1]);
        }

        // 「○月○日（曜日）開催」パターン
        if (preg_match('/(\d{1,2})\/(\d{1,2})\s*[（(][月火水木金土日][）)]\s*開催/u', $html, $matches)) {
            $year = date('Y');
            return "{$year}-{$matches[1]}-{$matches[2]}";
        }

        // 「2026年1月13日（火）」パターン
        if (preg_match('/(\d{4})年(\d{1,2})月(\d{1,2})日[（(][月火水木金土日][）)]/u', $html, $matches)) {
            return "{$matches[1]}-{$matches[2]}-{$matches[3]}";
        }

        return null;
    }

    /**
     * カテゴリ抽出
     */
    private function extractCategory(string $html): ?string
    {
        // JSON-LDのcategory
        if (preg_match('/"articleSection"\s*:\s*"([^"]+)"/', $html, $matches)) {
            return mb_substr($matches[1], 0, 100);
        }

        // カテゴリリンクから抽出
        if (preg_match('/href="[^"]*\/category\/([^"\/]+)\/?"[^>]*>([^<]+)</i', $html, $matches)) {
            return mb_substr(trim($matches[2]), 0, 100);
        }

        return null;
    }

    /**
     * タグ抽出
     */
    private function extractTags(string $html): ?string
    {
        $tags = [];

        // JSON-LDのkeywords
        if (preg_match('/"keywords"\s*:\s*"([^"]+)"/', $html, $matches)) {
            $tags = array_merge($tags, explode(',', $matches[1]));
        }

        // タグリンクから抽出
        if (preg_match_all('/href="[^"]*\/tag\/([^"\/]+)\/?"[^>]*>([^<]+)</i', $html, $matches)) {
            $tags = array_merge($tags, $matches[2]);
        }

        $tags = array_unique(array_map('trim', $tags));
        $tags = array_slice($tags, 0, 10);

        return !empty($tags) ? mb_substr(implode(',', $tags), 0, 255) : null;
    }

    /**
     * 日付パース
     */
    private function parseDate(string $dateStr): ?string
    {
        try {
            // ISO 8601形式
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $dateStr)) {
                return Carbon::parse($dateStr)->format('Y-m-d');
            }

            // 日本語形式（2025年1月30日）
            if (preg_match('/(\d{4})年(\d{1,2})月(\d{1,2})日/', $dateStr, $matches)) {
                return sprintf('%04d-%02d-%02d', $matches[1], $matches[2], $matches[3]);
            }

            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 除外すべきURLかチェック
     */
    private function shouldExclude(string $url): bool
    {
        foreach ($this->excludePatterns as $pattern) {
            if (stripos($url, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
}
