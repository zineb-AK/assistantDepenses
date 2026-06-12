<x-layout>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Dépenses</h1>

        <form method="GET" action="{{ route('depenses.index') }}" class="flex items-center gap-2">
            <select name="categorie" class="border rounded p-2 text-sm">
                <option value="">Toutes les catégories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->value }}" {{ request('categorie') === $cat->value ? 'selected' : '' }}>
                        {{ $cat->label() }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">Filtrer</button>
        </form>
    </div>

    @if ($totals->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
            @foreach ($categories as $cat)
                @php $total = $totals->get($cat->value)?->total ?? 0; @endphp
                <div class="bg-white shadow rounded p-4 text-center">
                    <p class="text-sm text-gray-500">{{ $cat->label() }}</p>
                    <p class="text-lg font-bold">{{ number_format($total, 2, ',', ' ') }} MAD</p>
                </div>
            @endforeach
        </div>
    @endif

    @if ($depenses->isEmpty())
        <p class="text-gray-500">Aucune dépense trouvée.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full bg-white shadow rounded">
                <thead>
                    <tr class="border-b bg-gray-50 text-left">
                        <th class="p-3 font-semibold">Libellé</th>
                        <th class="p-3 font-semibold">Qté</th>
                        <th class="p-3 font-semibold">Prix unitaire</th>
                        <th class="p-3 font-semibold">Total</th>
                        <th class="p-3 font-semibold">Catégorie</th>
                        <th class="p-3 font-semibold">Reçu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($depenses as $depense)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3">{{ $depense->libelle }}</td>
                            <td class="p-3">{{ $depense->quantite }}</td>
                            <td class="p-3">{{ number_format($depense->prix_unitaire, 2, ',', ' ') }} MAD</td>
                            <td class="p-3">{{ number_format($depense->quantite * $depense->prix_unitaire, 2, ',', ' ') }} MAD</td>
                            <td class="p-3">{{ $depense->categorie->label() }}</td>
                            <td class="p-3">
                                <a href="{{ route('recus.show', $depense->recu) }}" class="text-blue-600 hover:underline text-sm">
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
