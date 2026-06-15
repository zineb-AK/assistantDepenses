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
            <span class="inline-block px-4 py-1.5 text-sm font-bold rounded-full text-white shadow-sm
                {{ $recu->statut->value === 'pending' ? 'badge-pending' : ($recu->statut->value === 'processed' ? 'badge-processed' : 'badge-failed') }}">
                {{ $recu->statut->label() }}
            </span>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 card-hover">
            <h2 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">📝 Texte source</h2>
            <div class="bg-gray-50 rounded-xl p-4">
                <pre class="whitespace-pre-wrap text-sm text-gray-700 font-sans">{{ $recu->texte_source }}</pre>
            </div>
        </div>

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
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                            {{ $depense->categorie->value === 'alimentaire' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $depense->categorie->value === 'boissons' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $depense->categorie->value === 'hygiene' ? 'bg-pink-100 text-pink-700' : '' }}
                                            {{ $depense->categorie->value === 'entretien' ? 'bg-orange-100 text-orange-700' : '' }}
                                            {{ $depense->categorie->value === 'autre' ? 'bg-gray-100 text-gray-700' : '' }}">
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
