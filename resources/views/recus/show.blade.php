<x-layout>
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('recus.index') }}" class="inline-flex items-center gap-1 text-purple-600 hover:text-purple-700 font-medium mb-6 transition">
            ← Retour aux reçus
        </a>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">📄 Détail du reçu</h1>
                <p class="text-gray-500 text-sm">Reçu #{{ $recu->id }} · {{ $recu->created_at->format('d/m/Y') }}</p>
            </div>
            <span class="inline-block px-4 py-1.5 text-sm font-bold rounded-full text-white shadow-sm {{ $recu->statut->badgeClass() }}">
                {{ $recu->statut->label() }}
            </span>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 card-hover">
            <h2 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">📝 Texte source</h2>
            <div class="bg-gray-50 rounded-xl p-4">
                <pre class="whitespace-pre-wrap text-sm text-gray-700 font-sans">{{ $recu->texte_source }}</pre>
            </div>
        </div>

        @if ($recu->statut->value === 'pending')
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl flex items-center gap-3">
                <span class="text-xl">⏳</span>
                <span class="font-medium">Extraction en cours…</span>
            </div>
        @endif

        @if ($recu->statut->value === 'failed')
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3">
                <span class="text-xl">❌</span>
                <div>
                    <span class="font-medium">L'extraction a échoué.</span>
                    <a href="{{ route('recus.create') }}" class="underline ml-1">Vous pouvez soumettre à nouveau ce reçu.</a>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-lg p-6 card-hover">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">🧾 Dépenses extraites</h2>

            @if ($recu->depenses->isEmpty())
                <div class="text-center py-8">
                    <div class="text-4xl mb-3">
                        {{ $recu->statut->value === 'pending' ? '⏳' : '📭' }}
                    </div>
                    <p class="text-gray-500">
                        @if ($recu->statut->value === 'pending')
                            En cours de traitement...
                        @else
                            Aucune dépense extraite.
                        @endif
                    </p>
                </div>
            @else
                <div class="overflow-hidden rounded-xl border border-gray-100">
                    <table class="w-full table-modern">
                        <thead>
                            <tr class="text-left text-sm font-semibold uppercase tracking-wider">
                                <th class="p-3">Libellé</th>
                                <th class="p-3">Qté</th>
                                <th class="p-3">Prix unitaire</th>
                                <th class="p-3">Catégorie</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recu->depenses as $depense)
                                <tr class="border-b border-gray-100 hover:bg-purple-50/50 transition">
                                    <td class="p-3 font-medium">{{ $depense->libelle }}</td>
                                    <td class="p-3">{{ $depense->quantite }}</td>
                                    <td class="p-3">{{ number_format($depense->prix_unitaire, 2, ',', ' ') }} MAD</td>
                                    <td class="p-3">
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full {{ $depense->categorie->badgeClass() }}">
                                            {{ $depense->categorie->label() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layout>
