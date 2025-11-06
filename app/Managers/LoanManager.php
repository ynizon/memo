<?php

namespace App\Managers;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class LoanManager
{
    public static function getAllCharts($loans): array
    {
        $charts = ['all'=>["labels"=>[], "loans"=>[]]];
        foreach ($loans as $loan) {
            if (count($charts['all']['labels']) == 0){
                $charts['all']['labels'] = self::getLabels($loan);
            }
            $charts['all']['loans'][$loan->id] = self::getDatasets($loan);
        }

        return $charts;
    }

    public static function getDatasets($loan): array
    {
        $amounts = $loan->amountsGraph;
        $total = $loan->amount;
        $datas = [];

        foreach ($amounts as $amount) {
            $total += $amount->amount;
            $datas[] = $total;
        }

        return [
            "hidden" => $loan->active ? "false" : "true",
            "label"=> $loan->name,
            "data"=> $datas,
            "color"=> convertColorHexa($loan->color)
        ];
    }

    public static function getLabels($loan): array
    {
        $amounts = $loan->amountsGraph;
        $labels = [];
        foreach ($amounts as $amount) {
            $labels[] = $amount->created_at->format('m-Y');
        }

        return $labels;
    }
}
