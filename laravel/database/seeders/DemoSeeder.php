<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('active', true)->first();

        if (! $user) {
            throw new RuntimeException('Crie o primeiro usuário gerente antes de executar o DemoSeeder.');
        }

        if (StockMovement::exists()) {
            $this->command->warn('Movimentações já existem. Dados de demonstração não foram duplicados.');
            return;
        }

        DB::transaction(function () use ($user) {
            $categories = collect([
                'Bebidas',
                'Mercearia',
                'Descartáveis',
            ])->mapWithKeys(fn (string $name) => [$name => Category::firstOrCreate(['name' => $name])]);

            $suppliers = collect([
                ['name' => 'Distribuidora Londrina', 'contact_name' => 'Marcos Silva', 'phone' => '(43) 3333-1000'],
                ['name' => 'AABB Fornecimentos', 'contact_name' => 'Juliana Costa', 'phone' => '(43) 3333-2000'],
                ['name' => 'Mercado Central', 'contact_name' => 'Rafael Souza', 'phone' => '(43) 3333-3000'],
            ])->mapWithKeys(fn (array $data) => [$data['name'] => Supplier::firstOrCreate(['name' => $data['name']], $data)]);

            $products = [
                ['name' => 'Refrigerante lata 350ml', 'category' => 'Bebidas', 'supplier' => 'Distribuidora Londrina', 'minimum_stock' => 24, 'entry' => 120, 'exit' => 36],
                ['name' => 'Água mineral 500ml', 'category' => 'Bebidas', 'supplier' => 'Distribuidora Londrina', 'minimum_stock' => 30, 'entry' => 150, 'exit' => 90],
                ['name' => 'Cerveja lata 350ml', 'category' => 'Bebidas', 'supplier' => 'Distribuidora Londrina', 'minimum_stock' => 48, 'entry' => 240, 'exit' => 188],
                ['name' => 'Café em pó 500g', 'category' => 'Mercearia', 'supplier' => 'Mercado Central', 'minimum_stock' => 5, 'entry' => 20, 'exit' => 6],
                ['name' => 'Açúcar 1kg', 'category' => 'Mercearia', 'supplier' => 'Mercado Central', 'minimum_stock' => 8, 'entry' => 30, 'exit' => 12],
                ['name' => 'Copo descartável 300ml', 'category' => 'Descartáveis', 'supplier' => 'AABB Fornecimentos', 'minimum_stock' => 100, 'entry' => 500, 'exit' => 420],
                ['name' => 'Guardanapo de papel', 'category' => 'Descartáveis', 'supplier' => 'AABB Fornecimentos', 'minimum_stock' => 50, 'entry' => 200, 'exit' => 180],
                ['name' => 'Suco de laranja 1L', 'category' => 'Bebidas', 'supplier' => 'Mercado Central', 'minimum_stock' => 12, 'entry' => 30, 'exit' => 24],
            ];

            foreach ($products as $data) {
                $product = Product::create([
                    'name' => $data['name'],
                    'category_id' => $categories[$data['category']]->id,
                    'supplier_id' => $suppliers[$data['supplier']]->id,
                    'stock' => 0,
                    'minimum_stock' => $data['minimum_stock'],
                    'active' => true,
                ]);

                $product->update(['stock' => $data['entry']]);
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'type' => 'entrada',
                    'quantity' => $data['entry'],
                    'reason' => 'Carga inicial de demonstração',
                    'notes' => 'Registro criado pelo DemoSeeder',
                ]);

                $product->update(['stock' => $data['entry'] - $data['exit']]);
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'type' => 'saida',
                    'quantity' => $data['exit'],
                    'reason' => 'Consumo operacional',
                    'notes' => 'Registro criado pelo DemoSeeder',
                ]);
            }
        });

        $this->command->info('Dados de demonstração inseridos com sucesso.');
    }
}
