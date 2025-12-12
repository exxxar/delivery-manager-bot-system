<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Поставщики</title>
</head>
<body>
<table>
    <thead>
    <tr>
        <th style="font-weight: bold; ">ID</th>
        <th style="font-weight: bold;width:300px; ">Имя</th>
        <th style="font-weight: bold;width:150px; ">Дата рождения</th>
        <th style="font-weight: bold;width:150px; ">Телефон</th>
    </tr>
    </thead>
    <tbody>
    @foreach($suppliers as $supplier)
        <tr>
            <td>{{ $supplier->id }}</td>
            <td>{{ $supplier->name }}</td>
            <td>{{ $supplier->birthday ? \Carbon\Carbon::parse($supplier->birthday )->format('d.m.Y'): '' }}</td>
            <td>{{ $supplier->phone }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
