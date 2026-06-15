<?php

namespace App\Http\Controllers;

use App\Enums\DepenseCategorie;
use App\Models\Depense;
use Illuminate\Http\Request;

class DepenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Depense::whereHas('recu', fn($q) => $q->where('user_id', auth()->id()))
            ->with('recu')
            ->latest();

        if ($request->filled('categorie')) {
            $categorie = $request->categorie;
            if (DepenseCategorie::tryFrom($categorie) !== null) {
                $query->where('categorie', $categorie);
            }
        }

        $depenses = $query->get();

        $totals = Depense::whereHas('recu', fn($q) => $q->where('user_id', auth()->id()))
            ->selectRaw('categorie, SUM(quantite * prix_unitaire) as total')
            ->groupBy('categorie')
            ->when($request->filled('categorie') && DepenseCategorie::tryFrom($request->categorie) !== null, fn($q) => $q->where('categorie', $request->categorie))
            ->get()
            ->keyBy('categorie');

        $categories = DepenseCategorie::cases();

        return view('depenses.index', compact('depenses', 'totals', 'categories'));
    }
}
