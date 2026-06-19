<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant Dépenses</title>
    @if (!app()->runningUnitTests())
        @vite('resources/css/app.css')
    @endif
</head>
<body class="bg-surface text-gray-900 antialiased tracking-wide">
    <nav class="bg-white border-b border-accent-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-14 items-center">
                <div class="flex items-center gap-6">
                    <a href="{{ route('recus.index') }}" class="text-lg font-semibold text-accent-700 tracking-wider">
                        Assistant Dépenses
                    </a>
                    @auth
                        <a href="{{ route('recus.index') }}" class="text-sm text-muted hover:text-accent-600 transition">Mes reçus</a>
                        <a href="{{ route('depenses.index') }}" class="text-sm text-muted hover:text-accent-600 transition">Dépenses</a>
                    @endauth
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <span class="text-sm text-muted">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="text-sm text-muted hover:text-accent-600 transition">Déconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-muted hover:text-accent-600 transition">Connexion</a>
                        <a href="{{ route('register') }}" class="text-sm bg-accent-500 text-white px-3 py-1.5 rounded hover:bg-accent-600 transition font-medium">Inscription</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-flash-message />
        {{ $slot }}
    </main>
</body>
</html>
