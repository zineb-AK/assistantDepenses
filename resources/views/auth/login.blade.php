<x-layout>
    <div class="max-w-md mx-auto mt-10">
        <h1 class="text-2xl font-bold mb-6">Connexion</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block font-medium mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                    class="w-full border rounded p-2 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block font-medium mb-1">Mot de passe</label>
                <input type="password" name="password" id="password"
                    class="w-full border rounded p-2 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Se connecter
            </button>
        </form>

        <p class="mt-4 text-sm text-gray-600">
            Pas encore de compte ? <a href="{{ route('register') }}" class="text-blue-600 hover:underline">S'inscrire</a>
        </p>
    </div>
</x-layout>
