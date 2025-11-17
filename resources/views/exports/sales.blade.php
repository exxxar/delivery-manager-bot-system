<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Продажи</title>
</head>
<body>
<table>
    <thead>
    <tr>
        <th style="font-weight: bold;">ID</th>
        <th style="font-weight: bold;">Название</th>
        <th style="font-weight: bold;">Описание</th>
        <th style="font-weight: bold;">Статус</th>
        <th style="font-weight: bold;">Дата встречи</th>
        <th style="font-weight: bold;">Дата продажи</th>
        <th style="font-weight: bold;">План. доставка</th>
        <th style="font-weight: bold;">Факт. доставка</th>
        <th style="font-weight: bold;">Количество</th>
        <th style="font-weight: bold;">Сумма заказа</th>
        <th style="font-weight: bold;">Агент</th>
        <th style="font-weight: bold;">Клиент</th>
        <th style="font-weight: bold;">Поставщик</th>
        <th style="font-weight: bold;">Продукт</th>
        <th style="font-weight: bold;">Создан админом</th>
        <th style="font-weight: bold;">Создано</th>
        <th style="font-weight: bold;">Обновлено</th>
    </tr>
    </thead>
    <tbody>
    @foreach($sales as $sale)
        <tr>
            <td>{{ $sale->id }}</td>
            <td>{{ $sale->title }}</td>
            <td>{{ $sale->description }}</td>
            <td>
            @php
                $status = $sale->status ?? 'pending';
                switch ($status) {
                    case 'pending':
                        echo "Ожидает назначения";
                        break;

                    case 'assigned':
                        echo "Назначено агенту";
                        break;

                    case 'delivered':
                        echo "Доставляется";
                        break;

                    case 'completed':
                        echo "Завершено";
                        break;

                    case 'rejected':
                        echo "Отклонено";
                        break;

                    default:
                        echo "Неизвестный статус";
                        break;
                }
            @endphp

            </td>
            <td>{{ $sale->due_date }}</td>
            <td>{{ $sale->sale_date }}</td>
            <td>{{ $sale->planned_delivery_date }}</td>
            <td>{{ $sale->actual_delivery_date }}</td>
            <td>{{ $sale->quantity }}</td>
            <td>{{ $sale->total_price }}</td>
            <td>{{ $sale->agent?->name ?? '—' }}</td>
            <td>{{ $sale->customer?->name ?? '—' }}</td>
            <td>{{ $sale->supplier?->name ?? '—' }}</td>
            <td>{{ $sale->product?->name ?? '—' }}</td>
            <td>{{ $sale->creator?->name ?? '—' }}</td>
            <td>{{ $sale->created_at }}</td>
            <td>{{ $sale->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
