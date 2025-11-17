<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Товары</title>
</head>
<body>
<table>
    <thead>
    <tr>
        <th style="font-weight: bold;">ID</th>
        <th style="font-weight: bold;">Название</th>
        <th style="font-weight: bold;">Описание</th>
        <th style="font-weight: bold;">Цена</th>
        <th style="font-weight: bold;">Количество</th>
        <th style="font-weight: bold;">Поставщик</th>
        <th style="font-weight: bold;">Категория</th>
        <th style="font-weight: bold;">Создано</th>
        <th style="font-weight: bold;">Обновлено</th>
    </tr>
    </thead>
    <tbody>
    @foreach($products as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->description }}</td>
            <td>{{ $product->price }}</td>
            <td>{{ $product->count }}</td>
            <td>{{ $product->supplier?->name ?? '—' }}</td>
            <td>{{ $product->category?->name ?? '—' }}</td>
            <td>{{ $product->created_at }}</td>
            <td>{{ $product->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
