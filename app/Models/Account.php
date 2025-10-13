<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'rib',
        'name',
        'icon',
        'position',
        'color',
        'user_id',
        'active',
        'needrefresh',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->orderBy("created_at","desc")->limit(1000);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function amounts(): HasMany
    {
        return $this->hasMany(AccountAmount::class)->orderBy("created_at","desc");
    }

    public function amountsGraph(): HasMany
    {
        return $this->hasMany(AccountAmount::class)
            ->where("calculated","=",true)
            ->orderBy("created_at","asc");
    }

    public function refreshAmount(?Transaction $firstTransaction): float
    {
        $lastAmount = AccountAmount::where("account_id","=",$this->id)
            ->where("calculated","=",false)
            ->orderBy("created_at","desc")->first();
        $total = 0;
        if ($lastAmount){
            //Current total of the account
            $transactions = DB::table('transactions')
                ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                ->where('accounts.id', $this->id)
                ->where("transactions.created_at",">",$lastAmount->created_at)
                ->selectRaw("SUM(transactions.amount) as sum_amount")
                ->get();

            $total = $lastAmount->amount;
            foreach ($transactions as $transaction)
            {
                $total = $total + $transaction->sum_amount;
            }

            //Calculate of the last months
            $lastAmounts = AccountAmount::where("account_id","=",$this->id)
                ->where("calculated","=",false)
                ->orderBy("created_at","desc")->get();

            $this->refreshAmountMonths($firstTransaction, $lastAmounts);

            foreach ($this->loans as $loan)
            {
                $loan->refresh();
            }
        }

        return $total;
    }

    private function refreshAmountMonths(?Transaction $firstTransaction, Collection $lastAmounts): void
    {
        $firstDay = substr($firstTransaction->created_at,0,7)."-01";
        $firstDate = new DateTime($firstDay);
        $lastDay = date("Y-m-d");
        $lastDate = new DateTime($lastDay);
        $lastDate->modify('+1 month');

        //Delete calculated months
        DB::table('account_amounts')
            ->where('account_id', $this->id)
            ->where("calculated","=",true)
            ->delete();

        //Init all months
        $months = [];
        while ($lastDate->format('Y-m-01') >= $firstDate->format("Y-m-01")){
            $months[$lastDate->format('Y-m-01')] = 0;
            $lastDate->modify('-1 month');
        }

        //Fill old months
        foreach ($lastAmounts as $lastAmount){
            $transactions = DB::table('transactions')
                ->where('accounts_id', $this->id)
                ->where("transactions.created_at","<", $lastAmount->created_at)
                ->where("transactions.created_at",">=", $firstDay)
                ->selectRaw("SUM(transactions.amount) as sum_amount,
                STRFTIME('%Y-%m-01', transactions.created_at) as month")
                ->groupBy("month")
                ->orderBy("month","asc")
                ->get();

            $firstDate = new DateTime($firstDay);
            $currentAmount = $lastAmount->amount;
            $lastDay = $lastAmount->created_at;
            $lastDate = new DateTime($lastDay);

            while ($lastDate->format('Y-m-01') >= $firstDate->format("Y-m-01")){
                foreach ($transactions as $transaction) {
                    if ($transaction->month == $lastDate->format('Y-m-01')){
                        $currentAmount -= $transaction->sum_amount;
                    }
                }

                $months[$lastDate->format('Y-m-01')] = $currentAmount;
                $lastDate->modify('-1 month');
            }
        }

        //Fill months after the last amount
        if (count($lastAmounts) > 0) {
            $lastAmount = $lastAmounts->first();

            $transactions = DB::table('transactions')
                ->where('accounts_id', $this->id)
                ->where("transactions.created_at",">=",$lastAmount->created_at)
                ->selectRaw("SUM(transactions.amount) as sum_amount,
                STRFTIME('%Y-%m-01', transactions.created_at) as month")
                ->groupBy("month")
                ->orderBy("month","asc")
                ->get();

            $firstDay = $lastAmount->created_at;
            $firstDate = new DateTime($firstDay);

            $lastDay = date("Y-m-d");
            $lastDate = new DateTime($lastDay);
            $lastDate->modify('+1 month');
            $currentAmount = $lastAmount->amount;

            foreach ($transactions as $transaction) {
                if ($transaction->month == $firstDate->format('Y-m-01')) {
                    echo $transaction->sum_amount."xx";
                    $currentAmount += $transaction->sum_amount;
                }
            }
            $months[$firstDate->format('Y-m-01')] = $currentAmount;

            // Next months
            $firstDate->modify('+1 month');
            while ($firstDate->format("Y-m-01") <= $lastDate->format('Y-m-01')) {
                foreach ($transactions as $transaction) {
                    if ($transaction->month == $firstDate->format('Y-m-01')) {
                        $currentAmount += $transaction->sum_amount;
                    }
                }

                $months[$firstDate->format('Y-m-01')] = $currentAmount;
                $firstDate->modify('+1 month');
            }
        }

        //Fill AccountAmount table
        foreach ($months as $month => $monthlyAmount){
            $accountAmount = new AccountAmount();
            $accountAmount->created_at = $month;
            $accountAmount->account_id = $this->id;
            $accountAmount->amount = round($monthlyAmount,2);
            $accountAmount->calculated = 1;
            $accountAmount->save();
        }
    }
//
//    private function refreshAmountMonths(?Transaction $firstTransaction, ?AccountAmount $lastAmount, array
//    $oldAmounts)
//    {
//        // if date = 2025-09-01 so all transactions for this month is in
//        //event if they are after but before 2025-10-01
//
//        //Delete calculated months
//        $lastDate = $lastAmount->created_at;
//        $firstDay = substr($firstTransaction->created_at,0,7)."-01";
//        $firstDate = new DateTime($firstDay);
//
//        DB::table('account_amounts')
//            ->where('account_id', $this->id)
//            ->where("calculated","=",true)
//            ->delete();
//
//        //Refresh current month of the last amount (between first day of the month and lastAmount date created)
//        $months = [];
//        $firstDay = substr($lastAmount->created_at,0,7)."-01";
//        $months[$firstDay] = $lastAmount->amount;
//
//        $date = new DateTime($firstDay);
//        $date->modify('+1 month');
//        $nextFirstDay = $date->format('Y-m-01');
//
//        $transactions = DB::table('transactions')
//            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
//            ->where('accounts.id', $this->id)
//            ->where("transactions.created_at","<",$lastAmount->created_at)
//            ->where("transactions.created_at",">=", $firstDay)
//            ->selectRaw("SUM(transactions.amount) as sum_amount")
//            ->get();
//
//        foreach ($transactions as $transaction)
//        {
//            $months[$firstDay] = $months[$firstDay] + $transaction->sum_amount;
//        }
//
//        //Refresh old months
//        $transactions = DB::table('transactions')
//            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
//            ->where("transactions.created_at","<",$firstDay)
//            ->where('accounts.id', $this->id)
//            ->selectRaw("SUM(transactions.amount) as sum_amount,
//                STRFTIME('%Y-%m-01', transactions.created_at) as month")
//            ->groupBy("month")
//            ->orderBy("month","asc")
//            ->get();
//
//        foreach ($transactions as $transaction)
//        {
//            $months[$transaction->month] = $months[$firstDay] + $transaction->sum_amount;
//        }
//        //Fill empty months
//        $lastDay = substr($lastDate,0,7)."-01";
//        $lastDate = new DateTime($lastDay);
//
//        $oldAmount = 0;
//        while ($lastDate->format('Y-m-01') >= $firstDate->format("Y-m-01")){
//            if (isset($months[$lastDate->format('Y-m-01')])){
//                $oldAmount = $months[$lastDate->format('Y-m-01')];
//            }
//            if (!isset($months[$lastDate->format('Y-m-01')])){
//                $months[$lastDate->format('Y-m-01')] = $oldAmount;
//            }
//            $lastDate->modify('-1 month');
//        }
//
//        //Refresh current month of the last amount (between lastAmount date created and next first day of the month)
//        $transactions = DB::table('transactions')
//            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
//            ->where("transactions.created_at",">=",$lastAmount->created_at)
//            ->where("transactions.created_at","<",$nextFirstDay)
//            ->where('accounts.id', $this->id)
//            ->selectRaw("SUM(transactions.amount) as sum_amount,
//                STRFTIME('%Y-%m-01', transactions.created_at) as month")
//            ->groupBy("month")
//            ->orderBy("month","asc")
//            ->get();
//
//        $totalAfterLastAmount = $lastAmount->amount;
//        foreach ($transactions as $transaction)
//        {
//            $totalAfterLastAmount = $totalAfterLastAmount + $transaction->sum_amount;
//        }
//
//        //Refresh next months
//        $transactions = DB::table('transactions')
//            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
//            ->where("transactions.created_at",">=",$nextFirstDay)
//            ->where('accounts.id', $this->id)
//            ->selectRaw("SUM(transactions.amount) as sum_amount,
//                STRFTIME('%Y-%m-01', transactions.created_at) as month")
//            ->groupBy("month")
//            ->orderBy("month", "asc")
//            ->get();
//
//        if (count($transactions) == 0){
//            $months[$nextFirstDay] = $totalAfterLastAmount;
//        } else {
//            foreach ($transactions as $transaction)
//            {
//                $months[$transaction->month] = $transaction->sum_amount + $totalAfterLastAmount;
//                $totalAfterLastAmount = $totalAfterLastAmount + $transaction->sum_amount;
//            }
//        }
//
//        //Fill months event if they havent transaction
//        $lastDate = new DateTime($lastDay);
//        $lastDate = $lastDate->format('Y-m-01');
//        if (date("Y-m-d") > $lastDate){
//            $lastDate = date("Y-m-01");
//        }
//
//        //Get user input of last month
//        foreach ($months as $month => $amount){
//            if (isset($oldAmounts[$month]) && $month != date("Y-m-01")) {
//                //Override calculated value
//                $months[$month] = $oldAmounts[$month];
//            }
//        }
//
//        $lastDay = substr($lastDate,0,7)."-01";
//        $lastDate = new DateTime($lastDay);
//        while ($lastDate->format('Y-m-01') >= $firstDate->format("Y-m-01")){
//            $monthlyAmount = 0;
//            if (isset($months[$lastDate->format('Y-m-01')]))
//            {
//                $monthlyAmount = $months[$lastDate->format('Y-m-01')];
//            }
//
//            $accountAmount = new AccountAmount();
//            $accountAmount->created_at = $lastDate->format('Y-m-01');
//            $accountAmount->account_id = $this->id;
//            $accountAmount->amount = $monthlyAmount;
//            $accountAmount->calculated = 1;
//            $accountAmount->save();
//            $months[$lastDate->format('Y-m-01')] = $accountAmount->amount;
//            $lastDate->modify('-1 month');
//        }
//
////        krsort($months);
////        echo var_dump($months);exit();
//    }

    public function lastAmount(){
        $amountTmp = new AccountAmount();
        $amounts = AccountAmount::where("account_id","=",$this->id)->orderBy("created_at","desc")->get();

        foreach ($amounts as $amount){
            return $amount;
        }
        return $amountTmp;
    }
}
