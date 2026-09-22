<?php

namespace App\Services;

use App\Models\DataminingRun;
use Illuminate\Support\Facades\Http;

class DataMiningRunner
{
    public function dispatch(string $type, ?string $dateFrom = null, ?string $dateTo = null): DataminingRun
    {
        $run = DataminingRun::create([
            'type'       => $type,
            'status'     => 'running',
            'parameters' => [
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ],
        ]);

        try {
            Http::timeout(1)->post(config('datamining.url').'/run', [
                'run_id'    => $run->id,
                'type'      => $type,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return $run;
    }
}
