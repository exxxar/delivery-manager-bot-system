<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Суммарно</title>
</head>
<body>

@php
    // Базовые суммы
    $summaryArray = [
        "summary_total_sales" => 0,
        "summary_after_tax" => 0,
        "revenue_without_tax_total" => 0,
        "agent_salary" => 0,
        "agent_reward" => 0,
        "transfer_from_after_tax" => 0,
    ];

    // Собираем уникальных админов
    $uniqueAdmins = collect($data)
        ->pluck('admins')
        ->flatten(1)
        ->unique('admin_id')
        ->values()
        ->toArray();

    // Инициализируем суммы по админам
    foreach ($uniqueAdmins as $admin) {
        $summaryArray["admin_" . $admin["admin_id"]] = 0;
    }
@endphp

<table>
    <tr>
        <th style="width: 200px;font-weight: bold;">Оператор</th>
        <th style="width: 200px;font-weight: bold;">Объем продаж</th>
        <th style="width: 200px;font-weight: bold;text-align: right;">Выручка</th>
        <th style="width: 200px;font-weight: bold;text-align: right;">ЗП Оператора</th>
        <th style="width: 200px;font-weight: bold;text-align: right;">ЗП Оператора (минус налог)</th>

        {{-- Заголовки админов --}}
        @foreach($uniqueAdmins as $admin)
            <th style="width: 200px;font-weight: bold;text-align: right;">
                ЗП {{ $admin["admin_name"] ?? $admin["admin_id"] ?? '-' }}
            </th>
        @endforeach

        <th style="width: 200px;font-weight: bold;text-align: right;">Переводы</th>
    </tr>

    {{-- Строки по агентам --}}
    @foreach($data as $item)
        <tr>
            <th>{{ $item["agent"]["name"] }}</th>
            <th>{{ $item["summary"]["total_sales"] }}</th>
            <th>{{ number_format($item["summary"]["revenue_without_tax_total"], 0, ',', '') }}</th>
            <th>{{ number_format($item["agent"]["reward"], 0, ',', '') }}</th>
            <th>{{ number_format($item["agent"]["salary"], 0, ',', '') }}</th>

            @php
                // Суммируем общие показатели
                $summaryArray["summary_total_sales"] += $item["summary"]["total_sales"];
                $summaryArray["summary_after_tax"] += $item["summary"]["after_tax"];
                $summaryArray["revenue_without_tax_total"] += $item["summary"]["revenue_without_tax_total"];
                $summaryArray["agent_reward"] += $item["agent"]["reward"];
                $summaryArray["agent_salary"] += $item["agent"]["salary"];

                // Преобразуем админов текущего агента в map admin_id => данные
                $adminsMap = collect($item["admins"])->keyBy("admin_id");
            @endphp

            {{-- Выводим ЗП админов строго в их колонках --}}
            @foreach($uniqueAdmins as $admin)
                <td style="text-align: right;">
                    @if(isset($adminsMap[$admin["admin_id"]]))
                        {{ number_format($adminsMap[$admin["admin_id"]]["income_after_tax"], 0, ',', '') }}

                        @php
                            $summaryArray["admin_" . $admin["admin_id"]] +=
                                $adminsMap[$admin["admin_id"]]["income_after_tax"];
                        @endphp
                    @else
                        0
                    @endif
                </td>
            @endforeach

            <td style="text-align: right;">
                {{ number_format($item["summary"]["transfer_from_after_tax"], 0, ',', '') }}
            </td>

            @php
                $summaryArray["transfer_from_after_tax"] += $item["summary"]["transfer_from_after_tax"];
            @endphp
        </tr>
    @endforeach

    {{-- Итоговая строка --}}
    <tr>
        <td style="font-weight: bold;">Итого</td>
        <td style="font-weight: bold;">{{ $summaryArray["summary_total_sales"] }}</td>
        <td style="font-weight: bold;">
            {{ number_format($summaryArray["revenue_without_tax_total"], 0, ',', '') }}
        </td>
        <td style="font-weight: bold;">
            {{ number_format($summaryArray["agent_reward"], 0, ',', '') }}
        </td>
        <td style="font-weight: bold;">
            {{ number_format($summaryArray["agent_salary"], 0, ',', '') }}
        </td>

        {{-- Итоги по админам --}}
        @foreach($uniqueAdmins as $admin)
            <td style="font-weight: bold;">
                {{ number_format($summaryArray["admin_" . $admin["admin_id"]], 0, ',', '') }}
            </td>
        @endforeach

        <td style="font-weight: bold;">
            {{ number_format($summaryArray["transfer_from_after_tax"], 0, ',', '') }}
        </td>
    </tr>

    <tr></tr><tr></tr>

    <tr>
        <td colspan="2"></td>
        <td style="font-weight: bold;font-size: 16px;">Оборот</td>
        <td style="font-weight: bold;font-size: 16px;">
            {{ number_format($summaryArray["summary_total_sales"], 0, ',', '') }}
        </td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td style="font-weight: bold;font-size: 16px;">Минус налог</td>
        <td style="font-weight: bold;font-size: 16px;">
            {{ number_format($summaryArray["summary_after_tax"], 0, ',', '') }}
        </td>
    </tr>
</table>

</body>
</html>
