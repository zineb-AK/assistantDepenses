<?php

namespace App\Models;

use App\Enums\RecuStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recu extends Model
{
    protected $fillable = ['user_id', 'texte_source', 'statut', 'payload_ia'];

    protected function casts(): array
    {
        return [
            'statut' => RecuStatus::class,
            'payload_ia' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class);
    }
}
