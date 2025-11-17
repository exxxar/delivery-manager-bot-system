<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Категории продуктов</title>
</head>
<body>
<table>
    <thead>
    <tr>
        <th style="font-weight: bold;">ID</th>
        <th style="font-weight: bold;">Название</th>
        <th style="font-weight: bold;">Описание</th>
        <th style="font-weight: bold;">Создано</th>
        <th style="font-weight: bold;">Обновлено</th>
    </tr>
    </thead>
    <tbody>
    @foreach($categories as $category)
        <tr>
            <td>{{ $category->id }}</td>
            <td>{{ $category->name }}</td>
            <td>{{ $category->description }}</td>
            <td>{{ $category->created_at }}</td>
            <td>{{ $category->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
