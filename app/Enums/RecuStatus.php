<?php

namespace App\Enums;

enum RecuStatus: string
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Processed => 'Traité',
            self::Failed => 'Échoué',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'badge-pending',
            self::Processed => 'badge-processed',
            self::Failed => 'badge-failed',
        };
    }
}
