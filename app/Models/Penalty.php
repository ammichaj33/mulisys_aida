<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'loanDocIdFk',
        'penaltyMonth',
        'memberIdFk',
        'amount',
        'status',
        'reason',
        'description',
        'paidAt',
        'paidAmount',
        'createdBy',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'paidAmount' => 'decimal:2',
        'recordedDate' => 'datetime',
        'paidAt' => 'datetime',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'penalityId';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'penalities';

    /**
     * Get the loan document that owns the penalty.
     */
    public function loanDoc()
    {
        return $this->belongsTo(LoanDoc::class, 'loanDocIdFk');
    }

    /**
     * Get the member that owns the penalty.
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'memberIdFk');
    }

    /**
     * Get the user who created the penalty.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    /**
     * Montant total de la pénalité (payé + reste).
     */
    public function getTotalAmountAttribute()
    {
        if ($this->status === 'paid') {
            return round(max((float) $this->paidAmount, (float) $this->amount), 2);
        }

        return round((float) ($this->paidAmount ?? 0) + (float) $this->amount, 2);
    }

    /**
     * Reste à payer.
     */
    public function getRemainingAmountAttribute()
    {
        if ($this->status === 'paid') {
            return 0;
        }

        return round((float) $this->amount, 2);
    }

    /**
     * Paiement partiel en cours.
     */
    public function isPartiallyPaid()
    {
        return $this->status === 'notPaid' && (float) ($this->paidAmount ?? 0) > 0;
    }
}
