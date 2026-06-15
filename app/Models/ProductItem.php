<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model ProductItem — varian produk (ukuran, warna, SKU, stok).
 */
class ProductItem extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'color',
        'stock_quantity',
        'sku',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
        ];
    }

    /** Relasi: item → produk induk */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
