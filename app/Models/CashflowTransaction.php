<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashflowTransaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'cashflowTransactionId';
    protected $table = 'cashflow_transactions';

    protected $fillable = [
        'transactionDate',
        'transactionType',
        'categoryIdFk',
        'accountIdFk',
        'amount',
        'description',
        'paymentMethod',
        'referenceNumber',
        'loanDocIdFk',
        'memberIdFk',
        'userIdFk',
        'status',
        'confirmedAt',
        'confirmedBy',
        'attachmentPath',
    ];

    protected $casts = [
        'transactionDate' => 'date',
        'amount' => 'decimal:2',
        'confirmedAt' => 'datetime',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    /**
     * Get the category that owns the transaction.
     */
    public function category()
    {
        return $this->belongsTo(CashflowCategory::class, 'categoryIdFk', 'categoryId');
    }

    /**
     * Get the account that owns the transaction.
     */
    public function account()
    {
        return $this->belongsTo(CashflowAccount::class, 'accountIdFk', 'accountId');
    }

    /**
     * Get the loan document associated with the transaction.
     */
    public function loanDoc()
    {
        return $this->belongsTo(LoanDoc::class, 'loanDocIdFk', 'loanDocId');
    }

    /**
     * Get the member associated with the transaction.
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'memberIdFk', 'memberId');
    }

    /**
     * Get the user who created the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userIdFk', 'userId');
    }

    /**
     * Get the user who confirmed the transaction.
     */
    public function confirmedByUser()
    {
        return $this->belongsTo(User::class, 'confirmedBy', 'userId');
    }

    /**
     * Scope a query to only include income transactions.
     */
    public function scopeIncome($query)
    {
        return $query->where('transactionType', 'income');
    }

    /**
     * Scope a query to only include expense transactions.
     */
    public function scopeExpense($query)
    {
        return $query->where('transactionType', 'expense');
    }

    /**
     * Scope a query to only include confirmed transactions.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }
}

