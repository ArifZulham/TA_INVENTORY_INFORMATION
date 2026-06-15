<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Product — master produk olahraga dengan varian item (SKU).
 */
class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'base_price',
        'spec_tags',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'spec_tags' => 'array',
        ];
    }

    /** Relasi: produk → kategori */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Relasi: produk → varian/SKU */
    public function items(): HasMany
    {
        return $this->hasMany(ProductItem::class);
    }

    /**
     * Cek apakah produk masih memiliki stok tersedia (minimal 1 item > 0).
     */
    public function hasAvailableStock(): bool
    {
        return $this->items()->where('stock_quantity', '>', 0)->exists();
    }

    /**
     * Total stok seluruh varian produk.
     */
    public function totalStock(): int
    {
        return (int) $this->items()->sum('stock_quantity');
    }
}
