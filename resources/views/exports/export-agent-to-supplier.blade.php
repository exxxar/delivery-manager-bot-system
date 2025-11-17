<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    @php
        $newTitle = \App\Facades\BusinessLogicFacade::method()
        ->truncateTitle($title);
    @endphp
    <title>{{$newTitle}}</title>
</head>
<body>
<!-- resources/views/reports/delivery_report.blade.php -->


<h4 style="font-size: 18px;">Отчет по поставкам и агентам для поставщика {{$title ?? 'не указано'}}</h4>

<!-- Таблица -->
<table cellspacing="0" cellpadding="0" border="1">
    <!-- Первая строка с агентами -->
    <tr>
        <th style="font-weight: bold;">№</th>
        <th style="width:150px;font-weight: bold;">Дата</th>
        <th style="width:150px;font-weight: bold;">День недели</th>
        @foreach($agents as $agent)
            <th style="width:200px;font-weight: bold;text-align: center;">{{ $agent->name }} </th>
        @endforeach
        <th style="width:150px;font-weight: bold;text-align: right;">Всего</th>
    </tr>

    @php
        $index = 1;
        $yearSum = 0;

        $itemSum = [];
    @endphp
        <!-- Основной контент таблицы -->
    @foreach($period as $day)

        @php
            $day = (object)$day;
        @endphp

        <tr>
            <td style="font-weight: bold;">{{$index}}</td>
            <td style="width:150px;">{{$day->date}}</td>
            <td style="width:150px;">{{$day->day_name}}</td>
            @foreach($day->day_details as $item)
                @php
                    $item = (object)$item;
                    if (is_null($itemSum[$item->agent_id] ?? null))
                        $itemSum[$item->agent_id]  = 0;
                    $itemSum[$item->agent_id] +=$item->price;
                @endphp
                <td style="width:200px;text-align: center;">{{number_format($item->price,2,'.','')}} </td>
            @endforeach
            <td style="width:150px;font-weight: bold;">{{number_format($day->total,2,'.','')}}</td>
        </tr>

        @php
            $yearSum +=$day->total;
            $index++;
        @endphp
    @endforeach

    @php
        $itemSumFull = 0;
    @endphp
    <tr>
        <td colspan="3"></td>
        @foreach($agents as $agent)
            @php
                $itemSumFull +=$itemSum[$agent->id];
            @endphp
            <td style="width:200px;text-align: center;">{{  $itemSum[$agent->id] }}</td>
        @endforeach
        <td style="font-weight: bold;">{{$itemSumFull}} \ {{$yearSum}}</td>
    </tr>
</table>

<table cellspacing="0" cellpadding="0" border="1">
    <tbody>
    <tr>
        <td></td>
        <td style="font-weight: bold; font-size: 12px;">Итого, руб</td>
        <td style="font-weight: bold; font-size: 12px;">{{number_format($total_sum,2,'.','')}}</td>
    </tr>


    </tbody>
</table>
</body>
</html>
