<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gais_pages', function (Blueprint $table) {
            $table->id();
            $table->string('url', 500)->unique();
            $table->string('title', 255);
            $table->text('content')->nullable();        // 詳しい内容（500文字程度）
            $table->date('published_at')->nullable();   // 投稿日
            $table->date('event_date')->nullable();     // イベント開催日
            $table->string('category', 100)->nullable(); // カテゴリ
            $table->string('tags', 255)->nullable();    // タグ（カンマ区切り）
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('published_at');
            $table->index('event_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gais_pages');
    }
};
