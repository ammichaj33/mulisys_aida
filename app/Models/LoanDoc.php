<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDoc extends Model
{
    use HasFactory;

    protected $primaryKey = 'loanDocId';

    protected $table = 'loandocs';

    protected $fillable = [
        'refNumber',
        'submitDate',
        'description',
        'status',
        'requestAmount',
        'loanMonths',
        'interestRate',
        'memberIdFk',
        'docPath',
    ];

    protected $casts = [
        'submitDate' => 'date',
        'createdAt' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'memberIdFk', 'memberId');
    }

    public function loanRepayments()
    {
        return $this->hasMany(LoanRepayment::class, 'loanDocIdFk', 'loanDocId');
    }

    public function penalties()
    {
        return $this->hasMany(Penalty::class, 'loanDocIdFk', 'loanDocId');
    }

    public function validationHistory()
    {
        return $this->hasMany(Historique::class, 'recordIdFk', 'loanDocId')
            ->whereIn('recordStatus', ['accepted', 'rejected', 'toreviewed', 'validated'])
            ->orderBy('trackedDate', 'desc');
    }
}
