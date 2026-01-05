<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    /**
     * みんなの質問一覧ページ
     */
    public function index(Request $request): View
    {
        $sort = $request->input('sort', 'latest');

        $query = Question::query();

        if ($sort === 'popular') {
            $query->popular();
        } else {
            $query->latest();
        }

        $questions = $query->paginate(20)->appends(['sort' => $sort]);

        return view('questions.index', compact('questions'));
    }

    /**
     * 質問を保存（チャットから呼ばれる）
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:2000',
            'answer' => 'required|string|max:10000',
        ]);

        $question = Question::create([
            'question' => $request->input('question'),
            'answer' => $request->input('answer'),
            'session_id' => $request->ip(), // IPアドレスで識別
        ]);

        return response()->json([
            'success' => true,
            'id' => $question->id,
        ]);
    }

    /**
     * 役立ったカウントをトグル（増減）
     */
    public function helpful(Request $request, Question $question): JsonResponse
    {
        $action = $request->input('action', 'add'); // 'add' or 'remove'

        if ($action === 'remove' && $question->helpful_count > 0) {
            $question->decrement('helpful_count');
        } else {
            $question->increment('helpful_count');
        }

        return response()->json([
            'success' => true,
            'helpful_count' => $question->helpful_count,
            'action' => $action,
        ]);
    }

    /**
     * APIで質問一覧取得
     */
    public function list(Request $request): JsonResponse
    {
        $sort = $request->input('sort', 'latest');

        $query = Question::query();

        if ($sort === 'popular') {
            $query->popular();
        } else {
            $query->latest();
        }

        $questions = $query->paginate(20);

        return response()->json([
            'success' => true,
            'questions' => $questions->items(),
            'pagination' => [
                'current_page' => $questions->currentPage(),
                'last_page' => $questions->lastPage(),
                'total' => $questions->total(),
            ],
        ]);
    }
}
