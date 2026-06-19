<?php

namespace App\Enums;

enum DepenseCategorie: string
{
    case Alimentaire = 'alimentaire';
    case Boissons = 'boissons';
    case Hygiene = 'hygiène';
    case Entretien = 'entretien';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Alimentaire => 'Alimentaire',
            self::Boissons => 'Boissons',
            self::Hygiene => 'Hygiène',
            self::Entretien => 'Entretien',
            self::Autre => 'Autre',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Alimentaire => 'bg-amber-100 text-amber-800',
            self::Boissons => 'bg-blue-100 text-blue-800',
            self::Hygiene => 'bg-pink-100 text-pink-800',
            self::Entretien => 'bg-purple-100 text-purple-800',
            self::Autre => 'bg-gray-100 text-gray-700',
        };
    }
}
