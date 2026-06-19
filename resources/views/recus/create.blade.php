<x-layout>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Nouveau reçu</h1>

        <form method="POST" action="{{ route('recus.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="texte_source" class="block font-medium mb-1">Texte du reçu</label>
                <textarea name="texte_source" id="texte_source" rows="10"
                    class="w-full border rounded p-3 @error('texte_source') border-red-500 @enderror"
                    placeholder="Collez le texte du reçu fournisseur ici...">{{ old('texte_source') }}</textarea>
                @error('texte_source')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Entre 10 et 10 000 caractères.</p>
            </div>

            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Analyser le reçu
            </button>
        </form>
    </div>
</x-layout>
