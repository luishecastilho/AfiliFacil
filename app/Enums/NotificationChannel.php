<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case Email = 'email';
    case Database = 'database';
    case Slack = 'slack';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Database => 'In-app',
            self::Slack => 'Slack',
        };
    }
}
