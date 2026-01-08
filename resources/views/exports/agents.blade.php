<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Торговые представители</title>
</head>
<body>
<table>
    <thead>
    <tr>
        <th style="font-weight: bold;">ID</th>
        <th style="font-weight: bold;">Пользователь</th>
        <th style="font-weight: bold;">Роль</th>
        <th style="font-weight: bold;">Процент</th>
        <th style="font-weight: bold;">Имя</th>
        <th style="font-weight: bold;">Телефон</th>
        <th style="font-weight: bold;">Email</th>
        <th style="font-weight: bold;">Регион</th>
        <th style="font-weight: bold;">Создано</th>
        <th style="font-weight: bold;">Обновлено</th>
    </tr>
    </thead>
    <tbody>
    @foreach($agents as $agent)
        <tr>
            <td>{{ $agent->id }}</td>
            <td>{{ $agent->user?->fio_from_telegram ?? '—' }}</td>
            <td>
                @php
                    $role = $agent->user->role ?? 0;
                    switch ($role) {
                        default:
                        case 0:
                            echo "Пользователь"; break;
                        case 1:
                            echo "Администратор"; break;
                        case 2:
                            echo "Поставщик"; break;
                        case 3:
                            echo "Старший администратор"; break;
                        case 4:
                            echo "Суперадмин"; break;
                    }
                @endphp
            </td>
            <td>{{ $agent->user?->percent }}</td>
            <td>{{ $agent->name }}</td>
            <td>{{ $agent->phone }}</td>
            <td>{{ $agent->email }}</td>
            <td>{{ $agent->region }}</td>
            <td>{{ $agent->created_at }}</td>
            <td>{{ $agent->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
