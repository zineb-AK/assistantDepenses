<x-layout>
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('recus.index') }}" class="text-accent-500 hover:text-accent-600 font-medium inline-block mb-4 tracking-wide">&larr; Retour</a>
        <h1 class="text-2xl font-bold text-accent-800 tracking-wider mb-6">Nouveau reçu</h1>

        <div class="bg-white rounded-xl shadow-sm border border-accent-100 p-6">
            <form method="POST" action="{{ route('recus.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="texte_source" class="block font-medium text-accent-700 mb-1 tracking-wide">Texte du reçu</label>
                    <textarea name="texte_source" id="texte_source" rows="10"
                        class="w-full border border-accent-200 rounded-lg p-3 focus:ring-2 focus:ring-accent-300 focus:border-accent-400 outline-none transition text-gray-700 @error('texte_source') border-red-400 @enderror"
                        placeholder="Collez le texte du reçu fournisseur ici...">{{ old('texte_source') }}</textarea>
                    @error('texte_source')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-muted mt-1">Entre 10 et 10 000 caractères.</p>
                </div>

                <button type="submit" class="w-full py-3 bg-accent-500 text-white rounded-lg hover:bg-accent-600 font-medium tracking-wide shadow-sm transition">
                    Analyser le reçu
                </button>
            </form>
        </div>
    </div>
</x-layout>
