<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SimulateTemperatureData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temperature:simulate {--interval=30 : Update interval in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate real-time temperature data updates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $interval = $this->option('interval');
        $this->info("Starting temperature simulation with {$interval}s intervals...");
        $this->info("Press Ctrl+C to stop");

        $tables = [
            'tbl_temperatur' => 'SENSOR_REALTIME_001',
            'tbl_lapangan' => 'SENSOR_LAPANGAN_001', 
            'tbl_kantin' => 'SENSOR_KANTIN_001',
            'tbl_parkiran' => 'SENSOR_PARKIR_001',
            'tbl_food_court' => 'SENSOR_FOODCOURT_001',
            'tbl_taman' => 'SENSOR_TAMAN_001'
        ];

        while (true) {
            foreach ($tables as $table => $deviceId) {
                $this->updateTemperature($table, $deviceId);
            }
            
            $this->info('[' . now()->format('H:i:s') . '] Temperature data updated');
            sleep($interval);
        }
    }

    private function updateTemperature($table, $deviceId)
    {
        // Get last temperature for gradual changes
        $lastTemp = DB::table($table)
            ->where('id_perangkat', $deviceId)
            ->orderBy('tanggal', 'desc')
            ->value('nilai_temperatur') ?? 30;

        // Generate realistic temperature change (-2 to +2 degrees)
        $change = (rand(-200, 200) / 100); // -2.00 to +2.00
        $newTemp = max(18, min(45, $lastTemp + $change)); // Keep between 18-45°C

        // Add some location-specific bias
        $newTemp = $this->applyLocationBias($table, $newTemp);

        DB::table($table)->insert([
            'id_perangkat' => $deviceId,
            'nilai_temperatur' => round($newTemp, 1),
            'tanggal' => now()
        ]);
    }

    private function applyLocationBias($table, $temperature)
    {
        $hour = now()->hour;
        $isDay = $hour >= 6 && $hour <= 18;
        
        switch ($table) {
            case 'tbl_lapangan':
                // Lapangan tends to be hotter during day
                return $isDay ? $temperature + rand(2, 5) : $temperature;
                
            case 'tbl_parkiran':
                // Parking area gets very hot in afternoon
                return ($hour >= 12 && $hour <= 16) ? $temperature + rand(3, 6) : $temperature;
                
            case 'tbl_kantin':
            case 'tbl_food_court':
                // Food areas have cooking heat
                return ($hour >= 10 && $hour <= 14) ? $temperature + rand(1, 4) : $temperature;
                
            case 'tbl_taman':
                // Garden area is cooler due to plants
                return $temperature - rand(1, 3);
                
            default:
                return $temperature;
        }
    }
}