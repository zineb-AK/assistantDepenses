<x-layout>
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('recus.index') }}" class="text-accent-500 hover:text-accent-600 font-medium inline-block mb-4 tracking-wide">&larr; Retour aux reçus</a>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-accent-800 tracking-wider">Détail du reçu</h1>
                <p class="text-muted text-sm">Reçu #{{ $recu->id }} · {{ $recu->created_at->format('d/m/Y') }}</p>
            </div>
            <span class="px-3 py-1.5 text-sm font-medium rounded-full {{ $recu->statut->value === 'pending' ? 'bg-amber-50 text-amber-700' : ($recu->statut->value === 'processed' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700') }}">
                {{ $recu->statut->label() }}
            </span>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-accent-100 p-6 mb-6">
            <h2 class="font-semibold text-accent-700 mb-3 tracking-wide">Texte source</h2>
            <div class="bg-surface rounded-lg p-4">
                <pre class="whitespace-pre-wrap text-sm text-gray-700 font-sans">{{ $recu->texte_source }}</pre>
            </div>
        </div>

        @if ($recu->statut->value === 'pending')
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-lg flex items-center gap-3">
                <span class="font-medium tracking-wide">Extraction en cours…</span>
            </div>
        @endif

        @if ($recu->statut->value === 'failed')
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-3">
                <div>
                    <span class="font-medium tracking-wide">L'extraction a échoué.</span>
                    <a href="{{ route('recus.create') }}" class="underline ml-1">Soumettre à nouveau</a>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-accent-100 p-6">
            <h2 class="font-semibold text-accent-700 mb-4 tracking-wide">Dépenses extraites</h2>

            @if ($recu->depenses->isEmpty())
                <div class="text-center py-8">
                    <p class="text-muted">
                        @if ($recu->statut->value === 'pending')
                            En cours de traitement...
                        @else
                            Aucune dépense extraite.
                        @endif
                    </p>
                </div>
            @else
                <div class="overflow-hidden rounded-lg border border-accent-100">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-sm font-semibold uppercase tracking-widest bg-surface text-accent-600">
                                <th class="p-3">Libellé</th>
                                <th class="p-3">Qté</th>
                                <th class="p-3">Prix unitaire</th>
                                <th class="p-3">Catégorie</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recu->depenses as $depense)
                                <tr class="border-b border-accent-50 hover:bg-accent-50/50 transition">
                                    <td class="p-3 font-medium">{{ $depense->libelle }}</td>
                                    <td class="p-3 text-muted">{{ $depense->quantite }}</td>
                                    <td class="p-3 text-muted">{{ number_format($depense->prix_unitaire, 2, ',', ' ') }} MAD</td>
                                    <td class="p-3">
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full {{ $depense->categorie->value === 'alimentaire' ? 'bg-accent-50 text-accent-700' : ($depense->categorie->value === 'boissons' ? 'bg-blue-50 text-blue-700' : ($depense->categorie->value === 'hygiene' ? 'bg-purple-50 text-purple-700' : ($depense->categorie->value === 'entretien' ? 'bg-teal-50 text-teal-700' : 'bg-gray-100 text-gray-700'))) }}">
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
