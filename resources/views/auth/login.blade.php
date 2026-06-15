<x-layout>
    <div class="min-h-[70vh] flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-xl p-8 card-hover">
                <div class="text-center mb-8">
                    <div class="text-4xl mb-2">🔐</div>
                    <h1 class="text-2xl font-bold text-gray-800">Connexion</h1>
                    <p class="text-gray-500 text-sm mt-1">Accédez à votre espace dépenses</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="input-modern w-full rounded-xl p-3 @error('email') border-red-400 @enderror"
                            placeholder="votre@email.com">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                        <input type="password" name="password" id="password"
                            class="input-modern w-full rounded-xl p-3 @error('password') border-red-400 @enderror"
                            placeholder="••••••••">
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-gradient w-full py-3 text-white font-medium rounded-xl text-sm">
                        Se connecter
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    Pas encore de compte ?
                    <a href="{{ route('register') }}" class="text-purple-600 font-medium hover:text-purple-700 transition">S'inscrire</a>
                </p>
            </div>
        </div>
    </div>
</x-layout>
