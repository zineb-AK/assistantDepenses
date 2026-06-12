<x-layout>
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('recus.index') }}" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Retour</a>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Détail du reçu</h1>
            <span class="px-3 py-1 text-sm rounded {{ $recu->statut->label() === 'En attente' ? 'bg-yellow-100 text-yellow-800' : ($recu->statut->label() === 'Traité' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                {{ $recu->statut->label() }}
            </span>
        </div>

        <div class="bg-white shadow rounded p-4 mb-6">
            <h2 class="font-semibold mb-2">Texte source</h2>
            <pre class="whitespace-pre-wrap text-sm">{{ $recu->texte_source }}</pre>
        </div>

        <div class="bg-white shadow rounded p-4">
            <h2 class="font-semibold mb-4">Dépenses extraites</h2>

            @if ($recu->depenses->isEmpty())
                <p class="text-gray-500">
                    @if ($recu->statut->value === 'pending')
                        En cours de traitement...
                    @else
                        Aucune dépense extraite.
                    @endif
                </p>
            @else
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left">
                            <th class="p-2 font-semibold">Libellé</th>
                            <th class="p-2 font-semibold">Qté</th>
                            <th class="p-2 font-semibold">Prix unitaire</th>
                            <th class="p-2 font-semibold">Catégorie</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recu->depenses as $depense)
                            <tr class="border-b">
                                <td class="p-2">{{ $depense->libelle }}</td>
                                <td class="p-2">{{ $depense->quantite }}</td>
                                <td class="p-2">{{ number_format($depense->prix_unitaire, 2, ',', ' ') }} MAD</td>
                                <td class="p-2">{{ $depense->categorie->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-layout>
