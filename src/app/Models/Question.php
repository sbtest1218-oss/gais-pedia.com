<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'question',
        'answer',
        'session_id',
        'helpful_count',
    ];

    protected $casts = [
        'helpful_count' => 'integer',
    ];

    /**
     * 最新の質問から取得
     */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('created_at');
    }

    /**
     * 役立った数が多い順
     */
    public function scopePopular($query)
    {
        return $query->orderByDesc('helpful_count');
    }
}
