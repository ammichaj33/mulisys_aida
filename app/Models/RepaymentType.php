<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepaymentType extends Model
{
    use HasFactory;

    protected $primaryKey = 'repaymentTypeID';

    protected $table = 'repaymenttype';

    protected $fillable = [
        'repaymentType',
        'description',
    ];
}
