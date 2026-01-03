<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'fullName',
        'role',
        'phoneNumber',
        'email',
        'isActive',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'isActive' => 'boolean',
        'createdAt' => 'datetime',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'userId';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Get the loan documents for the user.
     */
    public function loanDocs()
    {
        return $this->hasMany(LoanDoc::class, 'memberIdFk');
    }

    /**
     * Get the loan repayments for the user.
     */
    public function loanRepayments()
    {
        return $this->hasMany(LoanRepayment::class, 'userIdFk');
    }

    /**
     * Get the historique records for the user.
     */
    public function historique()
    {
        return $this->hasMany(Historique::class, 'userIdFk');
    }
}
