<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: tabel products
 * Menyimpan data master produk yang terhubung ke kategori.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_price', 12, 2);
            // spec_tags disimpan sebagai JSON: ["running", "lightweight", "indoor"]
            $table->json('spec_tags')->nullable();
            $table->timestamps();

            // Index untuk mempercepat pencarian nama produk
            $table->index('name');
            $table->fullText('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
