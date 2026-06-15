<?php

namespace App\Http\Controllers;

use App\Services\OpenAiService;
use App\Services\ProductSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller utama pencarian produk berbasis AI.
 */
class ProductController extends Controller
{
    public function __construct(
        private readonly OpenAiService $openAiService,
        private readonly ProductSearchService $productSearchService,
    ) {}

    /**
     * Endpoint pencarian produk natural language.
     *
     * Alur:
     * 1. Parse intent via OpenAI → nama produk resmi
     * 2. Query database + eager load items
     * 3. KONDISI A: produk & stok ada → kembalikan detail
     * 4. KONDISI B: kosong → fallback alternatif + pesan AI persuasif
     */
    public function searchProduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $userPrompt = $validated['prompt'];

        // Langkah 1: Deteksi nama produk komersial dari bahasa natural
        $intent = $this->openAiService->parseIntent($userPrompt);
        $detectedProduct = $intent['detected_product'];

        // Langkah 2: Cari produk di database (dengan relasi items & category)
        $product = $this->productSearchService->findByDetectedName($detectedProduct);

        // KONDISI A: Produk ditemukan DAN masih ada stok
        if ($product && $product->hasAvailableStock()) {
            return response()->json([
                'status' => 'found',
                'message' => 'Produk ditemukan dan tersedia.',
                'detected_product' => $detectedProduct,
                'user_prompt' => $userPrompt,
                'product' => $this->productSearchService->formatProduct($product),
            ]);
        }

        // KONDISI B: Produk tidak ada ATAU stok habis → trigger fallback
        $alternatives = $this->productSearchService->findAlternatives($product, 3);

        // Jika produk tidak ditemukan sama sekali, cari referensi kategori dari nama terdekat
        if ($alternatives->isEmpty() && ! $product) {
            $fallbackReference = $this->productSearchService->findByDetectedName(
                $this->extractBrand($detectedProduct)
            );
            $alternatives = $this->productSearchService->findAlternatives($fallbackReference, 3);
        }

        $formattedAlternatives = $alternatives
            ->map(fn ($alt) => $this->productSearchService->formatProduct($alt))
            ->all();

        // Kirim alternatif ke OpenAI untuk dirangkai menjadi kalimat rekomendasi
        $recommendationMessage = $formattedAlternatives
            ? $this->openAiService->generateRecommendationMessage(
                $userPrompt,
                $detectedProduct,
                $formattedAlternatives,
            )
            : 'Maaf, produk yang Anda cari belum tersedia dan kami belum menemukan alternatif serupa. Silakan coba kata kunci lain atau hubungi staff kami.';

        return response()->json([
            'status' => 'fallback',
            'message' => $recommendationMessage,
            'detected_product' => $detectedProduct,
            'user_prompt' => $userPrompt,
            'reason' => $product ? 'out_of_stock' : 'not_found',
            'original_product' => $product
                ? $this->productSearchService->formatProduct($product)
                : null,
            'alternatives' => $formattedAlternatives,
        ]);
    }

    /**
     * Ekstrak merek dari nama produk untuk pencarian fallback.
     */
    private function extractBrand(string $detectedProduct): string
    {
        $brands = ['Adidas', 'Nike', 'Puma', 'New Balance', 'Asics', 'Mizuno', 'Under Armour'];

        foreach ($brands as $brand) {
            if (stripos($detectedProduct, $brand) !== false) {
                return $brand;
            }
        }

        $parts = preg_split('/\s+/', trim($detectedProduct)) ?: [];

        return $parts[0] ?? $detectedProduct;
    }
}
