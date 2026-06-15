<?php

namespace App\Http\Controllers;

use App\Enums\RecuStatus;
use App\Http\Requests\StoreRecuRequest;
use App\Jobs\ExtraireDepensesDuRecu;
use App\Models\Recu;

class RecuController extends Controller
{
    public function index()
    {
        $recus = auth()->user()->recus()->with('depenses')->latest()->get();
        return view('recus.index', compact('recus'));
    }

    public function create()
    {
        return view('recus.create');
    }

    public function store(StoreRecuRequest $request)
    {
        $recu = auth()->user()->recus()->create([
            'texte_source' => $request->texte_source,
            'statut' => RecuStatus::Pending,
        ]);

        ExtraireDepensesDuRecu::dispatch($recu);

        return redirect()->route('recus.index')
            ->with('success', 'Reçu créé avec succès');
    }

    public function show(Recu $recu)
    {
        if ($recu->user_id !== auth()->id()) {
            abort(404);
        }

        $recu->load('depenses');

        return view('recus.show', compact('recu'));
    }

    public function destroy(Recu $recu)
    {
        if ($recu->user_id !== auth()->id()) {
            abort(404);
        }

        $recu->delete();

        return redirect()->route('recus.index')
            ->with('success', 'Reçu supprimé avec succès');
    }
}
