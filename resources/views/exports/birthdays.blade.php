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
        <th style="font-weight: bold;">Имя</th>
        <th style="font-weight: bold;">Дата рождения</th>
        <th style="font-weight: bold;">День недели</th>
        <th style="font-weight: bold;">Тип</th>
    </tr>
    </thead>

    <tbody>
    @foreach($items as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td>{{ \Carbon\Carbon::parse($item['date'])->format('d.m.Y') }}</td>
            <td>{{ $item['weekday'] }}</td>
            <td>{{ $item['type'] == "пользователь"?"Сотрудник":"Поставщик" }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
