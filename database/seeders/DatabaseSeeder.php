<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductItem;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder data awal SmartSport Assistant.
 * Mengisi kategori, produk olahraga, dan varian SKU untuk demo.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User demo untuk login
        User::factory()->create([
            'name' => 'SmartSport Admin',
            'email' => 'admin@smartsport.test',
            'password' => 'password',
        ]);

        $categories = [
            ['name' => 'Sepatu Sepak Bola', 'slug' => 'sepatu-sepak-bola'],
            ['name' => 'Sepatu Lari', 'slug' => 'sepatu-lari'],
            ['name' => 'Apparel Olahraga', 'slug' => 'apparel-olahraga'],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        $football = Category::where('slug', 'sepatu-sepak-bola')->first();
        $running = Category::where('slug', 'sepatu-lari')->first();
        $apparel = Category::where('slug', 'apparel-olahraga')->first();

        // --- Produk Sepak Bola ---
        $adidasSpeedportal = Product::create([
            'category_id' => $football->id,
            'name' => 'Adidas X Speedportal.1 FG',
            'description' => 'Sepatu sepak bola elite dengan upper Speedskin dan sol FG untuk akselerasi maksimal.',
            'base_price' => 2899000,
            'spec_tags' => ['football', 'fg', 'speed', 'lightweight', 'elite'],
        ]);

        // Stok habis — untuk trigger fallback
        ProductItem::create([
            'product_id' => $adidasSpeedportal->id,
            'size' => '42',
            'color' => 'Solar Red',
            'stock_quantity' => 0,
            'sku' => 'ADI-XSP-42-RED',
        ]);

        $nikeMercurial = Product::create([
            'category_id' => $football->id,
            'name' => 'Nike Mercurial Vapor 15 Elite FG',
            'description' => 'Sepatu sepak bola ringan dengan teknologi Aerotrak untuk kecepatan tinggi.',
            'base_price' => 3199000,
            'spec_tags' => ['football', 'fg', 'speed', 'elite', 'mercurial'],
        ]);

        foreach (['40', '42', '43'] as $size) {
            ProductItem::create([
                'product_id' => $nikeMercurial->id,
                'size' => $size,
                'color' => 'Volt',
                'stock_quantity' => rand(2, 8),
                'sku' => 'NKE-MCV15-'.$size.'-VLT',
            ]);
        }

        $pumaFuture = Product::create([
            'category_id' => $football->id,
            'name' => 'Puma Future 7 Ultimate FG/AG',
            'description' => 'Sepatu fleksibel dengan FUZIONFIT360 untuk kontrol bola optimal.',
            'base_price' => 2599000,
            'spec_tags' => ['football', 'fg', 'ag', 'control', 'agility'],
        ]);

        ProductItem::create([
            'product_id' => $pumaFuture->id,
            'size' => '41',
            'color' => 'Fizzy Yellow',
            'stock_quantity' => 5,
            'sku' => 'PMA-FUT7-41-YLW',
        ]);

        // --- Produk Lari ---
        $nikePegasus = Product::create([
            'category_id' => $running->id,
            'name' => 'Nike Air Zoom Pegasus 41',
            'description' => 'Sepatu lari daily trainer dengan cushioning Zoom Air responsif.',
            'base_price' => 1899000,
            'spec_tags' => ['running', 'daily', 'cushion', 'road'],
        ]);

        foreach (['40', '41', '42', '44'] as $size) {
            ProductItem::create([
                'product_id' => $nikePegasus->id,
                'size' => $size,
                'color' => 'Black/White',
                'stock_quantity' => rand(3, 10),
                'sku' => 'NKE-PEG41-'.$size.'-BW',
            ]);
        }

        $adidasBoston = Product::create([
            'category_id' => $running->id,
            'name' => 'Adidas Adizero Boston 12',
            'description' => 'Sepatu lari tempo dengan plate ENERGYRODS untuk lari cepat.',
            'base_price' => 2199000,
            'spec_tags' => ['running', 'tempo', 'racing', 'lightweight'],
        ]);

        ProductItem::create([
            'product_id' => $adidasBoston->id,
            'size' => '42',
            'color' => 'Core Black',
            'stock_quantity' => 4,
            'sku' => 'ADI-BOS12-42-BLK',
        ]);

        // --- Apparel ---
        $nikeDriFit = Product::create([
            'category_id' => $apparel->id,
            'name' => 'Nike Dri-FIT Academy Training Jersey',
            'description' => 'Jersey latihan breathable dengan teknologi Dri-FIT.',
            'base_price' => 449000,
            'spec_tags' => ['apparel', 'training', 'jersey', 'breathable'],
        ]);

        foreach (['S', 'M', 'L', 'XL'] as $size) {
            ProductItem::create([
                'product_id' => $nikeDriFit->id,
                'size' => $size,
                'color' => 'Royal Blue',
                'stock_quantity' => rand(5, 15),
                'sku' => 'NKE-DFT-'.$size.'-BLU',
            ]);
        }
    }
}
