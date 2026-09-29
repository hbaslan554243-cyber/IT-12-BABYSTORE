<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'cashier_name',
        'payment_method',
        'gcash_reference',
        'total_amount',
        'cash_received',
        'change_due',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
