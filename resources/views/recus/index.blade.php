<x-layout>
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-accent-800 tracking-wider">Mes reçus</h1>
        <a href="{{ route('recus.create') }}" class="px-4 py-2 bg-accent-500 text-white rounded-lg hover:bg-accent-600 font-medium tracking-wide shadow-sm transition">
            + Nouveau reçu
        </a>
    </div>

    @if ($recus->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-accent-100 p-12 text-center">
            <p class="text-muted text-lg">Aucun reçu pour le moment.</p>
            <a href="{{ route('recus.create') }}" class="text-accent-500 hover:text-accent-600 font-medium underline mt-2 inline-block">Créer un reçu</a>
        </div>
    @else
        <div class="overflow-x-auto bg-white rounded-xl shadow-sm border border-accent-100">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-accent-100 text-left tracking-wider text-sm">
                        <th class="p-3 font-semibold text-accent-600">Statut</th>
                        <th class="p-3 font-semibold text-accent-600">Dépenses</th>
                        <th class="p-3 font-semibold text-accent-600">Date</th>
                        <th class="p-3 font-semibold text-accent-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recus as $recu)
                        <tr class="border-b border-accent-50 hover:bg-accent-50/50 transition">
                            <td class="p-3">
                                <span class="px-3 py-1 text-sm font-medium rounded-full {{ $recu->statut->value === 'pending' ? 'bg-amber-50 text-amber-700' : ($recu->statut->value === 'processed' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700') }}">
                                    {{ $recu->statut->label() }}
                                </span>
                            </td>
                            <td class="p-3 text-muted">{{ $recu->depenses->count() }}</td>
                            <td class="p-3 text-sm text-muted">{{ $recu->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3 flex gap-3">
                                <a href="{{ route('recus.show', $recu) }}" class="text-accent-500 hover:text-accent-600 font-medium">Voir</a>
                                <form method="POST" action="{{ route('recus.destroy', $recu) }}" onsubmit="return confirm('Supprimer ce reçu ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-muted hover:text-red-600 font-medium">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layout>
