<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    //
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:entrada,saida'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $product = Product::whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
            if ($data['type'] === 'saida' && $product->stock < $data['quantity']) {
                abort(422, 'A quantidade solicitada é maior que o estoque disponível.');
            }

            $delta = $data['type'] === 'entrada' ? $data['quantity'] : -$data['quantity'];
            $product->update(['stock' => $product->stock + $delta]);
            StockMovement::create($data + ['user_id' => $request->user()->id]);
        });

        return back()->with('success', 'Movimentação registrada com sucesso.');
    }
}
