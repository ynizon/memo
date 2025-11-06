<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref',
        'name',
        'icon',
        'color',
        'user_id',
        'active',
        'rate',
        'from',
        'to',
        'amount',
        'amount_now',
        'account_id',
        'needrefresh',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function amounts(): HasMany
    {
        return $this->hasMany(LoanAmount::class)->orderBy("created_at","desc");
    }

    public function amountsGraph(): HasMany
    {
        return $this->hasMany(LoanAmount::class)
            ->orderBy("created_at","asc");
    }

    public function refresh(){
        //Get first date of loan
        $from = Loan::where("user_id","=", Auth::user()->id)->min('from');
        $firstDay = substr($from,0,7)."-01";
        $firstDate = new DateTime($firstDay);
        $lastDay = date("Y-m-01");
        $lastDate = new DateTime($lastDay);

        //Delete calculated months
        DB::table('loan_amounts')
            ->where('loan_id', $this->account_id)
            ->delete();

        //Init all months
        $months = [];
        while ($firstDate->format("Y-m-01") <= $lastDate->format('Y-m-01')){
            $months[$lastDate->format('Y-m-01')] = $this->amount;
            $firstDate->modify('+1 month');
        }

        //Look for ref
        $transactions = DB::table('transactions')
            ->where('account_id', $this->account_id)
            ->where('name', "like", "%".$this->ref."%")
            ->selectRaw("SUM(transactions.amount) as sum_amount,
                STRFTIME('%Y-%m-01', transactions.created_at) as month")
            ->groupBy("month")
            ->orderBy("month","asc")
            ->get();

        $firstDate = new DateTime($firstDay);
        $currentAmount = 0;
        while ($firstDate->format("Y-m-01") <= $lastDate->format('Y-m-01')){
            $sumMonth = 0;
            foreach ($transactions as $transaction) {
                if ($transaction->month == $firstDate->format('Y-m-01')) {
                    $sumMonth += $transaction->sum_amount;
                    $currentAmount += $transaction->sum_amount;
                }
            }

            $months[$firstDate->format('Y-m-01')] = $sumMonth;
            $firstDate->modify('+1 month');
        }

        //Fill AccountAmount table
        foreach ($months as $month => $monthlyAmount){
            $loanAmount = new LoanAmount();
            $loanAmount->created_at = $month;
            $loanAmount->loan_id = $this->id;
            $loanAmount->amount = round($monthlyAmount,2);
            $loanAmount->save();
        }

        $this->amount_now = $this->amount + $currentAmount;
        $this->save();
    }
}
