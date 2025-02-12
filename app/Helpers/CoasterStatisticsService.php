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
        $openTime = $coaster->getOpeningTime();
        $closeTime = $coaster->getClosingTime();

        $openTimestamp = strtotime("1970-01-01 $openTime");
        $closeTimestamp = strtotime("1970-01-01 $closeTime");

        if ($closeTimestamp < $openTimestamp) {
            $closeTimestamp += 24 * 60 * 60;
        }

        return ($closeTimestamp - $openTimestamp) / 60;
    }
}