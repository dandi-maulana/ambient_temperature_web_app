<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
       public function index()
    {
        // Ambil data suhu terbaru dari setiap tabel
        $data = [
            'kantin' => $this->getLatestTemperature('tbl_kantin'),
            'lapangan' => $this->getLatestTemperature('tbl_lapangan'),
            'parkiran' => $this->getLatestTemperature('tbl_parkiran'),
            'food_court' => $this->getLatestTemperature('tbl_food_court'),
            'taman' => $this->getLatestTemperature('tbl_taman'),
            'real_time' => $this->getLatestTemperature('tbl_temperatur'),
        ];

        // Hitung rata-rata suhu
        $temperatures = array_column($data, 'nilai_temperatur');
        $averageTemp = count($temperatures) > 0 ? array_sum($temperatures) / count($temperatures) : 0;

        // Tentukan suhu tertinggi dan terendah
        $highestTemp = max($temperatures);
        $lowestTemp = min($temperatures);

        // Ambil data untuk chart (7 hari terakhir)
        $chartData = $this->getChartData();

        // Ambil laporan berdasarkan status suhu
        $reports = $this->generateReports($data);

        return view('dashboard', compact('data', 'averageTemp', 'highestTemp', 'lowestTemp', 'chartData', 'reports'));
    }

    private function getLatestTemperature($table)
    {
        $result = DB::table($table)
            ->select('nilai_temperatur', 'tanggal', 'id_perangkat')
            ->orderBy('tanggal', 'desc')
            ->first();

        if ($result) {
            $statusData = $this->getTemperatureStatus((float) $result->nilai_temperatur);
            return [
                'nilai_temperatur' => (float) $result->nilai_temperatur,
                'tanggal' => $result->tanggal,
                'id_perangkat' => $result->id_perangkat,
                'status' => $statusData['status'],
                'color' => $statusData['color'],
                'icon' => $statusData['icon'],
                'border' => $statusData['border'],
                'text' => $statusData['text'],
                'bg' => $statusData['bg']
            ];
        }

        return [
            'nilai_temperatur' => 0,
            'tanggal' => null,
            'id_perangkat' => null,
            'status' => 'Normal',
            'color' => 'green',
            'icon' => 'fas fa-thermometer-half',
            'border' => 'border-green-500',
            'text' => 'text-green-500',
            'bg' => 'bg-green-50'
        ];
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

    private function getChartData()
    {
        // Ambil data 7 hari terakhir untuk chart
        $data = DB::table('tbl_temperatur')
            ->select(DB::raw('DATE(tanggal) as date'), DB::raw('AVG(nilai_temperatur) as avg_temp'))
            ->where('tanggal', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $data->map(function ($item) {
            return [
                'date' => $item->date,
                'temperature' => round($item->avg_temp, 1)
            ];
        });
    }

    private function generateReports($data)
    {
        $reports = [];
        $locationNames = [
            'kantin' => 'Kantin Pertanian',
            'lapangan' => 'Lapangan Futsal',
            'parkiran' => 'Parkiran',
            'food_court' => 'FoodCourt',
            'taman' => 'Taman',
            'real_time' => 'Area Real-Time'
        ];

        foreach ($data as $key => $location) {
            $temp = $location['nilai_temperatur'];
            $locationName = $locationNames[$key];
            
            if ($temp >= 35) {
                $reports[] = [
                    'type' => 'danger',
                    'icon' => 'fas fa-exclamation-circle',
                    'color' => 'red',
                    'message' => "Suhu di {$locationName} sangat tinggi ({$temp}°C) - Berpotensi berbahaya!",
                    'time' => $this->getRandomTime(),
                    'bg' => 'bg-red-50',
                    'border' => 'border-red-200',
                    'text' => 'text-red-800'
                ];
            } elseif ($temp >= 32) {
                $reports[] = [
                    'type' => 'warning',
                    'icon' => 'fas fa-exclamation-triangle',
                    'color' => 'yellow',
                    'message' => "Suhu di {$locationName} tinggi ({$temp}°C) - Kurang nyaman untuk aktivitas.",
                    'time' => $this->getRandomTime(),
                    'bg' => 'bg-yellow-50',
                    'border' => 'border-yellow-200',
                    'text' => 'text-yellow-800'
                ];
            } elseif ($temp <= 20) {
                $reports[] = [
                    'type' => 'info',
                    'icon' => 'fas fa-info-circle',
                    'color' => 'blue',
                    'message' => "Suhu di {$locationName} rendah ({$temp}°C) - Cuaca sejuk dan nyaman.",
                    'time' => $this->getRandomTime(),
                    'bg' => 'bg-blue-50',
                    'border' => 'border-blue-200',
                    'text' => 'text-blue-800'
                ];
            }
        }

        // Tambahkan laporan umum jika ada banyak lokasi dengan suhu tinggi
        $highTempLocations = collect($data)->filter(function ($location) {
            return $location['nilai_temperatur'] >= 33;
        })->count();

        if ($highTempLocations >= 3) {
            array_unshift($reports, [
                'type' => 'critical',
                'icon' => 'fas fa-fire',
                'color' => 'red',
                'message' => "PERINGATAN: {$highTempLocations} lokasi memiliki suhu tinggi. Disarankan minum air yang banyak dan hindari aktivitas berat.",
                'time' => 'Baru saja',
                'bg' => 'bg-red-50',
                'border' => 'border-red-200',
                'text' => 'text-red-800'
            ]);
        }

        return collect($reports)->take(5); // Ambil 5 laporan teratas
    }

    private function getRandomTime()
    {
        $times = ['Baru saja', '5 menit lalu', '15 menit lalu', '30 menit lalu', '1 jam lalu', '2 jam lalu'];
        return $times[array_rand($times)];
    }

    // API endpoint untuk real-time updates
    public function getRealtimeData()
    {
        $data = [
            'kantin' => $this->getLatestTemperature('tbl_kantin'),
            'lapangan' => $this->getLatestTemperature('tbl_lapangan'),
            'parkiran' => $this->getLatestTemperature('tbl_parkiran'),
            'food_court' => $this->getLatestTemperature('tbl_food_court'),
            'taman' => $this->getLatestTemperature('tbl_taman'),
            'real_time' => $this->getLatestTemperature('tbl_temperatur'),
        ];

        $temperatures = array_column($data, 'nilai_temperatur');
        $averageTemp = count($temperatures) > 0 ? array_sum($temperatures) / count($temperatures) : 0;
        $highestTemp = max($temperatures);
        $lowestTemp = min($temperatures);
        $reports = $this->generateReports($data);

        return response()->json([
            'data' => $data,
            'averageTemp' => round($averageTemp, 1),
            'highestTemp' => $highestTemp,
            'lowestTemp' => $lowestTemp,
            'reports' => $reports,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }
}
