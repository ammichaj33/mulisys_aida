<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestCancellation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'loanDocIdFk',
        'repaymentIdFk',
        'cancelledMonth',
        'cancelledInterestAmount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cancelledInterestAmount' => 'decimal:2',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'interest_cancellations';

    /**
     * Get the loan document that owns the interest cancellation.
     */
    public function loanDoc()
    {
        return $this->belongsTo(LoanDoc::class, 'loanDocIdFk');
    }

    /**
     * Get the loan repayment that owns the interest cancellation.
     */
    public function loanRepayment()
    {
        return $this->belongsTo(LoanRepayment::class, 'repaymentIdFk');
    }
}
