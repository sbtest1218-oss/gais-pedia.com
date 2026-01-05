<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('question');              // 質問内容
            $table->text('answer');                // 回答内容
            $table->string('session_id', 100)->nullable(); // セッション識別用
            $table->integer('helpful_count')->default(0);  // 役立った数
            $table->timestamps();

            $table->index('created_at');
            $table->index('helpful_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
