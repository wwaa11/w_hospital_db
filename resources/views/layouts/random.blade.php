<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield("title", "สุ่มรายชื่อสมาชิกสหกรณ์")</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mitr:wght@400;500;600;700&family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --font-display: "Mitr", "Sarabun", sans-serif;
            --font-body: "Sarabun", sans-serif;
            --color-clay-bg: #d5e6df;
            --color-clay-panel: #eaf3ef;
            --color-clay-raised: #f3f9f6;
            --color-clay-ink: #2a3d35;
            --color-clay-muted: #5f7a6e;
            --color-clay-mint: #6fbf9a;
            --color-clay-mint-deep: #3f8f6c;
            --color-clay-peach: #f0a878;
            --color-clay-peach-deep: #d9844f;
            --color-clay-line: #c5d9cf;
        }

        :root {
            --clay-out: 10px 10px 22px #b8cec4, -8px -8px 18px #f4fbf7;
            --clay-out-sm: 6px 6px 14px #b8cec4, -5px -5px 12px #f4fbf7;
            --clay-in: inset 6px 6px 12px #b8cec4, inset -5px -5px 10px #f7fcf9;
            --clay-press: inset 5px 5px 10px #b8cec4, inset -4px -4px 8px #f4fbf7;
        }

        html {
            font-family: var(--font-body);
            color: var(--color-clay-ink);
        }

        .font-display {
            font-family: var(--font-display);
        }

        .clay-page {
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
            background:
                radial-gradient(circle at 12% 18%, #f7d9c4 0%, transparent 32%),
                radial-gradient(circle at 88% 12%, #b9dfd0 0%, transparent 34%),
                radial-gradient(circle at 70% 88%, #c5dce8 0%, transparent 36%),
                radial-gradient(circle at 18% 82%, #e8c8c8 0%, transparent 30%),
                var(--color-clay-bg);
        }

        .clay-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(1.5px);
            pointer-events: none;
            z-index: 0;
            opacity: 0.55;
            animation: clay-float 9s ease-in-out infinite;
        }

        .clay-blob-a {
            width: 180px;
            height: 180px;
            top: 8%;
            left: -40px;
            background: #f2c4a8;
            box-shadow: var(--clay-out);
        }

        .clay-blob-b {
            width: 140px;
            height: 140px;
            top: 18%;
            right: -30px;
            background: #9fd4bf;
            box-shadow: var(--clay-out);
            animation-delay: -2s;
        }

        .clay-blob-c {
            width: 110px;
            height: 110px;
            bottom: 12%;
            left: 8%;
            background: #a9c9d8;
            box-shadow: var(--clay-out-sm);
            animation-delay: -4s;
        }

        @keyframes clay-float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-14px) scale(1.04); }
        }

        .clay-shell {
            position: relative;
            z-index: 1;
        }

        .clay-panel {
            background: var(--color-clay-panel);
            border: 3px solid var(--color-clay-line);
            border-radius: 2rem;
            box-shadow: var(--clay-out);
        }

        .clay-panel-raised {
            background: var(--color-clay-raised);
            border: 3px solid #d7e8df;
            border-radius: 1.75rem;
            box-shadow: var(--clay-out-sm);
        }

        .clay-inset {
            background: #dfece6;
            border: 2px solid #c5d9cf;
            border-radius: 1.5rem;
            box-shadow: var(--clay-in);
        }

        .clay-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            border: 2px solid transparent;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: var(--clay-out-sm);
        }

        .clay-chip-mint {
            background: #d6f0e4;
            border-color: #b5dfcb;
            color: var(--color-clay-mint-deep);
        }

        .clay-chip-peach {
            background: #ffe3cf;
            border-color: #f2c4a0;
            color: var(--color-clay-peach-deep);
        }

        .clay-chip-sky {
            background: #d9ebf2;
            border-color: #b5d3e0;
            color: #3f6d7d;
        }

        .clay-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 999px;
            border: 3px solid transparent;
            font-family: var(--font-display);
            font-weight: 600;
            transition: transform 0.12s ease, box-shadow 0.12s ease;
            cursor: pointer;
        }

        .clay-btn:active {
            transform: scale(0.96);
            box-shadow: var(--clay-press) !important;
        }

        .clay-btn-mint {
            background: linear-gradient(180deg, #8fd4b0 0%, var(--color-clay-mint) 100%);
            border-color: #58a882;
            color: #1f4d39;
            box-shadow: var(--clay-out-sm);
            padding: 0.7rem 1.4rem;
        }

        .clay-btn-peach {
            background: linear-gradient(180deg, #f7bf93 0%, var(--color-clay-peach) 100%);
            border-color: #d9844f;
            color: #5c3318;
            box-shadow: 8px 8px 18px #c7b09a, -6px -6px 14px #fff6ee;
            padding: 0.95rem 2.2rem;
            font-size: 1.15rem;
        }

        .clay-btn-ghost {
            background: var(--color-clay-raised);
            border-color: var(--color-clay-line);
            color: var(--color-clay-muted);
            box-shadow: var(--clay-out-sm);
            padding: 0.55rem 1.1rem;
            font-size: 0.9rem;
        }

        .clay-btn-draw {
            animation: clay-breathe 2.8s ease-in-out infinite;
        }

        @keyframes clay-breathe {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); }
        }

        .clay-btn-draw:active {
            animation: none;
        }

        .clay-input {
            width: 100%;
            border-radius: 1.25rem !important;
            border: 3px solid var(--color-clay-line) !important;
            background: #e3efe9 !important;
            box-shadow: var(--clay-in) !important;
            color: var(--color-clay-ink) !important;
            font-family: var(--font-display);
        }

        .clay-input:focus {
            outline: none;
            border-color: var(--color-clay-mint) !important;
        }

        .clay-file {
            width: 100%;
            border-radius: 1.25rem !important;
            border: 3px solid var(--color-clay-line) !important;
            background: #e3efe9 !important;
            box-shadow: var(--clay-in) !important;
        }

        .clay-step {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1rem;
            border-radius: 999px;
            background: var(--color-clay-raised);
            border: 2px solid var(--color-clay-line);
            box-shadow: var(--clay-out-sm);
            color: var(--color-clay-muted);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .clay-step-on {
            background: #d6f0e4;
            border-color: #8fd4b0;
            color: var(--color-clay-mint-deep);
        }

        .clay-dot {
            width: 0.65rem;
            height: 0.65rem;
            border-radius: 999px;
            background: currentColor;
            box-shadow: inset 1px 1px 2px rgba(255, 255, 255, 0.55);
        }

        .clay-alert {
            border-radius: 1.5rem;
            border: 3px solid;
            box-shadow: var(--clay-out-sm);
            padding: 1rem 1.25rem;
            font-size: 0.95rem;
        }

        .clay-alert-error {
            background: #f8dede;
            border-color: #e8a0a0;
            color: #8a3f3f;
        }

        .clay-alert-ok {
            background: #d6f0e4;
            border-color: #8fd4b0;
            color: var(--color-clay-mint-deep);
        }

        .clay-alert-warn {
            background: #ffe3cf;
            border-color: #f2c4a0;
            color: var(--color-clay-peach-deep);
        }

        .clay-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 999px;
            font-family: var(--font-display);
            font-weight: 700;
            border: 2px solid;
            box-shadow: var(--clay-out-sm);
            flex-shrink: 0;
        }

        .clay-number-hot {
            background: linear-gradient(180deg, #f7bf93, var(--color-clay-peach));
            border-color: #d9844f;
            color: #5c3318;
        }

        .clay-number-cool {
            background: #e3efe9;
            border-color: var(--color-clay-line);
            color: var(--color-clay-muted);
        }

        .winner-reveal {
            animation: clay-pop 0.5s cubic-bezier(0.34, 1.4, 0.64, 1) both;
        }

        @keyframes clay-pop {
            from {
                opacity: 0;
                transform: scale(0.86) translateY(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .clay-blob,
            .clay-btn-draw,
            .winner-reveal {
                animation: none;
            }
        }

        @media print {
            .no-print,
            .clay-blob {
                display: none !important;
            }

            .clay-page {
                background: white;
                min-height: auto;
            }

            .clay-panel,
            .clay-panel-raised {
                box-shadow: none;
                border-color: #ccc;
            }
        }
    </style>
    @stack("head")
</head>
<body class="clay-page antialiased">
    <div class="clay-blob clay-blob-a no-print" aria-hidden="true"></div>
    <div class="clay-blob clay-blob-b no-print" aria-hidden="true"></div>
    <div class="clay-blob clay-blob-c no-print" aria-hidden="true"></div>
    <div class="clay-shell">
        @yield("content")
    </div>
    @stack("scripts")
</body>
</html>
