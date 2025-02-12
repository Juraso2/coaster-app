<?php

namespace App\Commands;

use Clue\React\Redis\RedisClient;
use CodeIgniter\CLI\BaseCommand;

class Monitor extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'app:monitor';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Monitor the roller coaster system';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'app:monitor';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        $redis = new RedisClient(getenv('redis.url'));
        $coasters = [];

        $redis->subscribe('coaster_events');
        echo "📡 Nasłuchuję zdarzeń w Redis Pub/Sub...\n\n";

        $redis->on('message', function ($channel, $message) use (&$coasters) {
            $data = json_decode($message, true);
            $coasterId = $data['coasterId'];

            $coasters[$coasterId] = $data;

            $this->displayStatistics($coasters);
        });
    }

    private function displayStatistics(array $coasters): void
    {
        system('clear');
        echo "🕒 [Godzina " . date('H:i') . "]\n";
        echo "📊 Aktualne statystyki systemu kolejek górskich:\n";

        foreach ($coasters as $id => $data) {
            echo "\n[Kolejka $id]\n";
            echo "1️⃣ Godziny działania: {$data['openingTime']} - {$data['closingTime']}\n";
            echo "2️⃣ Liczba wagonów: {$data['availableWagons']} / {$data['totalWagons']}\n";
            echo "3️⃣ Dostępny personel: {$data['staffCount']} / {$data['requiredStaff']}\n";
            echo "4️⃣ Klienci dziennie: {$data['customersCount']} (Pojemność: {$data['totalCapacity']})\n";

            $problemMessage = $this->detectProblems($data);
            echo "5️⃣ " . ($problemMessage ?: "🟢 Status: OK") . "\n";
        }
    }

    private function detectProblems(array $coasterData): string
    {
        $problems = [];

        if ($coasterData['staffCount'] < $coasterData['requiredStaff']) {
            $missingStaff = $coasterData['requiredStaff'] - $coasterData['staffCount'];
            $problems[] = "Brakuje $missingStaff pracowników";
        }

        if ($coasterData['availableWagons'] < $coasterData['totalWagons']) {
            $missingWagons = $coasterData['totalWagons'] - $coasterData['availableWagons'];
            $problems[] = "Brak $missingWagons wagonów";
        }

        if ($coasterData['customersCount'] > $coasterData['totalCapacity']) {
            $excessCustomers = $coasterData['customersCount'] - $coasterData['totalCapacity'];
            $problems[] = "Nie można obsłużyć $excessCustomers klientów";
        }

        if ($coasterData['staffCount'] > $coasterData['requiredStaff']) {
            $excessStaff = $coasterData['staffCount'] - $coasterData['requiredStaff'];
            $problems[] = "Nadmiarowy personel: $excessStaff";
        }

        if (!empty($problems)) {
            $problemMessage = "Problem: " . implode(", ", $problems);

            $logMessage = "[" . date('Y-m-d H:i:s') . "] Kolejka {$coasterData['coasterId']} - $problemMessage\n";
            file_put_contents(WRITEPATH .'/logs/monitor.log', $logMessage, FILE_APPEND);

            return $problemMessage;
        }

        return "";
    }
}
