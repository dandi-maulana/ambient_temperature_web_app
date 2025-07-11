<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LocationController extends Controller
{
    private $locations = [
        'kantin' => [
            'name' => 'Kantin Pertanian',
            'table' => 'tbl_kantin',
            'device' => 'SENSOR_KANTIN_001',
            'description' => 'Area kantin utama dengan berbagai fasilitas makanan dan minuman untuk mahasiswa dan staf.',
            'capacity' => '200 orang',
            'operational_hours' => '07:00 - 17:00 WIB'
        ],
        'lapangan' => [
            'name' => 'Lapangan Futsal',
            'table' => 'tbl_lapangan',
            'device' => 'SENSOR_LAPANGAN_001',
            'description' => 'Lapangan futsal outdoor yang digunakan untuk kegiatan olahraga dan rekreasi.',
            'capacity' => '50 orang',
            'operational_hours' => '06:00 - 21:00 WIB'
        ],
        'parkiran' => [
            'name' => 'Area Parkiran',
            'table' => 'tbl_parkiran',
            'device' => 'SENSOR_PARKIR_001',
            'description' => 'Area parkir kendaraan bermotor dengan kapasitas untuk motor dan mobil.',
            'capacity' => '300 kendaraan',
            'operational_hours' => '24 jam'
        ],
        'food_court' => [
            'name' => 'Food Court',
            'table' => 'tbl_food_court',
            'device' => 'SENSOR_FOODCOURT_001',
            'description' => 'Food court modern dengan berbagai pilihan makanan dari berbagai tenant.',
            'capacity' => '150 orang',
            'operational_hours' => '08:00 - 20:00 WIB'
        ],
        'taman' => [
            'name' => 'Taman Kampus',
            'table' => 'tbl_taman',
            'device' => 'SENSOR_TAMAN_001',
            'description' => 'Area hijau dengan berbagai tanaman yang menyediakan suasana asri dan sejuk.',
            'capacity' => 'Tidak terbatas',
            'operational_hours' => '24 jam'
        ],
        'real_time' => [
            'name' => 'Monitoring Real-Time',
            'table' => 'tbl_temperatur',
            'device' => 'SENSOR_REALTIME_001',
            'description' => 'Sensor utama untuk monitoring suhu real-time di area kampus.',
            'capacity' => 'Area umum',
            'operational_hours' => '24 jam'
        ]
    ];

    public function index()
    {
        $locationsData = [];
        
        foreach ($this->locations as $key => $location) {
            $data = $this->getLocationData($location['table']);
            $locationsData[$key] = array_merge($location, $data);
        }

        return view('locations.index', compact('locationsData'));
    }

    public function show($locationKey)
    {
        if (!array_key_exists($locationKey, $this->locations)) {
            abort(404, 'Lokasi tidak ditemukan');
        }

        $location = $this->locations[$locationKey];
        $locationData = $this->getLocationData($location['table']);
        
        // Get detailed statistics
        $statistics = $this->getLocationStatistics($location['table']);
        
        // Get historical data for charts (last 7 days)
        $chartData = $this->getLocationChartData($location['table']);
        
        // Get hourly data for today
        $todayData = $this->getTodayHourlyData($location['table']);

        $locationInfo = array_merge($location, $locationData, [
            'statistics' => $statistics,
            'chartData' => $chartData,
            'todayData' => $todayData,
            'key' => $locationKey
        ]);

        return view('locations.detail', compact('locationInfo'));
    }

    private function getLocationData($table)
    {
        $result = DB::table($table)
            ->select('nilai_temperatur', 'tanggal', 'id_perangkat')
            ->orderBy('tanggal', 'desc')
            ->first();

        if ($result) {
            $statusData = $this->getTemperatureStatus((float) $result->nilai_temperatur);
            return [
                'current_temperature' => (float) $result->nilai_temperatur,
                'last_update' => $result->tanggal,
                'device_id' => $result->id_perangkat,
                'status' => $statusData['status'],
                'color' => $statusData['color'],
                'icon' => $statusData['icon'],
                'border' => $statusData['border'],
                'text' => $statusData['text'],
                'bg' => $statusData['bg']
            ];
        }

        return [
            'current_temperature' => 0,
            'last_update' => null,
            'device_id' => null,
            'status' => 'Normal',
            'color' => 'green',
            'icon' => 'fas fa-thermometer-half',
            'border' => 'border-green-500',
            'text' => 'text-green-500',
            'bg' => 'bg-green-50'
        ];
    }

    private function getLocationStatistics($table)
    {
        $stats = DB::table($table)
            ->select(
                DB::raw('AVG(nilai_temperatur) as avg_temp'),
                DB::raw('MAX(nilai_temperatur) as max_temp'),
                DB::raw('MIN(nilai_temperatur) as min_temp'),
                DB::raw('COUNT(*) as total_readings')
            )
            ->where('tanggal', '>=', now()->subDays(7))
            ->first();

        // Get readings for different time periods
        $todayReadings = DB::table($table)
            ->whereDate('tanggal', today())
            ->count();

        $yesterdayAvg = DB::table($table)
            ->whereDate('tanggal', now()->subDay())
            ->avg('nilai_temperatur');

        return [
            'avg_temp_7days' => round($stats->avg_temp ?? 0, 1),
            'max_temp_7days' => round($stats->max_temp ?? 0, 1),
            'min_temp_7days' => round($stats->min_temp ?? 0, 1),
            'total_readings' => $stats->total_readings ?? 0,
            'today_readings' => $todayReadings,
            'yesterday_avg' => round($yesterdayAvg ?? 0, 1)
        ];
    }

    private function getLocationChartData($table)
    {
        $data = DB::table($table)
            ->select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('AVG(nilai_temperatur) as avg_temp'),
                DB::raw('MAX(nilai_temperatur) as max_temp'),
                DB::raw('MIN(nilai_temperatur) as min_temp')
            )
            ->where('tanggal', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $data->map(function ($item) {
            return [
                'date' => $item->date,
                'avg_temperature' => round($item->avg_temp, 1),
                'max_temperature' => round($item->max_temp, 1),
                'min_temperature' => round($item->min_temp, 1)
            ];
        });
    }

    private function getTodayHourlyData($table)
    {
        $data = DB::table($table)
            ->select(
                DB::raw('HOUR(tanggal) as hour'),
                DB::raw('AVG(nilai_temperatur) as avg_temp')
            )
            ->whereDate('tanggal', today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return $data->map(function ($item) {
            return [
                'hour' => $item->hour,
                'temperature' => round($item->avg_temp, 1)
            ];
        });
    }

    private function getTemperatureStatus($temperature)
    {
        if ($temperature >= 35) {
            return [
                'status' => 'Tinggi',
                'color' => 'red',
                'icon' => 'fas fa-thermometer-full',
                'border' => 'border-red-500',
                'text' => 'text-red-500',
                'bg' => 'bg-red-50'
            ];
        } elseif ($temperature >= 30) {
            return [
                'status' => 'Sedang',
                'color' => 'orange',
                'icon' => 'fas fa-thermometer-three-quarters',
                'border' => 'border-orange-500',
                'text' => 'text-orange-500',
                'bg' => 'bg-orange-50'
            ];
        } elseif ($temperature >= 25) {
            return [
                'status' => 'Normal',
                'color' => 'green',
                'icon' => 'fas fa-thermometer-half',
                'border' => 'border-green-500',
                'text' => 'text-green-500',
                'bg' => 'bg-green-50'
            ];
        } else {
            return [
                'status' => 'Rendah',
                'color' => 'blue',
                'icon' => 'fas fa-thermometer-empty',
                'border' => 'border-blue-500',
                'text' => 'text-blue-500',
                'bg' => 'bg-blue-50'
            ];
        }
    }

    // API endpoint for real-time location data
    public function getLocationRealtimeData($locationKey)
    {
        if (!array_key_exists($locationKey, $this->locations)) {
            return response()->json(['error' => 'Lokasi tidak ditemukan'], 404);
        }

        $location = $this->locations[$locationKey];
        $data = $this->getLocationData($location['table']);
        $statistics = $this->getLocationStatistics($location['table']);

        return response()->json([
            'data' => $data,
            'statistics' => $statistics,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }
}
