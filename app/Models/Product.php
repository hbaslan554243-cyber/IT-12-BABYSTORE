<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * Fixed dropdown options for Category and Unit, so staff pick from a
     * consistent list instead of free-typing (which previously led to
     * near-duplicate values like "Diapers" vs "diaper"). Add more here if
     * the store's product range grows.
     */

    public const CATEGORY_PREFIXES = [
        'Diapers' => 'DIA',
        'Milk' => 'MLK',
        'Lotion' => 'LOT',
        'Wipes' => 'WIP',
        'Feeding' => 'FEE',
        'Clothing' => 'CLO',
        'Toys' => 'TOY',
        'Accessories' => 'ACC',
        'Other' => 'OTH',
    ];

    public const CATEGORIES = [
        'Diapers', 'Milk', 'Lotion', 'Wipes', 'Feeding', 'Clothing', 'Toys', 'Accessories', 'Other',
    ];

    public const UNITS = [
        'pc', 'pack', 'box', 'bottle', 'can', 'set', 'pair',
    ];

    /**
     * Size dropdown options: the code is what gets stored (keeps product
     * names compact on receipts/POS, e.g. "Baby Diapers - M"), the label
     * is what staff see in the dropdown.
     */
    public const SIZES = [
        'S' => 'Small',
        'M' => 'Medium',
        'L' => 'Large',
        'XL' => 'Extra Large',
        'XXL' => 'Double Large',
    ];

    protected $fillable = [
        'sku',
        'name',
        'brand',
        'variant',
        'size',
        'category',
        'unit',
        'selling_price',
        'cost_price',
        'quantity_on_hand',
        'reorder_point',
        'expiration_date',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'expiration_date' => 'date',
    ];

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /** "Baby Diapers - Cool Blue - M" style label for tight UI spaces (POS search, PO forms). */
    public function displayName(): string
    {
        return implode(' - ', array_filter([$this->name, $this->variant, $this->size]));
    }

    /** Zero stock — distinct from "low but still available". */
    public function isOutOfStock(): bool
    {
        return $this->quantity_on_hand <= 0;
    }

    /** Still in stock, but at or below the reorder point. */
    public function isLowStock(): bool
    {
        return $this->quantity_on_hand > 0 && $this->quantity_on_hand <= $this->reorder_point;
    }

    public function isExpired(): bool
    {
        return $this->expiration_date && $this->expiration_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiration_date
            && !$this->isExpired()
            && $this->expiration_date->lte(now()->addDays($days));
    }

    public function scopeLowStock($query)
    {
        return $query->where('quantity_on_hand', '>', 0)->whereColumn('quantity_on_hand', '<=', 'reorder_point');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity_on_hand', '<=', 0);
    }

    /**
     * Next SKU for the given category, e.g. "DIA-0001", "DIA-0002", ...
     * Numbering restarts per category (not globally), so each category's
     * SKUs read as a clean, meaningful sequence of their own. For a
     * small-scale two-user store this simple read-then-compute approach is
     * fine; a high-concurrency system would want a DB-level counter/lock
     * instead to fully rule out a same-millisecond collision.
     */
    public static function generateSku(string $category): string
    {
        $prefix = self::CATEGORY_PREFIXES[$category] ?? 'OTH';

        $lastNumber = static::where('sku', 'like', $prefix . '-%')
            ->get()
            ->map(fn ($p) => (int) str_replace($prefix . '-', '', $p->sku))
            ->max() ?? 0;

        return $prefix . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
}