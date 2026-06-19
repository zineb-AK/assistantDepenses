<x-layout>
    <div class="max-w-md mx-auto mt-10">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-accent-800 tracking-wider">Connexion</h1>
            <p class="text-muted text-sm mt-1">Connectez-vous &agrave; votre espace</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-accent-100 p-6">
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block font-medium text-accent-700 mb-1 tracking-wide">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="w-full border border-accent-200 rounded-lg p-2.5 focus:ring-2 focus:ring-accent-300 focus:border-accent-400 outline-none transition @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block font-medium text-accent-700 mb-1 tracking-wide">Mot de passe</label>
                    <input type="password" name="password" id="password"
                        class="w-full border border-accent-200 rounded-lg p-2.5 focus:ring-2 focus:ring-accent-300 focus:border-accent-400 outline-none transition @error('password') border-red-400 @enderror">
                    @error('password')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-2.5 bg-accent-500 text-white rounded-lg hover:bg-accent-600 font-medium tracking-wide shadow-sm transition">
                    Se connecter
                </button>
            </form>
        </div>

        <p class="mt-4 text-sm text-muted text-center">
            Pas encore de compte ? <a href="{{ route('register') }}" class="text-accent-500 hover:text-accent-600 font-medium underline">S'inscrire</a>
        </p>
    </div>
</x-layout>
