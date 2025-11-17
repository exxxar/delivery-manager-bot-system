<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Пользователи</title>
</head>
<body>
<table>
    <thead>
    <tr>
        <th style="font-weight: bold;">ID</th>
        <th style="font-weight: bold;">Имя</th>
        <th style="font-weight: bold;">ФИО из Telegram</th>
        <th style="font-weight: bold;">Email</th>
        <th style="font-weight: bold;">Telegram Chat ID</th>
        <th style="font-weight: bold;">Роль</th>
        <th style="font-weight: bold;">Процент</th>
        <th style="font-weight: bold;">Работает?</th>
        <th style="font-weight: bold;">Email подтверждён</th>
        <th style="font-weight: bold;">Заблокирован</th>
        <th style="font-weight: bold;">Сообщение блокировки</th>
        <th style="font-weight: bold;">Создано</th>
        <th style="font-weight: bold;">Обновлено</th>
    </tr>
    </thead>
    <tbody>
    @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->fio_from_telegram }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->telegram_chat_id }}</td>
            <td>
                @php
                    $role = $user->role ?? 0;
                    switch ($role) {
                        default:
                        case 0:
                            echo "Пользователь"; break;
                        case 1:
                            echo "Агент"; break;
                        case 2:
                            echo "Поставщик"; break;
                        case 3:
                            echo "Администратор"; break;
                        case 4:
                            echo "Суперадмин"; break;
                    }
                @endphp
            </td>

            <td>{{ $user->percent * 100}}</td>
            <td>{{ $user->is_work ? 'Да' : 'Нет' }}</td>
            <td>{{ $user->email_verified_at }}</td>
            <td>{{ $user->blocked_at }}</td>
            <td>{{ $user->blocked_message }}</td>
            <td>{{ $user->created_at }}</td>
            <td>{{ $user->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
