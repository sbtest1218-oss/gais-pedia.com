<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GAISペディア - 生成AI協会 ナレッジアシスタント</title>
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
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
        }

        .chatbot-container {
            width: 100%;
            max-width: 900px;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 40px);
            overflow: hidden;
        }

        /* Header */
        .chatbot-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid #e9ecef;
            background-color: #ffffff;
            flex-shrink: 0;
            z-index: 10;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .avatar {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #4dabf7 0%, #339af0 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .header-title h1 {
            font-size: 24px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 4px;
        }

        .header-title p {
            font-size: 14px;
            color: #868e96;
        }

        .clear-history-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            color: #495057;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .clear-history-btn:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
        }

        /* Chat Messages Area */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-height: 0;
        }

        .message {
            max-width: 80%;
            padding: 16px 20px;
            border-radius: 16px;
            line-height: 1.6;
            font-size: 15px;
        }

        .message.bot {
            background-color: #f1f3f4;
            color: #212529;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }

        .message.user {
            background-color: #339af0;
            color: #ffffff;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .message.bot a,
        .message.bot .chat-link {
            color: #1971c2;
            text-decoration: none;
            border-bottom: 1px solid #a5d8ff;
            transition: all 0.2s ease;
        }

        .message.bot a:hover,
        .message.bot .chat-link:hover {
            color: #1864ab;
            border-bottom-color: #1971c2;
        }

        /* Markdown Styles */
        .message.bot h3 {
            font-size: 16px;
            font-weight: 700;
            margin: 16px 0 8px 0;
            color: #212529;
        }

        .message.bot h4 {
            font-size: 15px;
            font-weight: 600;
            margin: 12px 0 6px 0;
            color: #343a40;
        }

        .message.bot p {
            margin: 8px 0;
        }

        .message.bot ul,
        .message.bot ol {
            margin: 8px 0;
            padding-left: 24px;
        }

        .message.bot li {
            margin: 4px 0;
        }

        .message.bot table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 14px;
        }

        .message.bot th,
        .message.bot td {
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            text-align: left;
        }

        .message.bot th {
            background-color: #e9ecef;
            font-weight: 600;
        }

        .message.bot tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .message.bot code {
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }

        .message.bot pre {
            background-color: #212529;
            color: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 12px 0;
        }

        .message.bot pre code {
            background-color: transparent;
            padding: 0;
            color: inherit;
        }

        .message.bot blockquote {
            border-left: 4px solid #339af0;
            padding-left: 16px;
            margin: 12px 0;
            color: #495057;
        }

        .message.bot hr {
            border: none;
            border-top: 1px solid #dee2e6;
            margin: 16px 0;
        }

        .message.bot strong {
            font-weight: 600;
        }

        /* FAQ Section */
        .faq-section {
            padding: 20px 24px;
            border-top: 1px solid #e9ecef;
            background-color: #ffffff;
            flex-shrink: 0;
        }

        .faq-section.hidden {
            display: none;
        }

        .faq-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .faq-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 500;
            color: #212529;
        }

        .faq-title .icon {
            font-size: 18px;
        }

        .faq-close-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background-color: transparent;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            cursor: pointer;
            color: #868e96;
            transition: all 0.2s ease;
        }

        .faq-close-btn:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
            color: #495057;
        }

        /* FAQ Restore Button */
        .faq-restore-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 0 24px 16px;
            padding: 10px 16px;
            background-color: #f8f9fa;
            border: 1px dashed #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            color: #868e96;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .faq-restore-btn:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
            color: #495057;
        }

        .faq-restore-btn.hidden {
            display: none;
        }

        .faq-restore-btn svg {
            color: currentColor;
        }

        .faq-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .faq-btn {
            padding: 14px 16px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            color: #495057;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .faq-btn:hover {
            background-color: #f8f9fa;
            border-color: #339af0;
            color: #339af0;
        }

        /* Input Area */
        .input-area {
            padding: 16px 24px 24px;
            background-color: #ffffff;
            flex-shrink: 0;
        }

        .input-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .input-wrapper:focus-within {
            border-color: #339af0;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(51, 154, 240, 0.1);
        }

        .input-wrapper .icon {
            font-size: 20px;
            color: #adb5bd;
        }

        .input-wrapper input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 15px;
            color: #212529;
            outline: none;
        }

        .input-wrapper input::placeholder {
            color: #adb5bd;
        }

        .send-btn {
            padding: 10px 20px;
            background-color: #339af0;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .send-btn:hover {
            background-color: #228be6;
        }

        .send-btn:disabled {
            background-color: #adb5bd;
            cursor: not-allowed;
        }

        /* Desktop/Mobile visibility */
        .desktop-only {
            display: flex;
            align-items: center;
        }

        .mobile-only {
            display: none;
        }

        /* Hamburger Menu */
        .mobile-menu-container {
            position: relative;
        }

        .hamburger-btn {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 44px;
            height: 44px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            gap: 5px;
            padding: 12px;
            transition: all 0.2s ease;
        }

        .hamburger-btn:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
        }

        .hamburger-btn span {
            display: block;
            width: 18px;
            height: 2px;
            background-color: #495057;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .hamburger-btn.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .hamburger-btn.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger-btn.active span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }

        .mobile-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 220px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
            z-index: 100;
            overflow: hidden;
        }

        .mobile-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .mobile-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 14px 16px;
            background: none;
            border: none;
            font-size: 14px;
            color: #495057;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
        }

        .mobile-menu-item:hover {
            background-color: #f8f9fa;
        }

        .mobile-menu-item:not(:last-child) {
            border-bottom: 1px solid #f1f3f4;
        }

        .mobile-menu-item svg {
            flex-shrink: 0;
            color: #868e96;
        }

        /* Dark mode for mobile menu */
        body.dark-mode .hamburger-btn {
            background-color: #2a2a4a;
            border-color: #3a3a5a;
        }

        body.dark-mode .hamburger-btn:hover {
            background-color: #3a3a5a;
        }

        body.dark-mode .hamburger-btn span {
            background-color: #e9ecef;
        }

        body.dark-mode .mobile-menu {
            background-color: #16213e;
            border-color: #3a3a5a;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }

        body.dark-mode .mobile-menu-item {
            color: #e9ecef;
        }

        body.dark-mode .mobile-menu-item:hover {
            background-color: #2a2a4a;
        }

        body.dark-mode .mobile-menu-item:not(:last-child) {
            border-bottom-color: #2a2a4a;
        }

        body.dark-mode .mobile-menu-item svg {
            color: #adb5bd;
        }

        /* Responsive */
        @media (max-width: 640px) {
            body {
                padding: 0;
            }

            .chatbot-container {
                border-radius: 0;
                height: 100vh;
                height: 100dvh;
            }

            .faq-buttons {
                grid-template-columns: 1fr;
            }

            .header-title h1 {
                font-size: 18px;
            }

            .header-title p {
                font-size: 12px;
            }

            .avatar {
                width: 44px;
                height: 44px;
                font-size: 22px;
            }

            .avatar svg {
                width: 24px;
                height: 24px;
            }

            .chatbot-header {
                padding: 14px 16px;
            }

            .header-left {
                gap: 12px;
            }

            .desktop-only {
                display: none;
            }

            .mobile-only {
                display: block;
            }
        }

        /* Typing indicator */
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            background-color: #f1f3f4;
            border-radius: 16px;
            border-bottom-left-radius: 4px;
            align-self: flex-start;
        }

        .typing-indicator .typing-text {
            font-size: 14px;
            color: #495057;
        }

        .typing-indicator .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-indicator .typing-dots span {
            width: 8px;
            height: 8px;
            background-color: #339af0;
            border-radius: 50%;
            animation: typing 1.4s infinite ease-in-out;
        }

        .typing-indicator .typing-dots span:nth-child(1) { animation-delay: 0s; }
        .typing-indicator .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator .typing-dots span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-8px); }
        }

        /* Questions Link Button */
        .questions-link-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-right: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            color: #495057;
        }

        .questions-link-btn:hover {
            background-color: #f8f9fa;
            border-color: #339af0;
            color: #339af0;
        }

        /* Dark Mode Toggle */
        .theme-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-right: 8px;
        }

        .theme-toggle:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
        }

        .theme-toggle svg {
            width: 20px;
            height: 20px;
            color: #495057;
        }

        /* Dark Mode Styles */
        body.dark-mode {
            background-color: #1a1a2e;
        }

        body.dark-mode .chatbot-container {
            background-color: #16213e;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .chatbot-header {
            border-bottom-color: #2a2a4a;
            background-color: #16213e;
        }

        body.dark-mode .header-title h1 {
            color: #e9ecef;
        }

        body.dark-mode .header-title p {
            color: #868e96;
        }

        body.dark-mode .clear-history-btn,
        body.dark-mode .theme-toggle,
        body.dark-mode .air-canvas-btn,
        body.dark-mode .questions-link-btn {
            background-color: #2a2a4a;
            border-color: #3a3a5a;
            color: #e9ecef;
        }

        body.dark-mode .clear-history-btn:hover,
        body.dark-mode .theme-toggle:hover,
        body.dark-mode .air-canvas-btn:hover,
        body.dark-mode .questions-link-btn:hover {
            background-color: #3a3a5a;
            border-color: #4a4a6a;
        }

        body.dark-mode .air-canvas-btn svg {
            color: #e9ecef;
        }

        body.dark-mode .theme-toggle svg {
            color: #ffd43b;
        }

        body.dark-mode .message.bot {
            background-color: #2a2a4a;
            color: #e9ecef;
        }

        body.dark-mode .message.bot a {
            color: #74c0fc;
            border-bottom-color: #4dabf7;
        }

        body.dark-mode .message.bot a:hover {
            color: #a5d8ff;
        }

        body.dark-mode .message.bot th {
            background-color: #3a3a5a;
        }

        body.dark-mode .message.bot tr:nth-child(even) {
            background-color: #2a2a4a;
        }

        body.dark-mode .message.bot th,
        body.dark-mode .message.bot td {
            border-color: #3a3a5a;
        }

        body.dark-mode .message.bot code {
            background-color: #3a3a5a;
        }

        body.dark-mode .message.user {
            background-color: #228be6;
        }

        body.dark-mode .faq-section {
            border-top-color: #2a2a4a;
            background-color: #16213e;
        }

        body.dark-mode .input-area {
            background-color: #16213e;
        }

        body.dark-mode .faq-title {
            color: #e9ecef;
        }

        body.dark-mode .faq-close-btn {
            border-color: #3a3a5a;
            color: #868e96;
        }

        body.dark-mode .faq-close-btn:hover {
            background-color: #3a3a5a;
            border-color: #4a4a6a;
            color: #e9ecef;
        }

        body.dark-mode .faq-restore-btn {
            background-color: #2a2a4a;
            border-color: #3a3a5a;
            color: #868e96;
        }

        body.dark-mode .faq-restore-btn:hover {
            background-color: #3a3a5a;
            border-color: #4a4a6a;
            color: #adb5bd;
        }

        body.dark-mode .faq-btn {
            background-color: #2a2a4a;
            border-color: #3a3a5a;
            color: #adb5bd;
        }

        body.dark-mode .faq-btn:hover {
            background-color: #3a3a5a;
            border-color: #339af0;
            color: #74c0fc;
        }

        body.dark-mode .input-wrapper {
            background-color: #2a2a4a;
            border-color: #3a3a5a;
        }

        body.dark-mode .input-wrapper:focus-within {
            background-color: #2a2a4a;
            border-color: #339af0;
        }

        body.dark-mode .input-wrapper input {
            color: #e9ecef;
        }

        body.dark-mode .input-wrapper input::placeholder {
            color: #6c757d;
        }

        body.dark-mode .input-wrapper .icon {
            color: #6c757d;
        }

        body.dark-mode .typing-indicator {
            background-color: #2a2a4a;
        }

        body.dark-mode .typing-indicator .typing-text {
            color: #adb5bd;
        }

        body.dark-mode .modal-content {
            background-color: #16213e;
        }

        body.dark-mode .modal-title {
            color: #e9ecef;
        }

        body.dark-mode .modal-message {
            color: #adb5bd;
        }

        body.dark-mode .modal-btn-secondary {
            background-color: #2a2a4a;
            border-color: #3a3a5a;
            color: #e9ecef;
        }

        /* Storage Warning Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 32px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
        }

        .modal-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 12px;
        }

        .modal-message {
            font-size: 14px;
            color: #495057;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .modal-storage-info {
            background-color: #fff3cd;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #856404;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .modal-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modal-btn-primary {
            background-color: #339af0;
            color: #ffffff;
            border: none;
        }

        .modal-btn-primary:hover {
            background-color: #228be6;
        }

        .modal-btn-secondary {
            background-color: #ffffff;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        .modal-btn-secondary:hover {
            background-color: #f8f9fa;
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
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
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
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
        }

        .help-popup.active .help-section:nth-child(1) { animation-delay: 0.1s; }
        .help-popup.active .help-section:nth-child(2) { animation-delay: 0.2s; }
        .help-popup.active .help-section:nth-child(3) { animation-delay: 0.3s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        /* Help Trigger Button */
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

        /* Dark mode for help popup */
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

        body.dark-mode .help-feature:hover {
            background-color: #3a3a5a;
        }

        body.dark-mode .help-footer {
            border-top-color: #3a3a5a;
        }

        body.dark-mode .help-footer p {
            color: #868e96;
        }

        body.dark-mode .help-popup-close {
            background-color: rgba(255, 255, 255, 0.1);
            color: #adb5bd;
        }

        body.dark-mode .help-popup-close:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #e9ecef;
        }

        /* Responsive for help popup */
        @media (max-width: 640px) {
            .help-popup {
                right: 12px;
                left: 12px;
                width: auto;
                bottom: 80px;
                max-height: 70vh;
                border-radius: 16px;
            }

            .help-popup-content {
                padding: 20px;
                max-height: 70vh;
            }

            .help-popup-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .help-icon {
                font-size: 28px;
            }

            .help-popup-header h2 {
                font-size: 18px;
            }

            .help-popup-subtitle {
                padding-left: 0;
                margin-bottom: 16px;
            }

            .help-section {
                margin-bottom: 16px;
            }

            .help-section h3 {
                font-size: 13px;
            }

            .help-list li {
                font-size: 12px;
                padding: 4px 0 4px 18px;
            }

            .help-features {
                gap: 8px;
            }

            .help-feature {
                padding: 6px 10px;
                font-size: 11px;
            }

            .help-footer {
                margin-top: 12px;
                padding-top: 12px;
            }

            .help-footer p {
                font-size: 11px;
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

            .help-popup-close {
                width: 28px;
                height: 28px;
                top: 10px;
                right: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="chatbot-container">
        <!-- Header -->
        <header class="chatbot-header">
            <div class="header-left">
                <div class="avatar">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="6" width="18" height="14" rx="3" stroke="white" stroke-width="2"/>
                        <circle cx="8.5" cy="12" r="1.5" fill="white"/>
                        <circle cx="15.5" cy="12" r="1.5" fill="white"/>
                        <path d="M9 16H15" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <path d="M8 6V4C8 3.44772 8.44772 3 9 3H15C15.5523 3 16 3.44772 16 4V6" stroke="white" stroke-width="2"/>
                        <circle cx="12" cy="3" r="1" fill="white"/>
                    </svg>
                </div>
                <div class="header-title">
                    <h1>GAISペディア</h1>
                    <p>生成AI協会 ナレッジアシスタント</p>
                </div>
            </div>
            <!-- Desktop buttons -->
            <div class="header-buttons desktop-only">
                <a href="/questions" class="questions-link-btn" title="みんなの質問と回答を見る">
                    みんなの質問と回答
                </a>
                <button class="clear-history-btn" id="clearHistoryBtn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 6H5H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6H19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>履歴をクリア</span>
                </button>
                <button class="theme-toggle" id="themeToggle" title="テーマ切り替え" style="margin-left: 8px;">
                    <svg class="sun-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 2V4M12 20V22M4 12H2M22 12H20M5.64 5.64L4.22 4.22M19.78 19.78L18.36 18.36M5.64 18.36L4.22 19.78M19.78 4.22L18.36 5.64" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <svg class="moon-icon" style="display:none;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <!-- Mobile hamburger menu -->
            <div class="mobile-menu-container mobile-only">
                <button class="hamburger-btn" id="hamburgerBtn" title="メニュー">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="mobile-menu" id="mobileMenu">
                    <a href="/questions" class="mobile-menu-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        みんなの質問と回答
                    </a>
                    <button class="mobile-menu-item" id="clearHistoryBtnMobile">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 6H5H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6H19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        履歴をクリア
                    </button>
                    <button class="mobile-menu-item" id="themeToggleMobile">
                        <svg class="sun-icon-mobile" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 2V4M12 20V22M4 12H2M22 12H20M5.64 5.64L4.22 4.22M19.78 19.78L18.36 18.36M5.64 18.36L4.22 19.78M19.78 4.22L18.36 5.64" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <svg class="moon-icon-mobile" width="18" height="18" style="display:none;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="theme-text">ダークモード</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Chat Messages -->
        <div class="chat-messages" id="chatMessages">
            <!-- Messages will be loaded from localStorage or show welcome message -->
        </div>

        <!-- FAQ Restore Button (shown when FAQ is hidden) -->
        <button class="faq-restore-btn hidden" id="faqRestoreBtn" title="よくある質問を表示">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <path d="M12 16V12M12 8H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            よくある質問
        </button>

        <!-- FAQ Section -->
        <section class="faq-section" id="faqSection">
            <div class="faq-header">
                <div class="faq-title">
                    <span class="icon">💡</span>
                    <span>よくある質問</span>
                </div>
                <button class="faq-close-btn" id="faqCloseBtn" title="閉じる">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="faq-buttons">
                <button class="faq-btn" data-question="GAISの会員になるにはどうすればいい？">
                    GAISの会員になるにはどうすればいい？
                </button>
                <button class="faq-btn" data-question="次回の勉強会はいつ？">
                    次回の勉強会はいつ？
                </button>
                <button class="faq-btn" data-question="建築土木WGの活動内容は？">
                    建築土木WGの活動内容は？
                </button>
                <button class="faq-btn" data-question="正会員と準会員の違いは？">
                    正会員と準会員の違いは？
                </button>
            </div>
        </section>

        <!-- Input Area -->
        <div class="input-area">
            <div class="input-wrapper">
                <span class="icon">💬</span>
                <input
                    type="text"
                    id="messageInput"
                    placeholder="生成AI協会について質問してください"
                    autocomplete="off"
                >
                <button class="send-btn" id="sendBtn">送信</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatMessages = document.getElementById('chatMessages');
            const messageInput = document.getElementById('messageInput');
            const sendBtn = document.getElementById('sendBtn');
            const clearHistoryBtn = document.getElementById('clearHistoryBtn');
            const faqButtons = document.querySelectorAll('.faq-btn');
            const faqSection = document.getElementById('faqSection');
            const faqCloseBtn = document.getElementById('faqCloseBtn');
            const faqRestoreBtn = document.getElementById('faqRestoreBtn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const themeToggle = document.getElementById('themeToggle');

            // Dark Mode
            const STORAGE_KEY_THEME = 'gaispedia_theme';

            function initTheme() {
                const savedTheme = localStorage.getItem(STORAGE_KEY_THEME);
                if (savedTheme === 'dark') {
                    document.body.classList.add('dark-mode');
                    updateThemeIcon(true);
                }
            }

            function toggleTheme() {
                const isDark = document.body.classList.toggle('dark-mode');
                localStorage.setItem(STORAGE_KEY_THEME, isDark ? 'dark' : 'light');
                updateThemeIcon(isDark);
            }

            function updateThemeIcon(isDark) {
                const sunIcon = themeToggle.querySelector('.sun-icon');
                const moonIcon = themeToggle.querySelector('.moon-icon');
                if (isDark) {
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                } else {
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                }
            }

            themeToggle.addEventListener('click', toggleTheme);
            initTheme();

            // Mobile Menu
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            const clearHistoryBtnMobile = document.getElementById('clearHistoryBtnMobile');
            const themeToggleMobile = document.getElementById('themeToggleMobile');

            function toggleMobileMenu() {
                hamburgerBtn.classList.toggle('active');
                mobileMenu.classList.toggle('active');
            }

            function closeMobileMenu() {
                hamburgerBtn.classList.remove('active');
                mobileMenu.classList.remove('active');
            }

            hamburgerBtn.addEventListener('click', toggleMobileMenu);

            // Mobile theme toggle
            function updateMobileThemeIcon(isDark) {
                const sunIconMobile = document.querySelector('.sun-icon-mobile');
                const moonIconMobile = document.querySelector('.moon-icon-mobile');
                const themeText = document.querySelector('.theme-text');
                if (isDark) {
                    sunIconMobile.style.display = 'none';
                    moonIconMobile.style.display = 'block';
                    themeText.textContent = 'ライトモード';
                } else {
                    sunIconMobile.style.display = 'block';
                    moonIconMobile.style.display = 'none';
                    themeText.textContent = 'ダークモード';
                }
            }

            // Initialize mobile theme icon
            updateMobileThemeIcon(document.body.classList.contains('dark-mode'));

            themeToggleMobile.addEventListener('click', () => {
                toggleTheme();
                updateMobileThemeIcon(document.body.classList.contains('dark-mode'));
                closeMobileMenu();
            });

            // Mobile clear history
            clearHistoryBtnMobile.addEventListener('click', () => {
                clearHistory();
                closeMobileMenu();
            });

            // Close menu on outside click
            document.addEventListener('click', (e) => {
                if (mobileMenu.classList.contains('active') &&
                    !mobileMenu.contains(e.target) &&
                    !hamburgerBtn.contains(e.target)) {
                    closeMobileMenu();
                }
            });

            // FAQ Section Close/Restore
            const STORAGE_KEY_FAQ_HIDDEN = 'gaispedia_faq_hidden';

            function initFaqSection() {
                const isHidden = localStorage.getItem(STORAGE_KEY_FAQ_HIDDEN) === 'true';
                if (isHidden) {
                    faqSection.classList.add('hidden');
                    faqRestoreBtn.classList.remove('hidden');
                }
            }

            function closeFaqSection() {
                faqSection.classList.add('hidden');
                faqRestoreBtn.classList.remove('hidden');
                localStorage.setItem(STORAGE_KEY_FAQ_HIDDEN, 'true');
            }

            function restoreFaqSection() {
                faqSection.classList.remove('hidden');
                faqRestoreBtn.classList.add('hidden');
                localStorage.setItem(STORAGE_KEY_FAQ_HIDDEN, 'false');
            }

            faqCloseBtn.addEventListener('click', closeFaqSection);
            faqRestoreBtn.addEventListener('click', restoreFaqSection);
            initFaqSection();

            // Modal elements
            const storageModal = document.getElementById('storageModal');
            const modalCloseBtn = document.getElementById('modalCloseBtn');
            const modalClearBtn = document.getElementById('modalClearBtn');
            const storageInfo = document.getElementById('storageInfo');

            // LocalStorage keys
            const STORAGE_KEY_HISTORY = 'gaispedia_chat_history';
            const STORAGE_KEY_MESSAGES = 'gaispedia_chat_messages';
            const STORAGE_WARNING_SIZE = 50 * 1024 * 1024; // 50MB in bytes

            // Default welcome message
            const WELCOME_MESSAGE = 'GAISペディアへようこそ！\n\n生成AI協会（GAIS）に関するご質問にお答えします。\n何かお手伝いできることはありますか？';

            // Conversation history for context
            let conversationHistory = [];

            // Show welcome message
            function showWelcomeMessage() {
                chatMessages.innerHTML = '';
                addMessage(WELCOME_MESSAGE, 'bot');
            }

            // LocalStorage functions
            function getStorageSize() {
                let total = 0;
                for (let key in localStorage) {
                    if (localStorage.hasOwnProperty(key)) {
                        total += localStorage[key].length * 2; // UTF-16 = 2 bytes per char
                    }
                }
                return total;
            }

            function formatBytes(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            function checkStorageSize() {
                const size = getStorageSize();
                if (size >= STORAGE_WARNING_SIZE) {
                    storageInfo.textContent = `現在の使用量: ${formatBytes(size)}`;
                    storageModal.classList.add('active');
                }
            }

            function saveToLocalStorage() {
                try {
                    localStorage.setItem(STORAGE_KEY_HISTORY, JSON.stringify(conversationHistory));
                    localStorage.setItem(STORAGE_KEY_MESSAGES, chatMessages.innerHTML);
                    checkStorageSize();
                } catch (e) {
                    console.error('LocalStorage save error:', e);
                    // Storage might be full
                    storageInfo.textContent = 'ストレージが満杯です！';
                    storageModal.classList.add('active');
                }
            }

            function loadFromLocalStorage() {
                try {
                    const savedHistory = localStorage.getItem(STORAGE_KEY_HISTORY);
                    const savedMessages = localStorage.getItem(STORAGE_KEY_MESSAGES);

                    if (savedHistory) {
                        conversationHistory = JSON.parse(savedHistory);
                    }

                    if (savedMessages) {
                        chatMessages.innerHTML = savedMessages;
                        scrollToBottom();
                    } else {
                        // First time visit - show welcome message
                        showWelcomeMessage();
                    }

                    checkStorageSize();
                } catch (e) {
                    console.error('LocalStorage load error:', e);
                    showWelcomeMessage();
                }
            }

            function clearLocalStorage() {
                localStorage.removeItem(STORAGE_KEY_HISTORY);
                localStorage.removeItem(STORAGE_KEY_MESSAGES);
            }

            // Modal functions
            function closeModal() {
                storageModal.classList.remove('active');
            }

            modalCloseBtn.addEventListener('click', closeModal);
            modalClearBtn.addEventListener('click', () => {
                clearHistory();
                closeModal();
            });

            // Close modal when clicking outside
            storageModal.addEventListener('click', (e) => {
                if (e.target === storageModal) {
                    closeModal();
                }
            });

            // Load saved history on page load
            loadFromLocalStorage();

            // Send message function
            async function sendMessage(text) {
                if (!text.trim()) return;

                // Disable input while processing
                setInputEnabled(false);

                // Add user message
                addMessage(text, 'user');
                messageInput.value = '';

                // Add to history
                conversationHistory.push({ role: 'user', content: text });

                // Show typing indicator
                showTypingIndicator();

                try {
                    const response = await fetch('/api/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            message: text,
                            history: conversationHistory.slice(0, -1), // Send history without current message
                        }),
                    });

                    const data = await response.json();

                    hideTypingIndicator();

                    if (data.success) {
                        addMessage(data.message, 'bot');
                        // Add bot response to history
                        conversationHistory.push({ role: 'model', content: data.message });
                        // Save to localStorage
                        saveToLocalStorage();
                        // Save question to database (for みんなの質問)
                        saveQuestionToDb(text, data.message);
                    } else {
                        addMessage('エラーが発生しました: ' + (data.error || '不明なエラー'), 'bot');
                    }
                } catch (error) {
                    hideTypingIndicator();
                    console.error('Chat error:', error);
                    addMessage('通信エラーが発生しました。もう一度お試しください。', 'bot');
                }

                // Re-enable input
                setInputEnabled(true);
                messageInput.focus();
            }

            // Enable/disable input
            function setInputEnabled(enabled) {
                messageInput.disabled = !enabled;
                sendBtn.disabled = !enabled;
                faqButtons.forEach(btn => btn.disabled = !enabled);
            }

            // Add message to chat
            function addMessage(text, type) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${type}`;
                // Support markdown-like formatting (basic)
                messageDiv.innerHTML = formatMessage(text);
                chatMessages.appendChild(messageDiv);
                scrollToBottom();
            }

            // Configure marked.js with custom renderer
            const renderer = {
                link(token) {
                    const href = token.href || '';
                    const title = token.title || '';
                    const text = token.text || '';

                    // Only allow gais.jp links
                    if (!href.includes('gais.jp')) {
                        return text; // Just show text without link
                    }
                    // Skip Google redirect URLs
                    if (href.includes('vertexaisearch.cloud.google.com') ||
                        href.includes('grounding-api-redirect')) {
                        return text;
                    }
                    const titleAttr = title ? ` title="${title}"` : '';
                    return `<a href="${href}" target="_blank" rel="noopener noreferrer"${titleAttr}>${text}</a>`;
                }
            };

            marked.use({
                breaks: true,
                gfm: true,
                renderer
            });

            // Format message using marked.js
            function formatMessage(text) {
                let formatted = text;

                // Pre-process: Filter out non-gais.jp URLs in plain text
                formatted = formatted.replace(
                    /(https?:\/\/[^\s\)]+)/g,
                    function(match, url) {
                        if (!url.includes('gais.jp')) {
                            return ''; // Remove non-gais.jp URLs
                        }
                        if (url.includes('vertexaisearch.cloud.google.com') ||
                            url.includes('grounding-api-redirect')) {
                            return '';
                        }
                        return url;
                    }
                );

                // Clean up empty reference lines
                formatted = formatted.replace(/^- \s*$/gm, '');
                formatted = formatted.replace(/参照リンク:\s*(\n\s*)*$/g, '');

                // Convert markdown to HTML using marked.js
                formatted = marked.parse(formatted);

                return formatted;
            }

            // Show typing indicator
            function showTypingIndicator() {
                const typingDiv = document.createElement('div');
                typingDiv.className = 'typing-indicator';
                typingDiv.id = 'typingIndicator';
                typingDiv.innerHTML = `
                    <span class="typing-text">GAISペディアが入力中</span>
                    <div class="typing-dots">
                        <span></span><span></span><span></span>
                    </div>
                `;
                chatMessages.appendChild(typingDiv);
                scrollToBottom();
            }

            // Hide typing indicator
            function hideTypingIndicator() {
                const typingIndicator = document.getElementById('typingIndicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
            }

            // Scroll to bottom of chat
            function scrollToBottom() {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Save question to database
            async function saveQuestionToDb(question, answer) {
                try {
                    await fetch('/api/questions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ question, answer }),
                    });
                } catch (error) {
                    console.error('Failed to save question:', error);
                }
            }

            // Clear chat history
            function clearHistory() {
                conversationHistory = [];
                clearLocalStorage();
                showWelcomeMessage();
            }

            // Event listeners
            sendBtn.addEventListener('click', () => sendMessage(messageInput.value));

            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage(messageInput.value);
                }
            });

            clearHistoryBtn.addEventListener('click', clearHistory);

            faqButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const question = btn.dataset.question;
                    sendMessage(question);
                });
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

            // Close on outside click
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
                <span class="help-icon">&#129302;</span>
                <h2>GAISペディアへようこそ！</h2>
            </div>
            <p class="help-popup-subtitle">生成AI協会（GAIS）の公式ナレッジアシスタントです</p>

            <div class="help-section">
                <h3><span class="section-icon">&#128269;</span> できること</h3>
                <ul class="help-list">
                    <li>GAISのイベント・勉強会情報を検索</li>
                    <li>会員制度や入会方法について質問</li>
                    <li>ワーキンググループの活動内容を確認</li>
                    <li>過去のセミナー情報を探す</li>
                </ul>
            </div>

            <div class="help-section">
                <h3><span class="section-icon">&#128161;</span> 使い方のコツ</h3>
                <ul class="help-list">
                    <li>具体的に質問すると正確な回答が得られます</li>
                    <li>「次回の勉強会は？」「会員になるには？」など</li>
                    <li>よくある質問ボタンも活用してください</li>
                </ul>
            </div>

            <div class="help-section">
                <h3><span class="section-icon">&#9881;&#65039;</span> 機能</h3>
                <div class="help-features">
                    <div class="help-feature">
                        <span class="feature-icon">&#127769;</span>
                        <span>ダークモード</span>
                    </div>
                    <div class="help-feature">
                        <span class="feature-icon">&#128172;</span>
                        <span>みんなの質問</span>
                    </div>
                    <div class="help-feature">
                        <span class="feature-icon">&#128190;</span>
                        <span>履歴保存</span>
                    </div>
                </div>
            </div>

            <div class="help-footer">
                <p>&#128293; gais.jp の情報をリアルタイムで検索します</p>
            </div>
        </div>
    </div>

    <!-- Help Button -->
    <button class="help-trigger-btn" id="helpTriggerBtn" title="使い方を見る">
        <span>?</span>
    </button>

    <!-- Storage Warning Modal -->
    <div class="modal-overlay" id="storageModal">
        <div class="modal-content">
            <div class="modal-icon">&#9888;&#65039;</div>
            <h2 class="modal-title">ストレージ容量の警告</h2>
            <p class="modal-message">
                チャット履歴のデータ量が多くなっています。<br>
                パフォーマンス向上のため、履歴をクリアすることをお勧めします。
            </p>
            <div class="modal-storage-info" id="storageInfo">
                現在の使用量: 計算中...
            </div>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-secondary" id="modalCloseBtn">後で</button>
                <button class="modal-btn modal-btn-primary" id="modalClearBtn">履歴をクリア</button>
            </div>
        </div>
    </div>
</body>
</html>
