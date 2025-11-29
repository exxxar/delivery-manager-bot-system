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
        <th style="font-weight: bold;width:300px; ">Название</th>
        <th style="font-weight: bold;width:250px; ">Описание</th>
        <th style="font-weight: bold;width:300px; ">Адрес</th>
        <th style="font-weight: bold;width:150px; ">Телефон</th>
        <th style="font-weight: bold; ">Процент, %</th>
        <th style="font-weight: bold;width:150px; ">День рождения</th>
        <th style="font-weight: bold;width:250px; ">Электронная почта</th>
    </tr>
    </thead>
    <tbody>
    @foreach($suppliers as $supplier)
        <tr>
            <td>{{ $supplier->id }}</td>
            <td>{{ $supplier->name }}</td>
            <td>{{ $supplier->description }}</td>
            <td>{{ $supplier->address }}</td>
            <td>{{ $supplier->phone }}</td>
            <td>{{ $supplier->percent *100}}</td>
            <td>{{ $supplier->birthday ? \Carbon\Carbon::parse($supplier->birthday )->format('d.m.Y'): '' }}</td>
            <td>{{ $supplier->email }}</td>


        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
