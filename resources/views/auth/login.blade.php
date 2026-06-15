@extends('layouts.app')

@section('title', 'Login — SmartSport Assistant')

@section('content')
<div class="mx-auto max-w-md">
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-white sm:text-3xl">Selamat Datang</h1>
        <p class="mt-2 text-sm text-slate-400">Masuk untuk menggunakan pencarian produk berbasis AI</p>
    </div>

    <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl sm:p-8">
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-300">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                    placeholder="admin@smartsport.test"
                >
                @error('email')
                    <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-300">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                    placeholder="••••••••"
                >
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-400">
                <input type="checkbox" name="remember" class="rounded border-slate-600 bg-slate-950 text-emerald-500 focus:ring-emerald-500/30">
                Ingat saya
            </label>

            <button type="submit" class="w-full rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">
                Masuk
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-400">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-medium text-emerald-400 hover:text-emerald-300">Daftar sekarang</a>
        </p>
    </div>

    <p class="mt-4 text-center text-xs text-slate-500">
        Demo: admin@smartsport.test / password
    </p>
</div>
@endsection
