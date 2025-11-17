<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Клиенты</title>
</head>
<body>
<table>
    <thead>
    <tr>
        <th style="font-weight: bold;">ID</th>
        <th style="font-weight: bold;">Имя</th>
        <th style="font-weight: bold;">Компания</th>
        <th style="font-weight: bold;">Адрес</th>
        <th style="font-weight: bold;">Телефон</th>
        <th style="font-weight: bold;">Email</th>
        <th style="font-weight: bold;">Создано</th>
        <th style="font-weight: bold;">Обновлено</th>
    </tr>
    </thead>
    <tbody>
    @foreach($customers as $customer)
        <tr>
            <td>{{ $customer->id }}</td>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->company_name }}</td>
            <td>{{ $customer->address }}</td>
            <td>{{ $customer->phone }}</td>
            <td>{{ $customer->email }}</td>
            <td>{{ $customer->created_at }}</td>
            <td>{{ $customer->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
