<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Category — satu kategori memiliki banyak produk.
 */
class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    /** Relasi: kategori → produk */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
