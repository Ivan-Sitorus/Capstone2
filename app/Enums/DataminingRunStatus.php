<?php

namespace App\Enums;

enum DataminingRunStatus: string
{
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Running => 'Diproses',
            self::Completed => 'Selesai',
            self::Failed => 'Gagal',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Running => 'warning',
            self::Completed => 'success',
            self::Failed => 'danger',
        };
    }
}
