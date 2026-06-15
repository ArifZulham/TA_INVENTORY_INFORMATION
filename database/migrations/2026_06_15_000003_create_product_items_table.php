<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: tabel product_items
 * Menyimpan varian SKU per produk (ukuran, warna, stok).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->string('sku')->unique();
            $table->timestamps();

            $table->index(['product_id', 'stock_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_items');
    }
};
