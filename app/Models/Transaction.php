<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'methode_payment',
        'code_transaction',
        'amount_bill',
        'amount_credit',
        'transaction_date',
        'transaction_time',
        'status'
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
