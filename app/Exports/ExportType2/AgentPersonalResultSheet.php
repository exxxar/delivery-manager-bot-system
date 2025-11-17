<?php

namespace App\Exports\ExportType2;

use App\Facades\BusinessLogicFacade;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class AgentPersonalResultSheet implements FromView, WithTitle
{

    protected $fromDate;
    protected $toDate;
    protected $agent;

    public function __construct($agent, $fromDate = null, $toDate = null)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->agent = $agent;
    }


    public function view(): View
    {
        $sales = BusinessLogicFacade::method()
            ->getPersonalAgentSales($this->agent->id, $this->fromDate, $this->toDate);

        return view('exports.export-personal-agent-result', [
            'sales' => $sales,
            'title'=>$this->agent->name ?? 'Агент'

        ]);
    }

    public function title(): string
    {
        return $this->agent->name ?? 'Агент';
    }
}
