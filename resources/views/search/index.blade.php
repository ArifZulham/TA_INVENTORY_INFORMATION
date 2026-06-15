@extends('layouts.app')

@section('title', 'Pencarian Produk — SmartSport Assistant')

@section('content')
{{-- Hero & Search Form --}}
<section class="mb-8">
    <div class="mb-6">
        <span class="inline-flex items-center rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-400">
            Powered by OpenAI
        </span>
        <h1 class="mt-4 text-2xl font-bold text-white sm:text-4xl">
            Cari Produk Olahraga dengan Bahasa Natural
        </h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-400 sm:text-base">
            Ketik seperti berbicara ke sales: <em>"Ada sepatu Adidas buat ngebut di lapangan?"</em>
            — AI akan mendeteksi produk dan mengecek stok secara otomatis.
        </p>
    </div>

    <form id="search-form" class="flex flex-col gap-3 sm:flex-row">
        <input
            type="text"
            id="search-prompt"
            name="prompt"
            required
            minlength="3"
            maxlength="500"
            placeholder='Contoh: "Saya cari Nike Mercurial ukuran 42"'
            class="flex-1 rounded-xl border border-slate-700 bg-slate-900 px-4 py-3.5 text-sm text-white placeholder-slate-500 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
        >
        <button
            type="submit"
            id="search-btn"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-6 py-3.5 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-60"
        >
            <span id="search-btn-text">Cari Produk</span>
            <svg id="search-spinner" class="hidden h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </button>
    </form>

    {{-- Quick prompts --}}
    <div class="mt-4 flex flex-wrap gap-2">
        @foreach ([
            'Adidas X Speedportal FG',
            'Nike Mercurial Vapor 15',
            'Sepatu lari Nike Pegasus',
        ] as $example)
            <button
                type="button"
                class="quick-prompt rounded-full border border-slate-700 px-3 py-1 text-xs text-slate-400 transition hover:border-emerald-500/50 hover:text-emerald-400"
                data-prompt="{{ $example }}"
            >
                {{ $example }}
            </button>
        @endforeach
    </div>
</section>

{{-- Error Alert --}}
<div id="error-alert" class="mb-6 hidden rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300"></div>

{{-- Results Container --}}
<div id="results" class="hidden space-y-6">
    {{-- Detected product badge --}}
    <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="text-slate-400">Produk terdeteksi AI:</span>
        <span id="detected-product" class="rounded-lg bg-slate-800 px-3 py-1 font-medium text-emerald-400"></span>
        <span id="status-badge" class="rounded-lg px-3 py-1 text-xs font-semibold"></span>
    </div>

    {{-- AI Recommendation message (fallback) --}}
    <div id="recommendation-box" class="hidden rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5">
        <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-amber-400">Rekomendasi AI</p>
        <p id="recommendation-message" class="text-sm leading-relaxed text-amber-100 sm:text-base"></p>
    </div>

    {{-- Product card(s) --}}
    <div id="product-cards" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"></div>
</div>

{{-- Empty state --}}
<div id="empty-state" class="rounded-2xl border border-dashed border-slate-700 bg-slate-900/50 p-10 text-center">
    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-800 text-2xl">🔍</div>
    <p class="text-sm text-slate-400">Mulai ketik permintaan Anda di atas untuk mencari produk</p>
</div>
@endsection
