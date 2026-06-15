<x-layout>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="text-4xl mb-2">📝</div>
                <h1 class="text-2xl font-bold text-gray-800">Nouveau reçu</h1>
                <p class="text-gray-500 text-sm mt-1">Collez le texte de votre reçu fournisseur</p>
            </div>

            <form method="POST" action="{{ route('recus.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="texte_source" class="block text-sm font-medium text-gray-700 mb-2">Texte du reçu</label>
                    <textarea name="texte_source" id="texte_source" rows="10"
                        class="input-modern w-full rounded-xl p-4 @error('texte_source') border-red-400 @enderror"
                        placeholder="Collez le texte du reçu fournisseur ici...&#10;&#10;Ex:&#10;10kg Farine - 50 MAD&#10;5L Huile - 60 MAD&#10;3 Savons - 25.50 MAD">{{ old('texte_source') }}</textarea>
                    @error('texte_source')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-2">Entre 10 et 10 000 caractères.</p>
                </div>

                <button type="submit" class="btn-gradient w-full py-3 text-white font-medium rounded-xl text-sm shadow-lg inline-flex items-center justify-center gap-2">
                    🤖 Analyser le reçu
                </button>
            </form>
        </div>
    </div>
</x-layout>
