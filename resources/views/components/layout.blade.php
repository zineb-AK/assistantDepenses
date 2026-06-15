<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant Dépenses</title>
    @if (!app()->runningUnitTests())
        @vite('resources/css/app.css')
    @endif
    <style>
        .gradient-nav { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-hero { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .gradient-card { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
        .badge-pending { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); }
        .badge-processed { background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); }
        .badge-failed { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .card-hover { transition: transform 0.2s, box-shadow 0.2s; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 12px 25px -8px rgba(0,0,0,0.15); }
        .btn-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); transition: all 0.3s; }
        .btn-gradient:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4); }
        .btn-danger { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); transition: all 0.3s; }
        .btn-danger:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4); }
        .input-modern { transition: all 0.3s; border: 2px solid #e2e8f0; }
        .input-modern:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15); outline: none; }
        .table-modern th { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .table-modern tr { transition: background 0.2s; }
        .table-modern tr:hover { background: #f8f7ff; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen">
    <nav class="gradient-nav shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-6">
                    <a href="{{ route('recus.index') }}" class="text-white text-xl font-bold tracking-tight">
                        📋 Assistant Dépenses
                    </a>
                    @auth
                        <a href="{{ route('recus.index') }}" class="text-white/80 hover:text-white transition text-sm font-medium">Mes reçus</a>
                        <a href="{{ route('depenses.index') }}" class="text-white/80 hover:text-white transition text-sm font-medium">Dépenses</a>
                    @endauth
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <span class="text-white/70 text-sm">👤 {{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="px-4 py-1.5 bg-white/15 text-white text-sm rounded-lg hover:bg-white/25 transition">
                                Déconnexion
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-white/80 hover:text-white transition text-sm">Connexion</a>
                        <a href="{{ route('register') }}" class="px-4 py-1.5 bg-white text-purple-700 text-sm font-medium rounded-lg hover:bg-white/90 transition shadow">
                            Inscription
                        </a>
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
