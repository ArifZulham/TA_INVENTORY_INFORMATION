<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Service pencarian produk & logika fallback rekomendasi alternatif.
 */
class ProductSearchService
{
    /**
     * Cari produk berdasarkan nama hasil parsing AI.
     * Mencoba FULLTEXT (MySQL) terlebih dahulu, fallback ke LIKE.
     */
    public function findByDetectedName(string $detectedName): ?Product
    {
        $query = Product::query()->with(['items', 'category']);

        // FULLTEXT search (MySQL) — lebih akurat untuk nama produk
        if ($this->supportsFullText()) {
            $fulltextResults = (clone $query)
                ->whereFullText('name', $detectedName)
                ->limit(5)
                ->get();

            if ($fulltextResults->isNotEmpty()) {
                return $this->pickBestMatch($fulltextResults, $detectedName);
            }
        }

        // Fallback LIKE jika FULLTEXT tidak tersedia / tidak menghasilkan hasil
        $likeResults = (clone $query)
            ->where('name', 'LIKE', '%'.$detectedName.'%')
            ->orWhere('name', 'LIKE', '%'.$this->extractMainKeyword($detectedName).'%')
            ->limit(10)
            ->get();

        return $this->pickBestMatch($likeResults, $detectedName);
    }

    /**
     * Fallback: cari produk alternatif dalam kategori yang sama dengan stok > 0.
     * Skoring berdasarkan overlap spec_tags dan kemiripan harga (±30%).
     *
     * @return Collection<int, Product>
     */
    public function findAlternatives(?Product $referenceProduct, int $limit = 3): Collection
    {
        if (! $referenceProduct) {
            return collect();
        }

        $priceMin = $referenceProduct->base_price * 0.7;
        $priceMax = $referenceProduct->base_price * 1.3;
        $referenceTags = $referenceProduct->spec_tags ?? [];

        $candidates = Product::query()
            ->with(['items', 'category'])
            ->where('category_id', $referenceProduct->category_id)
            ->where('id', '!=', $referenceProduct->id)
            ->whereBetween('base_price', [$priceMin, $priceMax])
            ->whereHas('items', fn (Builder $q) => $q->where('stock_quantity', '>', 0))
            ->get();

        return $candidates
            ->map(function (Product $product) use ($referenceTags) {
                $product->similarity_score = $this->calculateTagSimilarity(
                    $referenceTags,
                    $product->spec_tags ?? []
                );

                return $product;
            })
            ->sortByDesc('similarity_score')
            ->take($limit)
            ->values();
    }

    /**
     * Format produk untuk response JSON API.
     *
     * @return array<string, mixed>
     */
    public function formatProduct(Product $product): array
    {
        $availableItems = $product->items
            ->filter(fn ($item) => $item->stock_quantity > 0)
            ->values();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'base_price' => (float) $product->base_price,
            'spec_tags' => $product->spec_tags ?? [],
            'category' => $product->category?->only(['id', 'name', 'slug']),
            'total_stock' => $product->totalStock(),
            'available_items' => $availableItems->map(fn ($item) => [
                'id' => $item->id,
                'sku' => $item->sku,
                'size' => $item->size,
                'color' => $item->color,
                'stock_quantity' => $item->stock_quantity,
            ])->all(),
        ];
    }

    /**
     * Pilih produk dengan skor kemiripan nama tertinggi.
     */
    private function pickBestMatch(Collection $products, string $detectedName): ?Product
    {
        if ($products->isEmpty()) {
            return null;
        }

        $normalizedTarget = strtolower($detectedName);

        return $products
            ->sortByDesc(function (Product $product) use ($normalizedTarget) {
                $name = strtolower($product->name);
                similar_text($normalizedTarget, $name, $percent);

                return $percent;
            })
            ->first();
    }

    /**
     * Ambil kata kunci utama dari nama produk (kata terakhir yang signifikan).
     */
    private function extractMainKeyword(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return end($parts) ?: $name;
    }

    /**
     * Hitung kemiripan spec_tags menggunakan Jaccard similarity (0–1).
     *
     * @param  array<int, string>  $tagsA
     * @param  array<int, string>  $tagsB
     */
    private function calculateTagSimilarity(array $tagsA, array $tagsB): float
    {
        $setA = collect($tagsA)->map(fn ($t) => strtolower($t))->unique();
        $setB = collect($tagsB)->map(fn ($t) => strtolower($t))->unique();

        if ($setA->isEmpty() && $setB->isEmpty()) {
            return 0.5;
        }

        $intersection = $setA->intersect($setB)->count();
        $union = $setA->merge($setB)->unique()->count();

        return $union > 0 ? $intersection / $union : 0;
    }

    /** Cek apakah driver database mendukung FULLTEXT index. */
    private function supportsFullText(): bool
    {
        return Product::query()->getConnection()->getDriverName() === 'mysql';
    }
}
