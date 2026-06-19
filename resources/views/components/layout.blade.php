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
<body class="bg-gray-50 text-gray-900 antialiased">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-4">
                    <a href="{{ route('recus.index') }}" class="text-lg font-semibold">Assistant Dépenses</a>
                    @auth
                        <a href="{{ route('recus.index') }}" class="text-sm text-gray-600 hover:underline">Mes reçus</a>
                        <a href="{{ route('depenses.index') }}" class="text-sm text-gray-600 hover:underline">Dépenses</a>
                    @endauth
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="text-sm text-red-600 hover:underline">Déconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:underline">Connexion</a>
                        <a href="{{ route('register') }}" class="text-sm text-blue-600 hover:underline">Inscription</a>
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
