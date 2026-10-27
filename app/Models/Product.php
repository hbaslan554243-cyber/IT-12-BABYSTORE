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
        'Baby Care' => 'BAB',
        'Bath/Hygiene' => 'BTH',
        'Feeding' => 'FEE',
        'Toys' => 'TOY',
        'Accessories' => 'ACC',
    ];

    public const CATEGORIES = [
        'Diapers', 'Milk', 'Lotion', 'Wipes', 'Baby Care', 'Bath/Hygiene', 'Feeding', 'Toys', 'Accessories',
    ];

    public const REMOVED_CATEGORIES = ['Clothing', 'Other', 'Others'];

    public const SIZE_CONTENT_CHOICES = [
        'size:NB' => 'NB',
        'size:S' => 'S',
        'size:M' => 'M',
        'size:L' => 'Large (L)',
        'content:ml' => 'ml (Milliliter)',
        'content:L' => 'L (Liter)',
        'content:g' => 'g (Gram)',
        'content:kg' => 'kg (Kilogram)',
        'content:sheets' => 'sheets',
        'content:pcs' => 'pcs',
    ];

    public const UNITS = [
        'pc', 'pack', 'box', 'bottle', 'can', 'set', 'pair',
    ];

    protected $fillable = [
        'sku',
        'name',
        'brand',
        'variant',
        'size',
        'size_content',
        'size_content_amount',
        'category',
        'unit',
        'selling_price',
        'cost_price',
        'quantity_on_hand',
        'reorder_point',
        'expiration_date',
        'archived_at',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'size_content_amount' => 'decimal:3',
        'expiration_date' => 'date',
        'archived_at' => 'datetime',
    ];

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /** Shared product label; flags select the primary and secondary product details. */
    public function productLabel(bool $includeName = true, bool $includeBrand = false): string
    {
        $parts = array_map(fn ($part) => trim((string) ($part ?? '')), [
            $includeName ? $this->name : null,
            $includeBrand ? $this->brand : null,
            $this->variant,
            $this->sizeContentLabel(),
        ]);

        return implode(' · ', array_values(array_filter($parts, fn ($part) => $part !== '')));
    }

    /** Returns M or a formatted amount and unit such as 200 ml or 0.5 L. */
    public function sizeContentLabel(): string
    {
        if (!$this->size_content) {
            return '';
        }

        if ($this->size_content_amount === null) {
            return $this->size_content;
        }

        $amount = (string) $this->size_content_amount;
        if (str_contains($amount, '.')) {
            $amount = rtrim(rtrim($amount, '0'), '.');
        }

        return $amount . ' ' . $this->size_content;
    }

    public function displayName(): string
    {
        return $this->productLabel();
    }

    public function displayDetails(): string
    {
        return $this->productLabel(false, true);
    }

    public function displayCategory(): string
    {
        return in_array($this->category, self::REMOVED_CATEGORIES, true)
            ? 'Uncategorized'
            : ($this->category ?: 'Uncategorized');
    }

    public static function categoriesForEdit(?string $existingCategory = null): array
    {
        $categories = self::CATEGORIES;
        if ($existingCategory && !in_array($existingCategory, self::REMOVED_CATEGORIES, true) && !in_array($existingCategory, $categories, true)) {
            $categories[] = $existingCategory;
        }

        return $categories;
    }

    /** Zero stock — distinct from "low but still available". */
    public function isOutOfStock(): bool
    {
        return $this->quantity_on_hand <= 0;
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
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
        $prefix = self::CATEGORY_PREFIXES[$category] ?? 'GEN';

        $lastNumber = static::where('sku', 'like', $prefix . '-%')
            ->get()
            ->map(fn ($p) => (int) str_replace($prefix . '-', '', $p->sku))
            ->max() ?? 0;

        return $prefix . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
}
