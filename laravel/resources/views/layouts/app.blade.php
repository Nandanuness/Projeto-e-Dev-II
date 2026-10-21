<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'ClubEstoque' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand"><span class="brand-mark">CE</span><span>ClubEstoque</span></div>
            <nav>
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Painel</a>
                <a class="{{ request()->routeIs('stock.*') ? 'active' : '' }}" href="{{ route('stock.index') }}">Estoque</a>
                <a class="{{ request()->routeIs('movements.*') ? 'active' : '' }}" href="{{ route('movements.index') }}">Histórico</a>
                @if(auth()->user()->isManager())
                    <a class="{{ request()->routeIs('admin.products*') ? 'active' : '' }}" href="{{ route('admin.products') }}">Produtos</a>
                    <a class="{{ request()->routeIs('admin.categories*') ? 'active' : '' }}" href="{{ route('admin.categories') }}">Categorias</a>
                    <a class="{{ request()->routeIs('admin.suppliers*') ? 'active' : '' }}" href="{{ route('admin.suppliers') }}">Fornecedores</a>
                    <a class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users') }}">Usuários</a>
                    <a class="{{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">Relatórios</a>
                @endif
            </nav>
            <div class="sidebar-foot">AABB Londrina<br><small>Controle interno</small></div>
        </aside>
        <main class="content">
            <header class="topbar">
                <div><span class="eyebrow">OPERAÇÃO DIÁRIA</span><h1>{{ $heading ?? 'Visão geral' }}</h1></div>
                <div class="account"><span>{{ auth()->user()->name }}</span><small>{{ ucfirst(auth()->user()->role) }}</small><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="link-button">Sair</button></form></div>
            </header>
            @if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
</body>
</html>
