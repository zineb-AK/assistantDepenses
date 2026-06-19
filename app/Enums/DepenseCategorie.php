<?php

namespace App\Enums;

enum DepenseCategorie: string
{
    case Alimentaire = 'alimentaire';
    case Boissons = 'boissons';
    case Hygiene = 'hygiene';
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

    public static function tryFromFlexible(string $value): ?self
    {
        $normalized = mb_strtolower(trim($value));

        $map = [
            'hygiène' => self::Hygiene,
            'alimentaire' => self::Alimentaire,
            'boissons' => self::Boissons,
            'boisson' => self::Boissons,
            'entretien' => self::Entretien,
            'autre' => self::Autre,
        ];

        return $map[$normalized] ?? self::tryFrom($normalized);
    }
}
