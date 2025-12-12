{{-- resources/views/exports/suppliers_revenue.blade.php --}}
    <!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    @php
        $newTitle = \App\Facades\BusinessLogicFacade::method()
        ->truncateTitle($title);
    @endphp
    <title>{{$newTitle}}</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 8px; }
        th { background: #f2f2f2; text-align: left; }
        tfoot td { font-weight: bold; }
    </style>
</head>
<body>
<h3>Отчёт по выручке младшего админа {{$title}}</h3>
<table>
    <thead>
    <tr>
        <th style="width: 150px;font-weight: bold;">Дата</th>
        <th style="width: 300px;font-weight: bold;">Поставщик</th>
        <th style="width: 100px;font-weight: bold;">Выручка, руб</th>
    </tr>
    </thead>
    <tbody>
    @php($total = 0)
    @forelse($sales as $sale)
        @php($sale = (object)$sale)
        @php($total += (float)($sale->total_price ?? 0))
        <tr>
            <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}</td>
            <td>{{ $sale->supplier_name ?? '-' }}</td>
            <td>{{ number_format((float)$sale->total_price, 2, ',', ' ') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="3">Данных нет</td>
        </tr>
    @endforelse
    </tbody>
    <tfoot>
    <tr>
        <td colspan="2">Итого</td>
        <td>{{ number_format($total, 2, ',', ' ') }}</td>
    </tr>
    </tfoot>
</table>
</body>
</html>
