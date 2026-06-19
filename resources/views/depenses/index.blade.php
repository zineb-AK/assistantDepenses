<x-layout>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <h1 class="text-2xl font-bold text-accent-800 tracking-wider">Dépenses</h1>

        <form method="GET" action="{{ route('depenses.index') }}" class="flex items-center gap-2">
            <select name="categorie" class="border border-accent-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-accent-300 focus:border-accent-400 outline-none text-muted">
                <option value="">Toutes les catégories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->value }}" {{ request('categorie') === $cat->value ? 'selected' : '' }}>
                        {{ $cat->label() }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-2 bg-accent-500 text-white text-sm rounded-lg hover:bg-accent-600 font-medium tracking-wide shadow-sm transition">Filtrer</button>
        </form>
    </div>

    @if ($totals->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            @foreach ($categories as $cat)
                @php $total = $totals->get($cat->value)?->total ?? 0; @endphp
                <div class="bg-white rounded-xl shadow-sm border border-accent-100 p-4 text-center">
                    <p class="text-sm text-muted tracking-wide">{{ $cat->label() }}</p>
                    <p class="text-lg font-bold text-accent-600">{{ number_format($total, 2, ',', ' ') }} MAD</p>
                </div>
            @endforeach
        </div>
    @endif

    @if ($depenses->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-accent-100 p-12 text-center">
            <p class="text-muted text-lg">Aucune dépense trouvée.</p>
        </div>
    @else
        <div class="overflow-x-auto bg-white rounded-xl shadow-sm border border-accent-100">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-accent-100 text-left tracking-wider text-sm">
                        <th class="p-3 font-semibold text-accent-600">Libellé</th>
                        <th class="p-3 font-semibold text-accent-600">Qté</th>
                        <th class="p-3 font-semibold text-accent-600">Prix unitaire</th>
                        <th class="p-3 font-semibold text-accent-600">Total</th>
                        <th class="p-3 font-semibold text-accent-600">Catégorie</th>
                        <th class="p-3 font-semibold text-accent-600">Reçu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($depenses as $depense)
                        <tr class="border-b border-accent-50 hover:bg-accent-50/50 transition">
                            <td class="p-3 font-medium">{{ $depense->libelle }}</td>
                            <td class="p-3 text-muted">{{ $depense->quantite }}</td>
                            <td class="p-3 text-muted">{{ number_format($depense->prix_unitaire, 2, ',', ' ') }} MAD</td>
                            <td class="p-3 font-semibold text-accent-600">{{ number_format($depense->quantite * $depense->prix_unitaire, 2, ',', ' ') }} MAD</td>
                            <td class="p-3">
                                <span class="inline-block px-3 py-1 text-xs font-medium rounded-full {{ $depense->categorie->value === 'alimentaire' ? 'bg-accent-50 text-accent-700' : ($depense->categorie->value === 'boissons' ? 'bg-blue-50 text-blue-700' : ($depense->categorie->value === 'hygiene' ? 'bg-purple-50 text-purple-700' : ($depense->categorie->value === 'entretien' ? 'bg-teal-50 text-teal-700' : 'bg-gray-100 text-gray-700'))) }}">
                                    {{ $depense->categorie->label() }}
                                </span>
                            </td>
                            <td class="p-3">
                                <a href="{{ route('recus.show', $depense->recu) }}" class="text-accent-500 hover:text-accent-600 font-medium text-sm">
                                    Reçu #{{ $depense->recu_id }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layout>
