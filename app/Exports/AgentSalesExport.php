<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AgentSalesExport implements FromView
{
    public function __construct(
        public int $agentId,
        public string $dateFrom,
        public string $dateTo
    ) {}

    public function view(): View
    {
        $sales = Sale::with(['agent', 'supplier'])
            ->where('status', 'completed')
            ->where('agent_id', $this->agentId)
            ->whereBetween('actual_delivery_date', [$this->dateFrom, $this->dateTo])
            ->orderBy('actual_delivery_date')
            ->get();

        return view('exports.agent_sales', [
            'sales' => $sales,
            'percent' => env('AGENT_PERCENT'),
        ]);
    }
}
