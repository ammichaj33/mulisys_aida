<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashflowAccount extends Model
{
    use HasFactory;

    protected $primaryKey = 'accountId';
    protected $table = 'cashflow_accounts';

    protected $fillable = [
        'accountName',
        'accountType',
        'initialBalance',
        'currentBalance',
        'description',
        'isActive',
    ];

    protected $casts = [
        'initialBalance' => 'decimal:2',
        'currentBalance' => 'decimal:2',
        'isActive' => 'boolean',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    /**
     * Get the transactions for this account.
     */
    public function transactions()
    {
        return $this->hasMany(CashflowTransaction::class, 'accountIdFk', 'accountId');
    }

    /**
     * Update the current balance based on transactions.
     */
    public function updateBalance()
    {
        $income = $this->transactions()
            ->where('transactionType', 'income')
            ->where('status', 'confirmed')
            ->sum('amount');
        
        $expense = $this->transactions()
            ->where('transactionType', 'expense')
            ->where('status', 'confirmed')
            ->sum('amount');
        
        $this->currentBalance = $this->initialBalance + $income - $expense;
        $this->save();
    }
}

