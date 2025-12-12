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

@php
 $period = $revenue["period"] ?? null;
 $summary = $revenue["summary"]?? null;
 $sales_by_date_supplier = $revenue["sales_by_date_supplier"]?? null;
 $admins = $revenue["admins"]?? null;

 $transfer = 0;
@endphp
<body>
<h1>Отчёт по выручке</h1>
<p>Период: {{ $period['start'] }} — {{ $period['end'] }}</p>

<h2>Итоги</h2>
<table>
    <tr><th colspan="2">Общая сумма продаж</th><td>{{ number_format($summary['total_sales'], 2, ',', ' ') }}</td></tr>
    <tr><th colspan="2">Налог ({{ $summary['tax_percent'] }}%)</th><td>{{ number_format($summary['tax_amount'], 2, ',', ' ') }}</td></tr>
    <tr><th colspan="2">После налога</th><td>{{ number_format($summary['after_tax'], 2, ',', ' ') }}</td></tr>
    <tr><th colspan="2">Выручка</th><td>{{ number_format($summary['revenue_total'], 2, ',', ' ') }}</td></tr>
    <tr><th colspan="2">Выручка после налога</th><td>{{ number_format($summary['revenue_without_tax_total'], 2, ',', ' ') }}</td></tr>
    <tr><th colspan="2">Переводы ({{ $summary['transfer_percent'] }}%) от суммы выручки</th><td>{{ number_format($summary['transfer_from_total'], 2, ',', ' ') }}</td></tr>
    <tr><th colspan="2">Переводы от суммы после налога</th><td>{{ number_format($summary['transfer_from_after_tax'], 2, ',', ' ') }}</td></tr>
</table>

<h2>Продажи по датам и поставщикам</h2>
<table>
    <thead>
    <tr>
        <th style="width: 150px;font-weight: bold;">Дата</th>
        <th style="width: 150px;font-weight: bold;">Поставщик</th>
        <th style="width: 150px;font-weight: bold;">Сумма продажи</th>
        <th style="width: 150px;font-weight: bold;">% поставщика</th>
        <th style="width: 150px;font-weight: bold;">Выручка (до налога)</th>
        <th style="width: 150px;font-weight: bold;">Выручка (после налога)</th>
        <th style="width: 150px;font-weight: bold;">Перевод</th>
    </tr>
    </thead>
    <tbody>
    @foreach($sales_by_date_supplier as $sale)
        <tr>
            <td>{{ $sale['date'] }}</td>
            <td>{{ $sale['supplier_name'] }}</td>
            <td>{{ number_format($sale['sale_amount'], 2, ',', ' ') }}</td>
            <td>{{ $sale['percent'] }}%</td>
            <td>{{ number_format($sale['revenue_total'], 2, ',', ' ') }}</td>
            <td>{{ number_format($sale['revenue_after_tax'], 2, ',', ' ') }}</td>
            <td>{{ number_format($sale['transfer'], 2, ',', ' ') }}</td>
        </tr>
        @php
            $transfer+=$sale['transfer'];
        @endphp
    @endforeach
   {{-- <tr>
        <td>Итого</td>
        <td></td>
        <td>{{ number_format($salesSum, 2, ',', ' ') }}</td>
        <td></td>
        <td>{{ number_format($sum, 2, ',', ' ') }}</td>
        <td>{{ number_format($sumWithoutTax, 2, ',', ' ') }}</td>
    </tr>--}}
    <tr>
        <td>Итого</td>
        <td></td>
        <td>{{ number_format($summary['total_sales'] , 2, ',', ' ') }}</td>
        <td></td>
        <td>{{ number_format($summary['revenue_total'] , 2, ',', ' ') }}</td>
        <td>{{ number_format($summary['revenue_without_tax_total'] , 2, ',', ' ') }}</td>
        <td>{{ number_format($transfer, 2, ',', ' ') }}</td>
    </tr>
    </tbody>
</table>

<h2>Доход администраторов</h2>
<table>
    <thead>
    <tr>
        <th style="width: 150px;font-weight: bold;">Админ</th>
        <th style="width: 150px;font-weight: bold;">%</th>
        <th style="width: 150px;font-weight: bold;">Зарплата (до налога)</th>
        <th style="width: 150px;font-weight: bold;">Зарплата (после налога)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($admins as $admin)
        <tr>
            <td>{{ $admin['admin_name'] }}</td>
            <td>{{ $admin['percent'] }}%</td>
            <td>{{ number_format($admin['income_total'], 2, ',', ' ') }}</td>
            <td>{{ number_format($admin['income_after_tax'], 2, ',', ' ') }}</td>

        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
