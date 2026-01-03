<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historique extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recordIdFk',
        'recordStatus',
        'operDescription',
        'userIdFk',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trackedDate' => 'datetime',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'operationId';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'historique';

    /**
     * Get the user that owns the historique record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userIdFk');
    }
}
