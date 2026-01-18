<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Общее</title>
</head>
<body>
<!-- resources/views/excel_export.blade.php -->


    <h4>Экспорт данных</h4>

    <table cellspacing="0" cellpadding="0" border="1">
        <thead>
        <tr>
            <th style="font-weight: bold;">№</th>
            <th style="width:300px;font-weight: bold;">Поставщики</th>
            <th style="width:150px;font-weight: bold;text-align: center;">Базовый процент, %</th>
            <th style="width:150px;font-weight: bold;text-align: center;">Проценты, руб</th>
            <th style="width:100px;font-weight: bold;text-align: center;">Январь, руб</th>
            <th style="width:100px;font-weight: bold;text-align: center;">Февраль, руб</th>
            <th style="width:100px;font-weight: bold;text-align: center;">Март, руб</th>
            <th style="width:100px;font-weight: bold;text-align: center;">Апрель, руб</th>
            <th style="width:100px;font-weight: bold;text-align: center;">Май, руб</th>
            <th style="width:100px;font-weight: bold;text-align: center;">Июнь, руб</th>
            <th style="width:100px;font-weight: bold;text-align: center;">Июль, руб</th>
            <th style="width:100px;font-weight: bold;text-align: center;">Август, руб</th>
            <th style="width:100px;font-weight: bold;text-align: center;">Сентябрь, руб</th>
            <th style="width:100px;font-weight: bold;text-align: center;">Октябрь, руб</th>
            <th style="width:100px;font-weight: bold;text-align: center;">Ноябрь, руб</th>
            <th style="width:100px;font-weight: bold;text-align: center;">Декабрь, руб</th>
            <th style="width:150px;font-weight: bold;text-align: center;">Итого за год, руб</th>
        </tr>
        </thead>
        <tbody>
        @php
            $yearSum = 0;
            $index=1;
        @endphp
        @foreach($data->details as $item)
            <tr>
                <td>{{$index}}</td>
                <td style="width:300px;">{{ $item['supplier'] }}</td>
                <td style="width:150px;text-align: center;">{{ $item['base_percent'] * 100}}</td>
                <td style="width:150px;text-align: center;">{{ number_format($item['percentage'],2,'.', '') }}</td>
                <td style="width:100px;text-align: center;">{{ $item['january'] }}</td>
                <td style="width:100px;text-align: center;">{{ $item['february'] }}</td>
                <td style="width:100px;text-align: center;">{{ $item['march'] }}</td>
                <td style="width:100px;text-align: center;">{{ $item['april'] }}</td>
                <td style="width:100px;text-align: center;">{{ $item['may'] }}</td>
                <td style="width:100px;text-align: center;">{{ $item['june'] }}</td>
                <td style="width:100px;text-align: center;">{{ $item['july'] }}</td>
                <td style="width:100px;text-align: center;">{{ $item['august'] }}</td>
                <td style="width:100px;text-align: center;">{{ $item['september'] }}</td>
                <td style="width:100px;text-align: center;">{{ $item['october'] }}</td>
                <td style="width:100px;text-align: center;">{{ $item['november'] }}</td>
                <td style="width:100px;text-align: center;">{{ $item['december'] }}</td>
                <td style="width:150px;font-weight: bold;text-align: center;">{{ number_format($item['year'],2,'.', '') }}</td>
            </tr>

            @php

                $index++;
            @endphp
        @endforeach

        </tbody>
    </table>

<table cellspacing="0" cellpadding="0" border="1">
    <tbody>
        <tr>
            <td></td>
            <td style="font-weight: bold; font-size: 12px;">Итого, руб</td>
            <td style="font-weight: bold; font-size: 12px;">{{number_format($data->total_sum,2,'.', '')}}</td>
        </tr>
        <tr>
            <td></td>
            <td style="font-weight: bold; font-size: 12px;">Итого проценты, руб</td>
            <td style="font-weight: bold; font-size: 12px;">{{number_format($data->total_percentage,2,'.', '')}}</td>
        </tr>
    </tbody>
</table>
</body>
</html>
