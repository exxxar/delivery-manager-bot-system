<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Доступ заблокирован</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            max-width: 420px;
            width: 100%;
            padding: 40px 32px;
            text-align: center;
        }

        .icon {
            width: 84px;
            height: 84px;
            margin: 0 auto 24px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
        }

        h1 {
            font-size: 22px;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .text {
            color: #6b7280;
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 24px;
        }

        .reason {
            background: #fef3c7;
            color: #92400e;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .btn {
            display: inline-block;
            background: #229ED9; /* фирменный синий Telegram */
            color: #fff;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn:hover { background: #1a8bc0; }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">🚫</div>
    <h1>Доступ заблокирован</h1>
    <p class="text">Ваш доступ к боту был заблокирован администратором.</p>

    @if(!empty($blockedMessage))
        <div class="reason">Причина: {{ $blockedMessage }}</div>
    @endif

    <a href="https://t.me/exxxar" class="btn">Написать в поддержку</a>

    <div class="footer">Если вы считаете это ошибкой — свяжитесь с нами</div>
</div>
</body>
</html>
