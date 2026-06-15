<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Controller halaman pencarian produk (UI).
 */
class SearchController extends Controller
{
    public function index(): View
    {
        return view('search.index');
    }
}
