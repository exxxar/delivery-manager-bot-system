<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @php
        $newTitle = \App\Facades\BusinessLogicFacade::method()
        ->truncateTitle($title);
    @endphp
    <title>{{$newTitle}}</title>
</head>
<body>
<table>
    <thead>
    <tr>
        <th style="font-weight: bold;">№</th>
        <th style="font-weight: bold;">Номер заявки</th>
        <th style="font-weight: bold;">Название</th>
        <th style="font-weight: bold;">Статус</th>
        <th style="font-weight: bold;">Дата встречи</th>
        <th style="font-weight: bold;">Дата продажи</th>
        <th style="font-weight: bold;">Факт. доставка</th>
        <th style="font-weight: bold;">Сумма заказа</th>
        <th style="font-weight: bold;">Тип оплаты</th>
        <th style="font-weight: bold;">Администратор</th>
        <th style="font-weight: bold;">Поставщик</th>
        <th style="font-weight: bold;">Продукт</th>

    </tr>
    </thead>
    <tbody>
    @php
        $index = 1;

        $statusMap = [
        'pending'   => 'Ожидает назначения',
        'assigned'  => 'Назначено младшему администратору',
        'delivered' => 'Доставляется',
        'completed' => 'Завершено',
        'rejected'  => 'Отклонено',
    ];
    @endphp
    @foreach($sales as $sale)
        <tr>
            <td>{{ $index }}</td>
            <td>{{ $sale->id }}</td>
            <td>{{ $sale->title }}</td>
            <td>{{ $statusMap[$sale->status] ?? 'Неизвестный статус' }}</td>
            <td>{{ $sale->due_date }}</td>
            <td>{{ $sale->sale_date }}</td>
            <td>{{ $sale->actual_delivery_date }}</td>
            <td>{{ $sale->total_price }}</td>
            <td>
                {{ $sale->payment_type == 1 ? 'Безналичный' : 'Наличный' }}
            </td>
            <td style="text-align: left;">{{ $sale->agent->name?? '—' }}</td>
            <td style="text-align: left;">{{ $sale->supplier->name ?? '—' }}</td>
            <td style="text-align: left;">{{ $sale->product->name ?? '—' }}</td>
        </tr>
        @php
            $index++;
        @endphp
    @endforeach
    </tbody>
</table>

</body>
</html>
