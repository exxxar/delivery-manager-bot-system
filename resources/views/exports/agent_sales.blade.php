<table>
    <thead>
    <tr>
        <th style="font-weight: bold; width:100px;">Дата</th>
        <th style="font-weight: bold; width:100px;">День недели</th>
        <th style="font-weight: bold; width:100px;">Сумма продажи, руб</th>
        <th style="font-weight: bold; width:100px;">Процент (%)</th>
        <th style="font-weight: bold; width:100px;">Вознаграждение, руб</th>
        <th style="font-weight: bold; width:100px;">Тип оплаты</th>
        <th style="font-weight: bold; width:200px;">Поставщик</th>
        <th style="font-weight: bold; width:200px;">Админ</th>
    </tr>
    </thead>
    <tbody>
    @php
        $weekDays = [
           'Monday'=>"Понедельник",
           'Tuesday'=>"Вторник",
           'Wednesday'=>"Среда",
           'Thursday'=>"Четверг",
           'Friday'=>"Пятница",
           'Saturday'=>"Суббота",
           'Sunday'=>"Воскресенье",
           'nonset'=>"Не указан",
   ];
        $totalSum = 0;
        $totalRewardSum = 0;
    @endphp
    @foreach($sales as $sale)
        @php
            $reward = $sale->total_price * ($percent ?? 0) / 100;

            $totalSum += $sale->total_price ?? 0;
            $totalRewardSum += $reward ?? 0;
        @endphp
        <tr>
            <td>{{ $sale->due_date }}</td>
            <td>{{ $weekDays[\Carbon\Carbon::parse($sale->due_date)->translatedFormat('l') ?? 'nonset'] }}</td>
            <td style="text-align: right;">{{ number_format($sale->total_price, 2, ',', ' ') }}</td>
            <td style="text-align: right;">{{ $percent }}</td>
            <td style="text-align: right;">{{ number_format($reward, 2, ',', ' ') }}</td>
            <td>
                {{ $sale->payment_type == 1 ? 'Наличный' : 'Безналичный' }}
            </td>
            <td>{{ $sale->supplier?->name }}</td>
            <td>{{ $sale->agent?->name }}</td>
        </tr>
    @endforeach
        <tr>
            <td colspan="1" style="font-weight:bold;">Итого</td>
            <td></td>
            <td style="text-align: right;font-weight: bold;">{{number_format($totalSum, 2, ',', ' ')}}</td>
            <td style="text-align: right;"></td>
            <td style="text-align: right;font-weight: bold;">{{ number_format($totalRewardSum, 2, ',', ' ') }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>
