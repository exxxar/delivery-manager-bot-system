<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @php
        $newTitle = \App\Facades\BusinessLogicFacade::method()
        ->truncateTitle($title ?? 'Товары');
    @endphp
    <title>{{$newTitle}}</title>
</head>
<body>
<table>
    <thead>
    <tr>
        <th style="font-weight: bold;">ID</th>
        <th style="font-weight: bold;width:150px;">Название</th>
        <th style="font-weight: bold;width:350px;">Описание</th>
        <th style="font-weight: bold;width:150px;">Цена</th>
        <th style="font-weight: bold;width:150px;">Количество</th>
        <th style="font-weight: bold;width:250px;">Поставщик</th>
        <th style="font-weight: bold;width:250px;">Категория</th>

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

        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
