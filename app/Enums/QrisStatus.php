<?php

namespace App\Enums;

enum QrisStatus: string
{
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case ResubmitRequested = 'resubmit_requested';
    case ProofSubmitted = 'proof_submitted';

    public function label(): string
    {
        return match ($this) {
            self::Accepted => 'Diterima',
            self::Rejected => 'Ditolak',
            self::ResubmitRequested => 'Diminta Ulang',
            self::ProofSubmitted => 'Bukti Terkirim',
        };
    }
}
