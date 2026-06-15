<x-layout>
    <div class="min-h-[70vh] flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-xl p-8 card-hover">
                <div class="text-center mb-8">
                    <div class="text-4xl mb-2">✨</div>
                    <h1 class="text-2xl font-bold text-gray-800">Inscription</h1>
                    <p class="text-gray-500 text-sm mt-1">Créez votre compte gratuitement</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="input-modern w-full rounded-xl p-3 @error('name') border-red-400 @enderror"
                            placeholder="Votre nom">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

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
                            placeholder="Minimum 8 caractères">
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="input-modern w-full rounded-xl p-3"
                            placeholder="Répétez le mot de passe">
                    </div>

                    <button type="submit" class="btn-gradient w-full py-3 text-white font-medium rounded-xl text-sm">
                        Créer mon compte
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    Déjà inscrit ?
                    <a href="{{ route('login') }}" class="text-purple-600 font-medium hover:text-purple-700 transition">Se connecter</a>
                </p>
            </div>
        </div>
    </div>
</x-layout>
