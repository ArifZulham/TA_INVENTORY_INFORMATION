<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;

/**
 * Service untuk komunikasi dengan OpenAI API.
 * Bertanggung jawab atas parsing intent user dan pembuatan rekomendasi persuasif.
 */
class OpenAiService
{
    /**
     * Deteksi nama produk resmi dari bahasa natural user.
     *
     * @return array{detected_product: string}
     */
    public function parseIntent(string $userPrompt): array
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.1,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => <<<'PROMPT'
Anda adalah parser intent produk untuk toko olahraga "SmartSport Assistant".

TUGAS:
- Ekstrak nama produk komersial/resmi dari permintaan user.
- Normalisasi ke nama merek + model yang umum dipakai di pasar (contoh: "Adidas X Speedportal", "Nike Mercurial Vapor 15").
- Abaikan kata sapaan, ukuran, warna, atau kata kerja. Fokus hanya pada identitas produk.

ATURAN KETAT:
- HANYA kembalikan JSON valid tanpa markdown.
- Format WAJIB: {"detected_product": "Nama Produk Resmi"}
- Jika tidak yakin, kembalikan perkiraan terbaik berdasarkan konteks olahraga.
- Jangan tambahkan field lain selain "detected_product".
PROMPT,
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt,
                ],
            ],
        ]);

        $content = $response->choices[0]->message->content ?? '{}';
        $parsed = json_decode($content, true);

        if (! is_array($parsed) || empty($parsed['detected_product'])) {
            throw new RuntimeException('OpenAI gagal mendeteksi nama produk dari prompt user.');
        }

        return [
            'detected_product' => trim((string) $parsed['detected_product']),
        ];
    }

    /**
     * Rangkai produk alternatif menjadi pesan rekomendasi ramah & persuasif.
     *
     * @param  array<int, array<string, mixed>>  $alternatives
     */
    public function generateRecommendationMessage(
        string $userPrompt,
        string $detectedProduct,
        array $alternatives,
    ): string {
        $productList = collect($alternatives)->map(function (array $product) {
            $tags = implode(', ', $product['spec_tags'] ?? []);

            return sprintf(
                '- %s (Rp %s) | Tags: %s | Stok: %d',
                $product['name'],
                number_format($product['base_price'], 0, ',', '.'),
                $tags ?: 'umum',
                $product['total_stock'] ?? 0,
            );
        })->implode("\n");

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'temperature' => 0.7,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => <<<'PROMPT'
Anda adalah sales assistant ramah di toko olahraga SmartSport.

TUGAS:
- Produk yang dicari user tidak tersedia / stok habis.
- Buat 2-3 kalimat rekomendasi alternatif yang persuasif, sopan, dan natural (Bahasa Indonesia).
- Sebutkan minimal 1-2 produk alternatif dari daftar yang diberikan beserta keunggulannya.
- Jangan mengarang produk di luar daftar.
- Tanpa bullet list, tanpa markdown, langsung kalimat siap kirim ke customer.
PROMPT,
                ],
                [
                    'role' => 'user',
                    'content' => "Permintaan user: {$userPrompt}\nProduk yang dicari: {$detectedProduct}\n\nAlternatif tersedia:\n{$productList}",
                ],
            ],
        ]);

        return trim($response->choices[0]->message->content ?? 'Maaf, produk tidak tersedia. Silakan hubungi staff kami untuk bantuan lebih lanjut.');
    }
}
