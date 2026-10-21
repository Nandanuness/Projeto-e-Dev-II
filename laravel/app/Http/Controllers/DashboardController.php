<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;

class DashboardController extends Controller
{
    //
    public function __invoke()
    {
        $data = [
            'products' => Product::with(['category', 'supplier'])->where('active', true)->orderBy('name')->get(),
            'recentMovements' => StockMovement::with(['product', 'user'])->latest()->limit(8)->get(),
            'stats' => [
                'products' => Product::where('active', true)->count(),
                'categories' => Category::count(),
                'suppliers' => Supplier::where('active', true)->count(),
                'lowStock' => Product::where('active', true)->whereColumn('stock', '<=', 'minimum_stock')->count(),
                'outOfStock' => Product::where('active', true)->where('stock', 0)->count(),
            ],
        ];

        return view(request()->routeIs('stock.index') ? 'stock.index' : 'dashboard', $data);
    }
}
