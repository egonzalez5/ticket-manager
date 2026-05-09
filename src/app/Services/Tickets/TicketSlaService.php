<?php

namespace App\Services\Tickets;

use App\Models\Sla;
use Carbon\CarbonInterface;
use Carbon\Carbon;

class TicketSlaService
{
    /**
     * Calcula la fecha límite de resolución (`due_date`) en base al SLA y un
     * timestamp base.
     *
     * @param  Sla|null  $sla
     * @param  CarbonInterface|null  $baseTime  Momento desde el que contar la resolución.
     */
    public function calculateDueDate(?Sla $sla, ?CarbonInterface $baseTime = null): ?CarbonInterface
    {
        if (!$sla) {
            return null;
        }

        $baseTime = $baseTime ?? Carbon::now();
        $minutes = $sla->resolution_time;

        if ($minutes === null) {
            return null;
        }

        return $baseTime->copy()->addMinutes((int) $minutes);
    }
}

