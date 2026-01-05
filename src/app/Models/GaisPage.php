<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaisPage extends Model
{
    protected $fillable = [
        'url',
        'title',
        'content',
        'published_at',
        'event_date',
        'category',
        'tags',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'date',
        'event_date' => 'date',
    ];

    /**
     * アクティブなページのみ取得
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 全ページの要約を取得（Geminiに渡す用）
     * フォーマット: [ID] タイトル (日付) - 内容
     */
    public static function getAllSummaries(): string
    {
        $pages = self::active()->orderBy('published_at', 'desc')->get();

        $lines = [];
        foreach ($pages as $page) {
            $line = "[{$page->id}] {$page->title}";

            // 日付情報を追加
            if ($page->event_date) {
                $line .= " [開催:{$page->event_date->format('Y/m/d')}]";
            } elseif ($page->published_at) {
                $line .= " [投稿:{$page->published_at->format('Y/m/d')}]";
            }

            // 内容を追加
            if ($page->content) {
                $line .= "\n  " . mb_substr($page->content, 0, 150);
            }

            $lines[] = $line;
        }

        return implode("\n", $lines);
    }
}
