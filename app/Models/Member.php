<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstName',
        'lastName',
        'phoneNumber',
        'birthDate',
        'gender',
        'address',
        'email',
        'institutionFrom',
        'photo',
        'idCard',
        'isActive',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birthDate' => 'date',
        'isActive' => 'boolean',
        'createdAt' => 'datetime',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'memberId';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'members';

    /**
     * Get the loan documents for the member.
     */
    public function loanDocs()
    {
        return $this->hasMany(LoanDoc::class, 'memberIdFk');
    }

    /**
     * Get the SMS notifications for the member.
     */
    public function smsNotifications()
    {
        return $this->hasMany(SmsNotification::class, 'memberIdFk');
    }
}
