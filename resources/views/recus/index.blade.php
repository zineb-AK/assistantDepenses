<x-layout>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Mes reçus</h1>
        <a href="{{ route('recus.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Nouveau reçu
        </a>
    </div>

    @if ($recus->isEmpty())
        <p class="text-gray-500">Aucun reçu. <a href="{{ route('recus.create') }}" class="text-blue-600 hover:underline">Créer un reçu</a></p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full bg-white shadow rounded">
                <thead>
                    <tr class="border-b bg-gray-50 text-left">
                        <th class="p-3 font-semibold">Statut</th>
                        <th class="p-3 font-semibold">Dépenses</th>
                        <th class="p-3 font-semibold">Date</th>
                        <th class="p-3 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recus as $recu)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3">
                                <span class="px-2 py-1 text-sm rounded {{ $recu->statut->label() === 'En attente' ? 'bg-yellow-100 text-yellow-800' : ($recu->statut->label() === 'Traité' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $recu->statut->label() }}
                                </span>
                            </td>
                            <td class="p-3">{{ $recu->depenses->count() }}</td>
                            <td class="p-3 text-sm text-gray-600">{{ $recu->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3 flex gap-2">
                                <a href="{{ route('recus.show', $recu) }}" class="text-blue-600 hover:underline">Voir</a>
                                <form method="POST" action="{{ route('recus.destroy', $recu) }}" onsubmit="return confirm('Supprimer ce reçu ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layout>
