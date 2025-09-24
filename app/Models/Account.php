<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function refreshAmount(): float
    {
        $lastAmount = AccountAmount::where("account_id","=",$this->id)
            ->where("calculated","=",false)
            ->orderBy("created_at","desc")->first();
        $total = 0;
        if ($lastAmount){
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
        }

        return $total;
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
