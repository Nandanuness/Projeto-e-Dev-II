<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Entrar | ClubEstoque</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="login-page">
    <main class="login-card">
        <div class="brand"><span class="brand-mark">CE</span><span>ClubEstoque</span></div>
        <p class="eyebrow">AABB LONDRINA</p>
        <h1>Controle de estoque, sem ruído.</h1>
        <p class="muted">Entre com seu usuário para acompanhar a operação do bar.</p>
        <form method="POST" action="{{ route('login.store') }}" class="stack-form">
            @csrf
            <label>E-mail<input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
            <label>Senha<input type="password" name="password" required></label>
            <label class="check"><input type="checkbox" name="remember"> Lembrar acesso</label>
            @if($errors->any())<div class="field-error">{{ $errors->first() }}</div>@endif
            @error('email')<div class="field-error">{{ $message }}</div>@enderror
            <button class="primary" type="submit">Entrar no painel</button>
        </form>
    </main>
</body>
</html>
