<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
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
        'refresh',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->limit(1000);
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
            $this->refreshAmountMonths($firstTransaction, $lastAmount, $lastAmount->amount);
        }

        return $total;
    }

    private function refreshAmountMonths(?Transaction $firstTransaction, ?AccountAmount $lastAmount, float $amount)
    {
        //Delete calculated months
        DB::table('account_amounts')
            ->where('account_id', $this->id)
            ->where("calculated","=",true)
            ->delete();

        //Refresh current month
        $months = [];
        $firstDay = substr($lastAmount->created_at,0,7)."-01";
        $transactions = DB::table('transactions')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where('accounts.id', $this->id)
            ->where("transactions.created_at","<",$lastAmount->created_at)
            ->where("transactions.created_at",">=", $firstDay)
            ->selectRaw("SUM(transactions.amount) as sum_amount")
            ->get();

        foreach ($transactions as $transaction)
        {
            $accountAmount = new AccountAmount();
            $accountAmount->created_at = $firstDay;
            $accountAmount->amount = $transaction->sum_amount ? $transaction->sum_amount : 0;
            $accountAmount->calculated = true;
            $accountAmount->account_id = $this->id;
            $accountAmount->save();

            $months[$firstDay] = $accountAmount;
        }

        //Refresh old months
        $transactions = DB::table('transactions')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where("transactions.created_at","<",$firstDay)
            ->where('accounts.id', $this->id)
            ->selectRaw("SUM(transactions.amount) as sum_amount,
                STRFTIME('%Y-%m-01', transactions.created_at) as month")
            ->groupBy("month")
            ->get();

        foreach ($transactions as $transaction)
        {
            $accountAmount = new AccountAmount();
            $accountAmount->created_at = $transaction->month;
            $accountAmount->amount = $transaction->sum_amount;
            $accountAmount->calculated = true;
            $accountAmount->account_id = $this->id;
            $accountAmount->save();

            $months[$transaction->month] = $accountAmount;
        }

        //Refresh newest months
        $date = new DateTime($firstDay);
        $date->modify('+1 month');
        $nextFirstDay = $date->format('Y-m-01');

        $transactions = DB::table('transactions')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where("transactions.created_at",">=",$lastAmount->created_at)
            ->where("transactions.created_at","<",$nextFirstDay)
            ->where('accounts.id', $this->id)
            ->selectRaw("SUM(transactions.amount) as sum_amount,
                STRFTIME('%Y-%m-01', transactions.created_at) as month")
            ->groupBy("month")
            ->get();

        $totalAfterLastAmount = 0;
        foreach ($transactions as $transaction)
        {
            $totalAfterLastAmount = $transaction->sum_amount;
        }

        $transactions = DB::table('transactions')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where("transactions.created_at",">=",$nextFirstDay)
            ->where('accounts.id', $this->id)
            ->selectRaw("SUM(transactions.amount) as sum_amount,
                STRFTIME('%Y-%m-01', transactions.created_at) as month")
            ->groupBy("month")
            ->get();

        foreach ($transactions as $transaction)
        {
            $accountAmount = new AccountAmount();
            $accountAmount->created_at = $transaction->month;
            $accountAmount->amount = $transaction->sum_amount;
            $accountAmount->calculated = true;
            $accountAmount->account_id = $this->id;
            $accountAmount->save();

            if ($transaction->month == $nextFirstDay){
                $months[$transaction->month] = $accountAmount + $totalAfterLastAmount;
            } else {
                $months[$transaction->month] = $accountAmount;
            }
        }
        if (count($transactions) == 0)
        {
            $accountAmount = new AccountAmount();
            $accountAmount->created_at = $nextFirstDay;
            $accountAmount->account_id = $this->id;
            $accountAmount->amount = $lastAmount->amount + $totalAfterLastAmount;
            $accountAmount->save();
        }

        //Fill months without transaction
        $firstDay = substr($firstTransaction->created_at,0,7)."-01";
        $firstDate = new DateTime($firstDay);
        $lastDay = substr($lastAmount->created_at,0,7)."-01";
        $lastDate = new DateTime($lastDay);
        while ($lastDate->format('Y-m-01') >= $firstDate->format("Y-m-01")){
            if (isset($months[$lastDate->format('Y-m-01')]))
            {
                $accountAmount = $months[$lastDate->format('Y-m-01')];
            } else {
                $accountAmount = new AccountAmount();
                $accountAmount->created_at = $lastDate->format('Y-m-01');
                $accountAmount->account_id = $this->id;
            }
            $accountAmount->amount = $amount - $accountAmount->amount;
            $amount = $accountAmount->amount;
            $accountAmount->save();
            $lastDate->modify('-1 month');
        }

    }

    public function lastAmount(){
        $amountTmp = new AccountAmount();
        $amounts = AccountAmount::where("account_id","=",$this->id)->orderBy("created_at","desc")->get();

        foreach ($amounts as $amount){
            return $amount;
        }
        return $amountTmp;
    }
}
