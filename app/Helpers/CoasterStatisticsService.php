<?php

namespace App\Helpers;

use App\Entities\Coaster;

class CoasterStatisticsService
{
    public function calculateAvailableWagons(Coaster $coaster): array
    {
        $totalCapacity = 0;
        $availableWagons = 0;
        $operatingMinutes = $this->calculateOperatingMinutes($coaster);

        foreach ($coaster->getWagons() as $wagon) {
            $rideTime = $coaster->getLength() / $wagon->getSpeed();
            $totalTimePerRide = $rideTime + 5;
            $ridesPerDay = floor($operatingMinutes / $totalTimePerRide);

            if ($ridesPerDay > 0) {
                $availableWagons++;
                $totalCapacity += $wagon->getCapacity() * $ridesPerDay;
            }
        }

        return [$availableWagons, $totalCapacity];
    }

    public function calculateRequiredStaff(int $availableWagons): int
    {
        return 1 + ($availableWagons * 2);
    }

    public function calculateOperatingMinutes(Coaster $coaster): int
    {
        [$openHour, $openMinute] = explode(':', $coaster->getOpeningTime());
        [$closeHour, $closeMinute] = explode(':', $coaster->getClosingTime());

        return (($closeHour * 60 + $closeMinute) - ($openHour * 60 + $openMinute));
    }
}