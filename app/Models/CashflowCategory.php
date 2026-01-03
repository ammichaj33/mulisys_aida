<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashflowCategory extends Model
{
    use HasFactory;

    protected $primaryKey = 'categoryId';
    protected $table = 'cashflow_categories';

    protected $fillable = [
        'categoryName',
        'categoryType',
        'parentCategoryId',
        'description',
        'isActive',
    ];

    protected $casts = [
        'isActive' => 'boolean',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    /**
     * Get the parent category.
     */
    public function parentCategory()
    {
        return $this->belongsTo(CashflowCategory::class, 'parentCategoryId', 'categoryId');
    }

    /**
     * Get the child categories.
     */
    public function childCategories()
    {
        return $this->hasMany(CashflowCategory::class, 'parentCategoryId', 'categoryId');
    }

    /**
     * Get the transactions for this category.
     */
    public function transactions()
    {
        return $this->hasMany(CashflowTransaction::class, 'categoryIdFk', 'categoryId');
    }
}

