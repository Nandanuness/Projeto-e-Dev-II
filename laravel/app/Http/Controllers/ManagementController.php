<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManagementController extends Controller
{
    public function categories()
    {
        $categories = Category::withCount('products')->when(request('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))->when(request('status') === 'active', fn ($query) => $query->where('active', true))->when(request('status') === 'inactive', fn ($query) => $query->where('active', false))->orderBy('name')->get();
        return view('management.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120', 'unique:categories,name']]);
        Category::create($data);
        return back()->with('success', 'Categoria cadastrada.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120', 'unique:categories,name,'.$category->id]]);
        $category->update($data);
        return back()->with('success', 'Categoria atualizada.');
    }

    public function destroyCategory(Category $category)
    {
        abort_if($category->products()->exists(), 422, 'Não é possível excluir uma categoria vinculada a produtos.');
        $category->delete();
        return back()->with('success', 'Categoria excluída.');
    }

    public function toggleCategory(Category $category)
    {
        $category->update(['active' => ! $category->active]);
        return back()->with('success', 'Situação da categoria atualizada.');
    }

    public function suppliers()
    {
        $suppliers = Supplier::withCount('products')->when(request('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))->when(request('status') === 'active', fn ($query) => $query->where('active', true))->when(request('status') === 'inactive', fn ($query) => $query->where('active', false))->orderBy('name')->get();
        return view('management.suppliers', compact('suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        Supplier::create($request->validate(['name' => ['required', 'string', 'max:160'], 'contact_name' => ['nullable', 'string', 'max:120'], 'phone' => ['nullable', 'string', 'max:30'], 'notes' => ['nullable', 'string', 'max:1000']]));
        return back()->with('success', 'Fornecedor cadastrado.');
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $supplier->update($request->validate(['name' => ['required', 'string', 'max:160'], 'contact_name' => ['nullable', 'string', 'max:120'], 'phone' => ['nullable', 'string', 'max:30'], 'notes' => ['nullable', 'string', 'max:1000'], 'active' => ['sometimes', 'boolean']]));
        return back()->with('success', 'Fornecedor atualizado.');
    }

    public function destroySupplier(Supplier $supplier)
    {
        abort_if($supplier->products()->exists(), 422, 'Desative o fornecedor enquanto houver produtos vinculados.');
        $supplier->delete();
        return back()->with('success', 'Fornecedor excluído.');
    }

    public function toggleSupplier(Supplier $supplier)
    {
        $supplier->update(['active' => ! $supplier->active]);
        return back()->with('success', 'Situação do fornecedor atualizada.');
    }

    public function products()
    {
        $products = Product::with(['category', 'supplier'])->when(request('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))->when(request('category_id'), fn ($query, $id) => $query->where('category_id', $id))->when(request('supplier_id'), fn ($query, $id) => $query->where('supplier_id', $id))->when(request('status') === 'active', fn ($query) => $query->where('active', true))->when(request('status') === 'inactive', fn ($query) => $query->where('active', false))->when(request('stock_status') === 'low', fn ($query) => $query->whereColumn('stock', '<=', 'minimum_stock'))->when(request('stock_status') === 'empty', fn ($query) => $query->where('stock', 0))->orderBy('name')->paginate(12)->withQueryString();
        return view('management.products', ['products' => $products, 'categories' => Category::where('active', true)->orderBy('name')->get(), 'suppliers' => Supplier::where('active', true)->orderBy('name')->get()]);
    }

    public function storeProduct(Request $request)
    {
        Product::create($request->validate(['name' => ['required', 'string', 'max:160'], 'category_id' => ['required', 'exists:categories,id'], 'supplier_id' => ['nullable', 'exists:suppliers,id'], 'minimum_stock' => ['required', 'integer', 'min:0']]));
        return back()->with('success', 'Produto cadastrado.');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $product->update($request->validate(['name' => ['required', 'string', 'max:160'], 'category_id' => ['required', 'exists:categories,id'], 'supplier_id' => ['nullable', 'exists:suppliers,id'], 'minimum_stock' => ['required', 'integer', 'min:0'], 'active' => ['sometimes', 'boolean']]));
        return back()->with('success', 'Produto atualizado.');
    }

    public function destroyProduct(Product $product)
    {
        abort_if($product->movements()->exists(), 422, 'Produtos com histórico devem ser desativados, não excluídos.');
        $product->delete();
        return back()->with('success', 'Produto excluído.');
    }

    public function toggleProduct(Product $product)
    {
        $product->update(['active' => ! $product->active]);
        return back()->with('success', 'Situação do produto atualizada.');
    }

    public function users()
    {
        $users = User::when(request('search'), fn ($query, $search) => $query->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))->when(request('role'), fn ($query, $role) => $query->where('role', $role))->when(request('status') === 'active', fn ($query) => $query->where('active', true))->when(request('status') === 'inactive', fn ($query) => $query->where('active', false))->orderBy('name')->get();
        return view('management.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'unique:users,email'], 'password' => ['required', 'string', 'min:8'], 'role' => ['required', 'in:gerente,funcionario']]);
        $data['password'] = Hash::make($data['password']);
        User::create($data + ['active' => true]);
        return back()->with('success', 'Usuário cadastrado.');
    }

    public function toggleUser(User $user)
    {
        abort_if($user->is(auth()->user()), 422, 'Você não pode desativar o próprio usuário.');
        $user->update(['active' => ! $user->active]);
        return back()->with('success', 'Situação do usuário atualizada.');
    }

    public function updateUser(Request $request, User $user)
    {
        abort_if($user->is(auth()->user()) && $request->role !== $user->role, 422, 'Você não pode alterar o próprio perfil.');
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'unique:users,email,'.$user->id], 'role' => ['required', 'in:gerente,funcionario'], 'password' => ['nullable', 'string', 'min:8']]);
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);
        return back()->with('success', 'Usuário atualizado.');
    }

    public function movements(Request $request)
    {
        $movements = StockMovement::with(['product', 'user'])->when($request->type, fn ($q, $type) => $q->where('type', $type))->when($request->product_id, fn ($q, $id) => $q->where('product_id', $id))->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))->when($request->from, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))->when($request->to, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))->latest()->paginate(20)->withQueryString();
        return view('management.movements', ['movements' => $movements, 'products' => Product::orderBy('name')->get(), 'users' => User::orderBy('name')->get()]);
    }
}
