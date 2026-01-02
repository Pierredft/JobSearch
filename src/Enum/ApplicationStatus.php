<?php

namespace App\Enum;

enum ApplicationStatus: string
{
    case SENT = 'sent';
    case NO_RESPONSE = 'no_response';
    case NEGATIVE_RESPONSE = 'negative_response';
    case POSITIVE_RESPONSE = 'positive_response';

    public function label(): string
    {
        return match($this) {
            self::SENT => 'Candidature envoyée',
            self::NO_RESPONSE => 'Sans retour',
            self::NEGATIVE_RESPONSE => 'Retour négatif',
            self::POSITIVE_RESPONSE => 'Retour positif',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::SENT => 'info',
            self::NO_RESPONSE => 'warning',
            self::NEGATIVE_RESPONSE => 'danger',
            self::POSITIVE_RESPONSE => 'success',
        };
    }
}
