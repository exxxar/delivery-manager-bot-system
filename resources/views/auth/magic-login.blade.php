<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>Вход в аккаунт</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;background:#f3f6fb;color:#172d50;font:16px system-ui,-apple-system,Segoe UI,sans-serif}
        .card{width:min(100%,440px);padding:30px;border:1px solid #e2eaf5;border-radius:22px;background:#fff;box-shadow:0 12px 40px #172d5014;text-align:center}
        .icon{width:58px;height:58px;margin:0 auto 18px;display:grid;place-items:center;border-radius:18px;background:#e8f0ff;color:#2457a7;font-size:25px}
        h1{font-size:22px;margin:0 0 10px}p{color:#6c7b91;line-height:1.55;margin:0 0 22px}
        button{width:100%;border:0;border-radius:12px;padding:14px 18px;background:#2056b4;color:#fff;font-size:15px;font-weight:700;cursor:pointer}
        .invalid{background:#fff0f2;color:#b8324b;border-radius:12px;padding:14px}
    </style>
</head>
<body>
<div class="card">
    @if($valid)
        <div class="icon">↗</div>
        <h1>Войти в аккаунт</h1>
        <p>Вы собираетесь войти как <strong>{{ $userName }}</strong>. Нажмите кнопку ниже, чтобы продолжить.</p>
        <form method="POST" action="{{ route('agent.magic-login.consume', ['token' => $token]) }}">
            @csrf
            <button type="submit">Войти в аккаунт</button>
        </form>
    @else
        <div class="icon">!</div>
        <h1>Ссылка недействительна</h1>
        <div class="invalid">Срок действия ссылки истёк, она уже использована или была отозвана. Попросите администратора создать новую.</div>
    @endif
</div>
</body>
</html>
