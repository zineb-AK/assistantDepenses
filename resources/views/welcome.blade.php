<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Assistant Dépenses</title>
        @fonts
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-surface text-gray-900 antialiased tracking-wide flex items-center justify-center min-h-screen flex-col p-6">
        <div class="w-full max-w-lg text-center">
            <h1 class="text-4xl font-bold tracking-wider text-accent-700 mb-2">Assistant Dépenses</h1>
            <p class="text-muted mb-8">Gestion de reçus et dépenses</p>

            <div class="bg-white rounded-xl shadow-sm border border-accent-100 p-8">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('recus.index') }}" class="inline-block px-6 py-3 bg-accent-500 text-white rounded-lg hover:bg-accent-600 font-medium tracking-wide shadow-sm transition text-lg">
                            Accéder à mes reçus
                        </a>
                    @else
                        <div class="flex flex-col gap-3">
                            <a href="{{ route('login') }}" class="inline-block px-6 py-3 bg-accent-500 text-white rounded-lg hover:bg-accent-600 font-medium tracking-wide shadow-sm transition">
                                Se connecter
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-block px-6 py-3 border-2 border-accent-300 text-accent-600 rounded-lg hover:bg-accent-50 font-medium tracking-wide transition">
                                    Créer un compte
                                </a>
                            @endif
                        </div>
                    @endauth
                @endif
            </div>

            <p class="mt-8 text-sm text-muted">v{{ app()->version() }}</p>
        </div>
    </body>
</html>
