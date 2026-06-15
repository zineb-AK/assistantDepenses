<x-layout>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📄 Mes reçus</h1>
            <p class="text-gray-500 text-sm">Gérez vos reçus fournisseurs</p>
        </div>
        <a href="{{ route('recus.create') }}" class="btn-gradient px-5 py-2.5 text-white font-medium rounded-xl text-sm inline-flex items-center gap-2 shadow-lg">
            + Nouveau reçu
        </a>
    </div>

    @if ($recus->isEmpty())
        <div class="bg-white rounded-2xl shadow-lg p-12 text-center card-hover">
            <div class="text-5xl mb-4">📋</div>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Aucun reçu pour le moment</h2>
            <p class="text-gray-500 mb-6">Commencez par créer votre premier reçu fournisseur.</p>
            <a href="{{ route('recus.create') }}" class="btn-gradient px-6 py-3 text-white font-medium rounded-xl inline-block shadow-lg">
                + Créer un reçu
            </a>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <table class="w-full table-modern">
                <thead>
                    <tr class="text-left text-sm font-semibold uppercase tracking-wider">
                        <th class="p-4">Statut</th>
                        <th class="p-4">Dépenses</th>
                        <th class="p-4">Date</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recus as $recu)
                        <tr class="border-b border-gray-100 card-hover">
                            <td class="p-4">
                                <span class="inline-block px-3 py-1.5 text-xs font-bold rounded-full text-white shadow-sm
                                    {{ $recu->statut->value === 'pending' ? 'badge-pending' : ($recu->statut->value === 'processed' ? 'badge-processed' : 'badge-failed') }}">
                                    {{ $recu->statut->label() }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 px-3 py-1 rounded-full text-sm font-medium">
                                    🧾 {{ $recu->depenses->count() }}
                                </span>
                            </td>
                            <td class="p-4 text-sm text-gray-500">{{ $recu->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('recus.show', $recu) }}" class="px-4 py-1.5 bg-gradient-to-r from-blue-400 to-blue-500 text-white text-sm rounded-lg hover:shadow-md transition">
                                        Voir
                                    </a>
                                    <form method="POST" action="{{ route('recus.destroy', $recu) }}" onsubmit="return confirm('Supprimer ce reçu ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-4 py-1.5 btn-danger text-white text-sm rounded-lg">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layout>
