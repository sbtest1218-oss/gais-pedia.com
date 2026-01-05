<?php

namespace App\Services;

use App\Models\GaisPage;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * Fallback URLs if DB is empty
     */
    private array $fallbackUrls = [
        'https://gais.jp/',
        'https://gais.jp/information/',
        'https://gais.jp/official_member_information/',
        'https://gais.jp/member_rule/',
        'https://gais.jp/inquiry/',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-2.5-flash');
    }

    /**
     * Send a chat message to Gemini API
     *
     * @param string $message User's message
     * @param array $history Previous conversation history
     * @param string|null $systemPrompt System instruction for the AI
     * @param array $tools Tools to enable (google_search, url_context)
     * @return array
     */
    public function chat(string $message, array $history = [], ?string $systemPrompt = null, array $tools = []): array
    {
        $url = "{$this->baseUrl}/models/{$this->model}:generateContent";

        // Build contents array with history and new message
        $contents = [];

        // Add conversation history
        foreach ($history as $item) {
            $contents[] = [
                'role' => $item['role'],
                'parts' => [['text' => $item['content']]]
            ];
        }

        // Add the new user message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]]
        ];

        // Build request payload
        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'topP' => 0.95,
                'topK' => 40,
                'maxOutputTokens' => 4096,
            ]
        ];

        // Add system instruction if provided
        if ($systemPrompt) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $systemPrompt]]
            ];
        }

        // Add tools if provided
        if (!empty($tools)) {
            $payload['tools'] = $tools;
        }

        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->apiKey,
            ])->timeout(120)->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();

                // Extract the response text
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($text) {
                    return [
                        'success' => true,
                        'message' => $text,
                        'usage' => $data['usageMetadata'] ?? null,
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'No response generated',
                    'error' => 'empty_response',
                ];
            }

            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'API request failed',
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];

        } catch (\Exception $e) {
            Log::error('Gemini API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to connect to Gemini API',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Chat with 2-step approach:
     * 1. Select relevant URLs from DB using Gemini
     * 2. Fetch those URLs with url_context and answer
     *
     * @param string $message User's message
     * @param array $history Previous conversation history
     * @return array
     */
    public function simpleChat(string $message, array $history = []): array
    {
        // Step 1: Get page list from DB and select relevant URLs
        $relevantUrls = $this->selectRelevantUrls($message);

        if (empty($relevantUrls)) {
            $relevantUrls = $this->fallbackUrls;
        }

        $urlList = implode("\n", $relevantUrls);

        // Step 2: Fetch URLs and answer
        $promptWithUrls = <<<PROMPT
以下のgais.jp公式ページを参照して、ユーザーの質問に回答してください。

【参照ページ】
{$urlList}

【ユーザーの質問】
{$message}
PROMPT;

        $systemPrompt = <<<SYSTEM
あなたは「GAISペディア」、生成AI協会（GAIS）の公式ナレッジアシスタントです。

【最重要】必ず日本語で回答してください。英語での回答は禁止です。

【ルール】
1. 提供されたgais.jpのページ内容のみを参照して回答
2. 該当する情報がない場合は「該当する情報が見つかりませんでした」と回答
3. 過去のイベントは「終了済み」と明記
4. 回答の最後に参照したページのURLを記載
SYSTEM;

        $tools = [
            ['url_context' => new \stdClass()]
        ];

        return $this->chat($promptWithUrls, $history, $systemPrompt, $tools);
    }

    /**
     * Select relevant URLs from DB using Gemini
     *
     * @param string $message User's question
     * @return array Selected URLs (max 3)
     */
    private function selectRelevantUrls(string $message): array
    {
        // Get all pages from DB (newest first), exclude AI news
        $pages = GaisPage::active()
            ->where('title', 'not like', '%生成AIニュース%')
            ->where('url', 'not like', '%ai-news%')
            ->orderByDesc('event_date')
            ->orderByDesc('published_at')
            ->get();

        if ($pages->isEmpty()) {
            Log::warning('No pages in DB, using fallback URLs');
            return $this->fallbackUrls;
        }

        // Build page list for Gemini with rich info
        $pageList = [];
        foreach ($pages as $page) {
            $line = "[{$page->id}] {$page->title}";

            // 日付情報
            if ($page->event_date) {
                $line .= " [開催:{$page->event_date->format('Y/m/d')}]";
            } elseif ($page->published_at) {
                $line .= " [投稿:{$page->published_at->format('Y/m/d')}]";
            }

            // 内容（150文字まで）
            if ($page->content) {
                $line .= "\n  " . mb_substr($page->content, 0, 150);
            }

            $pageList[] = $line;
        }
        $pageListText = implode("\n", $pageList);

        // Ask Gemini to select relevant pages
        $selectPrompt = <<<PROMPT
以下はgais.jp（生成AI協会）のページ一覧です。
ユーザーの質問に回答するために最も関連性の高いページを最大3つ選んでください。

【ページ一覧】
{$pageListText}

【ユーザーの質問】
{$message}

【注意】
- 「次回」「今後」の質問には、開催日が未来のページを優先
- 最新の情報が必要な場合は、投稿日が新しいページを優先
- 基本情報（会員、入会など）の質問には該当ページを選択
- 最大3つまで。本当に関連性の高いものだけを選ぶ

【回答形式】
関連するページのIDをカンマ区切りで出力してください。
例: 1,5,12
IDのみを出力し、説明は不要です。
PROMPT;

        $result = $this->chat($selectPrompt, [], 'あなたはページ選択アシスタントです。指示に従ってIDのみを出力してください。', []);

        if (!$result['success']) {
            Log::warning('Failed to select URLs', ['error' => $result['error'] ?? 'unknown']);
            return $this->fallbackUrls;
        }

        // Parse selected IDs
        $responseText = $result['message'];
        preg_match_all('/\d+/', $responseText, $matches);
        $selectedIds = array_map('intval', $matches[0] ?? []);
        $selectedIds = array_slice($selectedIds, 0, 3); // Limit to 3

        if (empty($selectedIds)) {
            return $this->fallbackUrls;
        }

        // Get URLs for selected IDs
        $selectedPages = GaisPage::whereIn('id', $selectedIds)->get();
        $urls = $selectedPages->pluck('url')->toArray();

        Log::info('Selected URLs', ['count' => count($urls), 'ids' => $selectedIds]);

        return $urls;
    }
}
