<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>みんなの質問 - GAISペディア</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🤖</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Header */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            animation: fadeInDown 0.5s ease-out;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .back-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #495057;
        }

        .back-btn:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
            transform: translateX(-2px);
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title .icon {
            font-size: 32px;
        }

        .page-title h1 {
            font-size: 28px;
            font-weight: 700;
            color: #212529;
        }

        .page-title p {
            font-size: 14px;
            color: #868e96;
            margin-top: 4px;
        }

        /* Sort Buttons */
        .sort-buttons {
            display: flex;
            gap: 8px;
        }

        .sort-btn {
            padding: 10px 16px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #495057;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sort-btn:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
        }

        .sort-btn.active {
            background-color: #339af0;
            border-color: #339af0;
            color: #ffffff;
        }

        /* Question Cards */
        .questions-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .question-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease-out;
            animation-fill-mode: both;
        }

        .question-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .question-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f3f4;
            cursor: pointer;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .question-content {
            flex: 1;
        }

        .question-text {
            font-size: 16px;
            font-weight: 500;
            color: #212529;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .question-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 13px;
            color: #868e96;
        }

        .question-meta .date {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .question-meta .helpful {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #339af0;
        }

        .expand-icon {
            width: 24px;
            height: 24px;
            color: #adb5bd;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }

        .question-card.expanded .expand-icon {
            transform: rotate(180deg);
        }

        /* Answer Section */
        .answer-section {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out;
            background-color: #f8f9fa;
        }

        .question-card.expanded .answer-section {
            max-height: 2000px;
        }

        .answer-content {
            padding: 24px;
        }

        .answer-label {
            font-size: 12px;
            font-weight: 700;
            color: #339af0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .answer-text {
            font-size: 15px;
            color: #495057;
            line-height: 1.8;
        }

        .answer-text a {
            color: #1971c2;
            text-decoration: none;
            border-bottom: 1px solid #a5d8ff;
        }

        .answer-text a:hover {
            color: #1864ab;
            border-bottom-color: #1971c2;
        }

        .answer-text p {
            margin: 8px 0;
        }

        .answer-text ul, .answer-text ol {
            margin: 8px 0;
            padding-left: 24px;
        }

        .answer-text li {
            margin: 4px 0;
        }

        /* Actions */
        .answer-actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e9ecef;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 13px;
            color: #495057;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background-color: #e9ecef;
        }

        .action-btn.helpful-btn.active {
            background-color: #d0ebff;
            border-color: #339af0;
            color: #1971c2;
        }

        .action-btn svg {
            width: 16px;
            height: 16px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            animation: fadeIn 0.5s ease-out;
        }

        .empty-state .icon {
            font-size: 64px;
            margin-bottom: 24px;
        }

        .empty-state h2 {
            font-size: 24px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 12px;
        }

        .empty-state p {
            font-size: 16px;
            color: #868e96;
            margin-bottom: 24px;
        }

        .empty-state .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 24px;
            background-color: #339af0;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            color: #ffffff;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .empty-state .cta-btn:hover {
            background-color: #228be6;
            transform: translateY(-1px);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 32px;
            animation: fadeIn 0.5s ease-out 0.3s;
            animation-fill-mode: both;
        }

        .pagination a, .pagination span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            color: #495057;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .pagination a:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
        }

        .pagination .active span {
            background-color: #339af0;
            border-color: #339af0;
            color: #ffffff;
        }

        .pagination .disabled span {
            color: #adb5bd;
            cursor: not-allowed;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .loading-spinner.active {
            display: block;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e9ecef;
            border-top-color: #339af0;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Stagger animation for cards */
        .question-card:nth-child(1) { animation-delay: 0.1s; }
        .question-card:nth-child(2) { animation-delay: 0.15s; }
        .question-card:nth-child(3) { animation-delay: 0.2s; }
        .question-card:nth-child(4) { animation-delay: 0.25s; }
        .question-card:nth-child(5) { animation-delay: 0.3s; }
        .question-card:nth-child(6) { animation-delay: 0.35s; }
        .question-card:nth-child(7) { animation-delay: 0.4s; }
        .question-card:nth-child(8) { animation-delay: 0.45s; }
        .question-card:nth-child(9) { animation-delay: 0.5s; }
        .question-card:nth-child(10) { animation-delay: 0.55s; }

        /* Dark Mode */
        body.dark-mode {
            background-color: #1a1a2e;
        }

        body.dark-mode .back-btn {
            background-color: #2a2a4a;
            border-color: #3a3a5a;
            color: #e9ecef;
        }

        body.dark-mode .page-title h1 {
            color: #e9ecef;
        }

        body.dark-mode .page-title p {
            color: #868e96;
        }

        body.dark-mode .sort-btn {
            background-color: #2a2a4a;
            border-color: #3a3a5a;
            color: #adb5bd;
        }

        body.dark-mode .sort-btn:hover {
            background-color: #3a3a5a;
        }

        body.dark-mode .sort-btn.active {
            background-color: #339af0;
            border-color: #339af0;
            color: #ffffff;
        }

        body.dark-mode .question-card {
            background-color: #16213e;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        body.dark-mode .question-header {
            border-bottom-color: #2a2a4a;
        }

        body.dark-mode .question-text {
            color: #e9ecef;
        }

        body.dark-mode .answer-section {
            background-color: #1a1a2e;
        }

        body.dark-mode .answer-content {
            border-top-color: #2a2a4a;
        }

        body.dark-mode .answer-text {
            color: #adb5bd;
        }

        body.dark-mode .answer-text a {
            color: #74c0fc;
        }

        body.dark-mode .answer-actions {
            border-top-color: #2a2a4a;
        }

        body.dark-mode .action-btn {
            background-color: #2a2a4a;
            border-color: #3a3a5a;
            color: #adb5bd;
        }

        body.dark-mode .action-btn:hover {
            background-color: #3a3a5a;
        }

        body.dark-mode .pagination a,
        body.dark-mode .pagination span {
            background-color: #2a2a4a;
            border-color: #3a3a5a;
            color: #adb5bd;
        }

        body.dark-mode .empty-state h2 {
            color: #e9ecef;
        }

        body.dark-mode .empty-state p {
            color: #868e96;
        }

        /* Help Popup */
        .help-popup {
            position: fixed;
            bottom: 100px;
            right: 24px;
            width: 340px;
            max-height: 80vh;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            overflow: hidden;
            transform: scale(0.8) translateY(20px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .help-popup.active {
            transform: scale(1) translateY(0);
            opacity: 1;
            visibility: visible;
        }

        .help-popup-close {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 32px;
            height: 32px;
            border: none;
            background-color: rgba(0, 0, 0, 0.05);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #868e96;
            transition: all 0.2s ease;
            z-index: 10;
        }

        .help-popup-close:hover {
            background-color: rgba(0, 0, 0, 0.1);
            color: #495057;
            transform: rotate(90deg);
        }

        .help-popup-content {
            padding: 24px;
            overflow-y: auto;
            max-height: 80vh;
        }

        .help-popup-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .help-icon {
            font-size: 36px;
            animation: helpBounce 2s infinite;
        }

        @keyframes helpBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .help-popup-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #212529;
            margin: 0;
        }

        .help-popup-subtitle {
            font-size: 13px;
            color: #868e96;
            margin-bottom: 20px;
            padding-left: 48px;
        }

        .help-section {
            margin-bottom: 20px;
        }

        .help-section h3 {
            font-size: 14px;
            font-weight: 600;
            color: #339af0;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .section-icon {
            font-size: 16px;
        }

        .help-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .help-list li {
            font-size: 13px;
            color: #495057;
            padding: 6px 0 6px 20px;
            position: relative;
            line-height: 1.5;
        }

        .help-list li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #51cf66;
            font-weight: bold;
        }

        .help-features {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .help-feature {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background-color: #e7f5ff;
            border-radius: 20px;
            font-size: 12px;
            color: #1971c2;
            transition: all 0.2s ease;
        }

        .help-feature:hover {
            transform: scale(1.05);
            background-color: #d0ebff;
        }

        .feature-icon {
            font-size: 14px;
        }

        .help-footer {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px dashed #dee2e6;
            text-align: center;
        }

        .help-footer p {
            font-size: 12px;
            color: #868e96;
        }

        .help-trigger-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #339af0 0%, #228be6 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(51, 154, 240, 0.4);
            cursor: pointer;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .help-trigger-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(51, 154, 240, 0.5);
        }

        .help-trigger-btn span {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
        }

        .help-trigger-btn.active {
            transform: rotate(45deg);
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            box-shadow: 0 4px 15px rgba(238, 90, 90, 0.4);
        }

        body.dark-mode .help-popup {
            background: linear-gradient(135deg, #1e1e3f 0%, #16213e 100%);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        }

        body.dark-mode .help-popup-header h2 {
            color: #e9ecef;
        }

        body.dark-mode .help-popup-subtitle {
            color: #868e96;
        }

        body.dark-mode .help-section h3 {
            color: #74c0fc;
        }

        body.dark-mode .help-list li {
            color: #adb5bd;
        }

        body.dark-mode .help-feature {
            background-color: #2a2a4a;
            color: #74c0fc;
        }

        body.dark-mode .help-footer {
            border-top-color: #3a3a5a;
        }

        body.dark-mode .help-popup-close {
            background-color: rgba(255, 255, 255, 0.1);
            color: #adb5bd;
        }

        /* Responsive */
        @media (max-width: 640px) {
            body {
                padding: 12px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .sort-buttons {
                width: 100%;
            }

            .sort-btn {
                flex: 1;
                justify-content: center;
            }

            .page-title h1 {
                font-size: 22px;
            }

            .question-header {
                padding: 16px;
            }

            .answer-content {
                padding: 16px;
            }

            .help-popup {
                right: 12px;
                left: 12px;
                width: auto;
                bottom: 90px;
            }

            .help-trigger-btn {
                right: 16px;
                bottom: 16px;
                width: 48px;
                height: 48px;
            }

            .help-trigger-btn span {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="page-header">
            <div class="header-left">
                <a href="/" class="back-btn" title="チャットに戻る">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <div class="page-title">
                    <span class="icon">&#128172;</span>
                    <div>
                        <h1>みんなの質問</h1>
                        <p>{{ $questions->total() }}件の質問と回答</p>
                    </div>
                </div>
            </div>
            <div class="sort-buttons">
                <button class="sort-btn active" data-sort="latest">新着順</button>
                <button class="sort-btn" data-sort="popular">人気順</button>
            </div>
        </header>

        <!-- Questions List -->
        @if($questions->count() > 0)
            <div class="questions-list" id="questionsList">
                @foreach($questions as $question)
                    <div class="question-card" data-id="{{ $question->id }}">
                        <div class="question-header">
                            <div class="question-content">
                                <div class="question-text">{{ $question->question }}</div>
                                <div class="question-meta">
                                    <span class="date">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                                            <path d="M16 2V6M8 2V6M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        {{ $question->created_at->format('Y/m/d H:i') }}
                                    </span>
                                    @if($question->helpful_count > 0)
                                        <span class="helpful">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M14 9V5C14 3.89543 13.1046 3 12 3H12.0001C10.8955 3 10 3.89543 10 5V9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M4 14H5.67568C6.1651 14 6.63772 14.1684 7.01138 14.4761L9.02272 16.1358C9.59969 16.6107 10.4003 16.6107 10.9773 16.1358L12.9886 14.4761C13.3623 14.1684 13.8349 14 14.3243 14H20V21H4V14Z" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                            {{ $question->helpful_count }}人が役立った
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <svg class="expand-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="answer-section">
                            <div class="answer-content">
                                <div class="answer-label">回答</div>
                                <div class="answer-text">{!! \Illuminate\Support\Str::markdown($question->answer) !!}</div>
                                <div class="answer-actions">
                                    <button class="action-btn helpful-btn" data-id="{{ $question->id }}">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M7 22V11M2 13V20C2 21.1046 2.89543 22 4 22H17.4262C18.907 22 20.1662 20.9197 20.3914 19.4562L21.4683 12.4562C21.7479 10.6389 20.3418 9 18.5032 9H15C14.4477 9 14 8.55228 14 8V4.46584C14 3.10399 12.896 2 11.5342 2C11.2093 2 10.915 2.1913 10.7831 2.48812L7.26394 10.4061C7.10344 10.7673 6.74532 11 6.35013 11H4C2.89543 11 2 11.8954 2 13Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        役に立った
                                    </button>
                                    <button class="action-btn copy-btn" data-question="{{ $question->question }}">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect x="9" y="9" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2"/>
                                            <path d="M5 15H4C2.89543 15 2 14.1046 2 13V4C2 2.89543 2.89543 2 4 2H13C14.1046 2 15 2.89543 15 4V5" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                        質問をコピー
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pagination">
                {{ $questions->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="icon">&#128566;</div>
                <h2>まだ質問がありません</h2>
                <p>GAISペディアに質問すると、ここに表示されます</p>
                <a href="/" class="cta-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    質問してみる
                </a>
            </div>
        @endif

        <!-- Loading Spinner -->
        <div class="loading-spinner" id="loadingSpinner">
            <div class="spinner"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const questionCards = document.querySelectorAll('.question-card');
            const sortButtons = document.querySelectorAll('.sort-btn');
            const helpfulClicked = new Set(JSON.parse(localStorage.getItem('gaispedia_helpful_clicked') || '[]'));

            // Dark mode from localStorage
            const savedTheme = localStorage.getItem('gaispedia_theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-mode');
            }

            // Initialize helpful button states
            document.querySelectorAll('.helpful-btn').forEach(btn => {
                const id = parseInt(btn.dataset.id);
                if (helpfulClicked.has(id)) {
                    btn.classList.add('active');
                }
            });

            // Toggle question card expansion
            questionCards.forEach(card => {
                const header = card.querySelector('.question-header');
                header.addEventListener('click', () => {
                    card.classList.toggle('expanded');
                });
            });

            // Helpful button click (toggle)
            document.querySelectorAll('.helpful-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    const id = parseInt(btn.dataset.id);
                    const isActive = helpfulClicked.has(id);
                    const action = isActive ? 'remove' : 'add';

                    try {
                        const response = await fetch(`/api/questions/${id}/helpful`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ action }),
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Toggle state
                            if (isActive) {
                                btn.classList.remove('active');
                                helpfulClicked.delete(id);
                            } else {
                                btn.classList.add('active');
                                helpfulClicked.add(id);
                            }
                            localStorage.setItem('gaispedia_helpful_clicked', JSON.stringify([...helpfulClicked]));

                            // Update helpful count display
                            const card = btn.closest('.question-card');
                            const meta = card.querySelector('.question-meta');
                            let helpfulSpan = meta.querySelector('.helpful');

                            if (data.helpful_count > 0) {
                                const helpfulHTML = `
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M14 9V5C14 3.89543 13.1046 3 12 3H12.0001C10.8955 3 10 3.89543 10 5V9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M4 14H5.67568C6.1651 14 6.63772 14.1684 7.01138 14.4761L9.02272 16.1358C9.59969 16.6107 10.4003 16.6107 10.9773 16.1358L12.9886 14.4761C13.3623 14.1684 13.8349 14 14.3243 14H20V21H4V14Z" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                    ${data.helpful_count}人が役立った
                                `;
                                if (helpfulSpan) {
                                    helpfulSpan.innerHTML = helpfulHTML;
                                } else {
                                    const newHelpful = document.createElement('span');
                                    newHelpful.className = 'helpful';
                                    newHelpful.innerHTML = helpfulHTML;
                                    meta.appendChild(newHelpful);
                                }
                            } else if (helpfulSpan) {
                                helpfulSpan.remove();
                            }
                        }
                    } catch (error) {
                        console.error('Helpful error:', error);
                    }
                });
            });

            // Copy question button
            document.querySelectorAll('.copy-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    const question = btn.dataset.question;

                    try {
                        await navigator.clipboard.writeText(question);
                        const originalHTML = btn.innerHTML;
                        btn.innerHTML = `
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            コピーしました
                        `;
                        setTimeout(() => {
                            btn.innerHTML = originalHTML;
                        }, 2000);
                    } catch (error) {
                        console.error('Copy error:', error);
                    }
                });
            });

            // Sort buttons
            sortButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const sort = btn.dataset.sort;
                    const url = new URL(window.location);
                    url.searchParams.set('sort', sort);
                    window.location.href = url.toString();
                });
            });

            // Set active sort button based on URL
            const urlParams = new URLSearchParams(window.location.search);
            const currentSort = urlParams.get('sort') || 'latest';
            sortButtons.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.sort === currentSort);
            });

            // Help Popup
            const helpPopup = document.getElementById('helpPopup');
            const helpTriggerBtn = document.getElementById('helpTriggerBtn');
            const helpPopupClose = document.getElementById('helpPopupClose');

            function toggleHelpPopup() {
                helpPopup.classList.toggle('active');
                helpTriggerBtn.classList.toggle('active');
            }

            helpTriggerBtn.addEventListener('click', toggleHelpPopup);
            helpPopupClose.addEventListener('click', toggleHelpPopup);

            document.addEventListener('click', (e) => {
                if (helpPopup.classList.contains('active') &&
                    !helpPopup.contains(e.target) &&
                    !helpTriggerBtn.contains(e.target)) {
                    toggleHelpPopup();
                }
            });
        });
    </script>

    <!-- Help Popup -->
    <div class="help-popup" id="helpPopup">
        <button class="help-popup-close" id="helpPopupClose">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <div class="help-popup-content">
            <div class="help-popup-header">
                <span class="help-icon">&#128172;</span>
                <h2>みんなの質問と回答</h2>
            </div>
            <p class="help-popup-subtitle">GAISペディアに寄せられた質問を共有</p>

            <div class="help-section">
                <h3><span class="section-icon">&#128269;</span> このページでできること</h3>
                <ul class="help-list">
                    <li>他のユーザーの質問と回答を閲覧</li>
                    <li>役立った回答に「いいね」をつける</li>
                    <li>質問をコピーして自分も聞いてみる</li>
                    <li>新着順・人気順で並び替え</li>
                </ul>
            </div>

            <div class="help-section">
                <h3><span class="section-icon">&#128161;</span> 使い方のコツ</h3>
                <ul class="help-list">
                    <li>質問カードをクリックで回答を表示</li>
                    <li>いいねは何度でも取り消しOK</li>
                    <li>気になる質問は「コピー」して試す</li>
                </ul>
            </div>

            <div class="help-section">
                <h3><span class="section-icon">&#9881;&#65039;</span> 機能</h3>
                <div class="help-features">
                    <div class="help-feature">
                        <span class="feature-icon">&#128077;</span>
                        <span>いいね</span>
                    </div>
                    <div class="help-feature">
                        <span class="feature-icon">&#128203;</span>
                        <span>コピー</span>
                    </div>
                    <div class="help-feature">
                        <span class="feature-icon">&#128197;</span>
                        <span>ソート</span>
                    </div>
                </div>
            </div>

            <div class="help-footer">
                <p>&#129302; GAISペディアで質問すると自動で追加されます</p>
            </div>
        </div>
    </div>

    <!-- Help Button -->
    <button class="help-trigger-btn" id="helpTriggerBtn" title="使い方を見る">
        <span>?</span>
    </button>
</body>
</html>
