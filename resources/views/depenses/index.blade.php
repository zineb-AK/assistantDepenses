<x-layout>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">💰 Dépenses</h1>
            <p class="text-gray-500 text-sm">Toutes vos dépenses classées par catégorie</p>
        </div>
        <form method="GET" action="{{ route('depenses.index') }}" class="flex items-center gap-2">
            <select name="categorie" class="input-modern rounded-xl p-2.5 text-sm pr-8">
                <option value="">Toutes les catégories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->value }}" {{ request('categorie') === $cat->value ? 'selected' : '' }}>
                        {{ $cat->label() }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn-gradient px-4 py-2.5 text-white text-sm font-medium rounded-xl shadow">
                Filtrer
            </button>
        </form>
    </div>

    @if ($totals->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            @foreach ($categories as $cat)
                @php $total = $totals->get($cat->value)?->total ?? 0; @endphp
                <div class="rounded-2xl p-5 card-hover shadow-lg text-white
                    {{ $cat->value === 'alimentaire' ? 'bg-gradient-to-br from-green-400 to-emerald-600' : '' }}
                    {{ $cat->value === 'boissons' ? 'bg-gradient-to-br from-blue-400 to-blue-600' : '' }}
                    {{ $cat->value === 'hygiene' ? 'bg-gradient-to-br from-pink-400 to-rose-600' : '' }}
                    {{ $cat->value === 'entretien' ? 'bg-gradient-to-br from-orange-400 to-amber-600' : '' }}
                    {{ $cat->value === 'autre' ? 'bg-gradient-to-br from-gray-400 to-slate-600' : '' }}">
                    <div class="text-2xl mb-1">
                        {{ $cat->value === 'alimentaire' ? '🍎' : ($cat->value === 'boissons' ? '🥤' : ($cat->value === 'hygiene' ? '🧴' : ($cat->value === 'entretien' ? '🧹' : '📦'))) }}
                    </div>
                    <p class="text-white/80 text-xs font-medium uppercase tracking-wider">{{ $cat->label() }}</p>
                    <p class="text-xl font-bold mt-1">{{ number_format($total, 2, ',', ' ') }} MAD</p>
                </div>
            @endforeach
        </div>
    @endif

    @if ($depenses->isEmpty())
        <div class="bg-white rounded-2xl shadow-lg p-12 text-center card-hover">
            <div class="text-5xl mb-4">📭</div>
            <p class="text-gray-500">Aucune dépense trouvée.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <table class="w-full table-modern">
                <thead>
                    <tr class="text-left text-sm font-semibold uppercase tracking-wider">
                        <th class="p-4">Libellé</th>
                        <th class="p-4">Qté</th>
                        <th class="p-4">Prix unitaire</th>
                        <th class="p-4">Total</th>
                        <th class="p-4">Catégorie</th>
                        <th class="p-4">Reçu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($depenses as $depense)
                        <tr class="border-b border-gray-100 card-hover">
                            <td class="p-4 font-medium">{{ $depense->libelle }}</td>
                            <td class="p-4">{{ $depense->quantite }}</td>
                            <td class="p-4">{{ number_format($depense->prix_unitaire, 2, ',', ' ') }} MAD</td>
                            <td class="p-4 font-semibold">{{ number_format($depense->quantite * $depense->prix_unitaire, 2, ',', ' ') }} MAD</td>
                            <td class="p-4">
                                <span class="inline-block px-3 py-1 text-xs font-medium rounded-full {{ $depense->categorie->badgeClass() }}">
                                    {{ $depense->categorie->label() }}
                                </span>
                            </td>
                            <td class="p-4">
                                <a href="{{ route('recus.show', $depense->recu) }}" class="text-purple-600 hover:text-purple-700 font-medium text-sm transition">
                                    Reçu #{{ $depense->recu_id }} →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layout>
