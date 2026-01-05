<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $primaryKey = 'loanRepaymentId';

    protected $table = 'loanrepayment';

    protected $fillable = [
        'amount',
        'loanDocIdFk',
        'repaymentDate',
        'repaymentTypeIdFk',
        'userIdFk',
        'description',
    ];

    public function loanDoc()
    {
        return $this->belongsTo(LoanDoc::class, 'loanDocIdFk', 'loanDocId');
    }

    public function repaymentType()
    {
        return $this->belongsTo(RepaymentType::class, 'repaymentTypeIdFk', 'repaymentTypeID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userIdFk', 'userId');
    }

    protected $casts = [
        'repaymentDate' => 'date',
        'createdAt' => 'datetime',
    ];
}
