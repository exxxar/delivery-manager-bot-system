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

@endphp
<body>


@php
    $step = 0;
    $adminSum = 0;

    $tmp = [];
    // Преобразуем коллекцию в массив
    $rows = $sales_by_date_supplier->toArray();

    foreach ($rows as $row)
         $adminSum+=!is_null($row['revenue_total'])?$row['revenue_total']*0.04:0;

    // Сколько строк нужно добавить
    $missing = 9 - count($rows);

    // Добавляем пустые строки
    for ($i = 0; $i < $missing; $i++) {
        $rows[] = [
            'date' => '',
            'supplier_name' => '',
            'sale_amount' => null,
            'percent' => null,
            'revenue_total' => null,
            'revenue_after_tax' => null,

        ];
    }
@endphp

<table>
    <thead>
    <tr>
        <th style="width: 150px;font-weight: bold;">Дата</th>
        <th style="width: 150px;font-weight: bold;">Имя</th>
        <th style="width: 150px;font-weight: bold;">Сумма продажи</th>
        <th style="width: 150px;font-weight: bold;">Выручка (детально)</th>
        <th style="width: 150px;font-weight: bold;">Админский 4%</th>

        <th style="width: 150px;font-weight: bold;"></th>
        <th style="width: 150px;font-weight: bold;">Выручка</th>
        <th style="width: 150px;font-weight: bold;">Админский 4%</th>

        @foreach($admins as $admin)
            <th style="width: 150px;font-weight: bold;">
                {{ $admin['admin_name'] }} ({{ $admin['percent'] }}
                % {{($admin["mentor_award"] ?? 0) > 0?"+ за наставника":""}} )

            </th>
        @endforeach


    </tr>
    </thead>
    <tbody>
    @foreach($rows as $sale)
        <tr>
            <td>{{ $sale['date'] }}</td>
            <td>{{ $sale['supplier_name'] }}</td>
            <td>{{ !is_null($sale['sale_amount'])?number_format($sale['sale_amount'], 2, ',', ' '):'' }}</td>
            <td>{{ !is_null($sale['revenue_total'])?number_format($sale['revenue_total'], 2, ',', ' '):'' }}</td>
            <td>
                {{ !is_null($sale['revenue_total'])?number_format($sale['revenue_total']*0.04, 2, ',', ' '):'' }}

            </td>
            @if ($step == 0)
                <td style="width: 150px;font-weight: bold;">
                    Сумма
                </td>
                <td style="width: 150px;font-weight: bold;">
                    {{ number_format($summary['revenue_total'], 2, ',', ' ') }}
                </td>

                <td style="width: 150px;font-weight: bold;">
                    {{ number_format($adminSum, 2, ',', ' ') }}
                </td>
                @foreach($admins as $admin)
                    <td style="width: 150px;font-weight: bold;">
                        {{ number_format($admin['income_total'] , 2, ',', ' ') }}
                    </td>
                @endforeach
            @endif

            @if ($step == 1)
                <td style="width: 150px;font-weight: bold;">
                    Минус налог
                </td>
                <td style="width: 150px;font-weight: bold;">
                    {{ number_format($summary['revenue_without_tax_total'], 2, ',', ' ') }}
                </td>
                <td style="width: 150px;font-weight: bold;background:yellow;">
                    {{ number_format($adminSum - $adminSum*(env("TAX_PERCENT")/100), 2, ',', ' ') }}
                </td>
                @foreach($admins as $admin)
                    <td style="width: 150px;font-weight: bold;">
                        {{ number_format($admin['income_after_tax'] , 2, ',', ' ') }}
                    </td>
                @endforeach
            @endif

            @if ($step == 2)
                <td style="width: 150px;font-weight: bold;">
                    Переводы 8%
                </td>
                <td style="width: 150px;font-weight: bold;">
                    {{ number_format($summary['transfer_from_total'], 2, ',', ' ') }}
                </td>
            @endif

            @if ($step == 3)
                <td style="width: 150px;font-weight: bold;">
                    8% минус налог
                </td>
                <td style="width: 150px;font-weight: bold;">
                    {{ number_format($summary['transfer_from_after_tax'], 2, ',', ' ') }}
                </td>
            @endif
            @if ($step == 5)
                <td style="width: 150px;font-weight: bold;">

                </td>
                <td style="width: 150px;font-weight: bold;background:yellow;">
                    Зарплата админа
                </td>
            @endif
            @if ($step == 8)
                <td style="width: 150px;font-weight: bold;">
                    Переводы 3%
                </td>
                <td style="width: 150px;font-weight: bold;">
                    {{ number_format($summary['revenue_total']*0.03, 2, ',', ' ')}}
                </td>

            @endif
        </tr>

        @php
            $step++;
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
        <td style="font-weight: bold;">Итого</td>
        <td></td>
        <td style="font-weight: bold;">{{ number_format($summary['total_sales'] , 2, ',', ' ') }}</td>
        <td style="font-weight: bold;">{{ number_format($summary['revenue_total'] , 2, ',', ' ') }}</td>
        <td style="font-weight: bold;">{{number_format($adminSum , 2, ',', ' ')}}</td>
        <td></td>
    </tr>
    </tbody>
</table>


</body>
</html>
