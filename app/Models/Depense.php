<?php

namespace App\Models;

use App\Enums\DepenseCategorie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    protected $fillable = ['recu_id', 'libelle', 'quantite', 'prix_unitaire', 'categorie'];

    protected function casts(): array
    {
        return [
            'categorie' => DepenseCategorie::class,
        ];
    }

    public function recu(): BelongsTo
    {
        return $this->belongsTo(Recu::class);
    }
}
