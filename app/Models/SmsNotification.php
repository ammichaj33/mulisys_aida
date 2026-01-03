<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsNotification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'memberIdFk',
        'loanDocIdFk',
        'message',
        'status',
        'sentAt',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sentAt' => 'datetime',
        'createdAt' => 'datetime',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'notificationId';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sms_notifications';

    /**
     * Get the member that owns the SMS notification.
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'memberIdFk');
    }

    /**
     * Get the loan document that owns the SMS notification.
     */
    public function loanDoc()
    {
        return $this->belongsTo(LoanDoc::class, 'loanDocIdFk');
    }
}
