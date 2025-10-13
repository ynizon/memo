<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanAmount extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'created_at',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
