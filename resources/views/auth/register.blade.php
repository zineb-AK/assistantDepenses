<x-layout>
    <div class="max-w-md mx-auto mt-10">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-accent-800 tracking-wider">Inscription</h1>
            <p class="text-muted text-sm mt-1">Cr&eacute;ez votre compte</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-accent-100 p-6">
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block font-medium text-accent-700 mb-1 tracking-wide">Nom</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="w-full border border-accent-200 rounded-lg p-2.5 focus:ring-2 focus:ring-accent-300 focus:border-accent-400 outline-none transition @error('name') border-red-400 @enderror">
                    @error('name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

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

                <div>
                    <label for="password_confirmation" class="block font-medium text-accent-700 mb-1 tracking-wide">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="w-full border border-accent-200 rounded-lg p-2.5 focus:ring-2 focus:ring-accent-300 focus:border-accent-400 outline-none transition">
                </div>

                <button type="submit" class="w-full py-2.5 bg-accent-500 text-white rounded-lg hover:bg-accent-600 font-medium tracking-wide shadow-sm transition">
                    S'inscrire
                </button>
            </form>
        </div>

        <p class="mt-4 text-sm text-muted text-center">
            D&eacute;j&agrave; inscrit ? <a href="{{ route('login') }}" class="text-accent-500 hover:text-accent-600 font-medium underline">Se connecter</a>
        </p>
    </div>
</x-layout>
