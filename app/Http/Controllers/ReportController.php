<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    private $locations = [
        'kantin' => [
            'name' => 'Kantin Pertanian',
            'table' => 'tbl_kantin',
            'device' => 'SENSOR_KANTIN_001'
        ],
        'lapangan' => [
            'name' => 'Lapangan Futsal',
            'table' => 'tbl_lapangan',
            'device' => 'SENSOR_LAPANGAN_001'
        ],
        'parkiran' => [
            'name' => 'Area Parkiran',
            'table' => 'tbl_parkiran',
            'device' => 'SENSOR_PARKIR_001'
        ],
        'food_court' => [
            'name' => 'Food Court',
            'table' => 'tbl_food_court',
            'device' => 'SENSOR_FOODCOURT_001'
        ],
        'taman' => [
            'name' => 'Taman Kampus',
            'table' => 'tbl_taman',
            'device' => 'SENSOR_TAMAN_001'
        ],
        'real_time' => [
            'name' => 'Monitoring Real-Time',
            'table' => 'tbl_temperatur',
            'device' => 'SENSOR_REALTIME_001'
        ]
    ];

    public function index(Request $request)
    {
        // Set default date range (last 7 days)
        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $selectedLocation = $request->input('location', 'all');
        $reportType = $request->input('report_type', 'summary');

        // Generate report data based on type
        $reportData = $this->generateReportData($startDate, $endDate, $selectedLocation, $reportType);

        return view('reports.index', compact(
            'reportData', 
            'startDate', 
            'endDate', 
            'selectedLocation', 
            'reportType'
        ));
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $selectedLocation = $request->input('location', 'all');
        $format = $request->input('format', 'csv');

        $data = $this->getExportData($startDate, $endDate, $selectedLocation);

        switch ($format) {
            case 'csv':
                return $this->exportToCSV($data, $startDate, $endDate, $selectedLocation);
            case 'json':
                return $this->exportToJSON($data, $startDate, $endDate, $selectedLocation);
            case 'pdf':
                return $this->exportToPDF($data, $startDate, $endDate, $selectedLocation);
            default:
                return $this->exportToCSV($data, $startDate, $endDate, $selectedLocation);
        }
    }

    private function generateReportData($startDate, $endDate, $location, $reportType)
    {
        $data = [
            'summary' => $this->getSummaryData($startDate, $endDate, $location),
            'daily' => $this->getDailyData($startDate, $endDate, $location),
            'hourly' => $this->getHourlyData($startDate, $endDate, $location),
            'alerts' => $this->getAlerts($startDate, $endDate, $location),
            'statistics' => $this->getStatistics($startDate, $endDate, $location),
            'charts' => $this->getChartsData($startDate, $endDate, $location),
            'locations' => $this->locations
        ];

        return $data;
    }

    private function getSummaryData($startDate, $endDate, $location)
    {
        $tables = $location === 'all' ? array_column($this->locations, 'table') : [$this->locations[$location]['table']];
        
        $summary = [];
        foreach ($tables as $table) {
            $locationKey = array_search(['table' => $table], array_map(function($loc) {
                return ['table' => $loc['table']];
            }, $this->locations));
            
            if (!$locationKey) {
                $locationKey = array_keys(array_filter($this->locations, function($loc) use ($table) {
                    return $loc['table'] === $table;
                }))[0] ?? 'unknown';
            }

            $stats = DB::table($table)
                ->selectRaw('
                    COUNT(*) as total_readings,
                    AVG(nilai_temperatur) as avg_temp,
                    MIN(nilai_temperatur) as min_temp,
                    MAX(nilai_temperatur) as max_temp,
                    STDDEV(nilai_temperatur) as std_dev
                ')
                ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->first();

            $summary[$locationKey] = [
                'name' => $this->locations[$locationKey]['name'],
                'total_readings' => $stats->total_readings ?? 0,
                'avg_temp' => round($stats->avg_temp ?? 0, 2),
                'min_temp' => round($stats->min_temp ?? 0, 2),
                'max_temp' => round($stats->max_temp ?? 0, 2),
                'std_dev' => round($stats->std_dev ?? 0, 2),
                'status' => $this->getTemperatureStatus($stats->avg_temp ?? 0)
            ];
        }

        return $summary;
    }

    private function getDailyData($startDate, $endDate, $location)
    {
        $tables = $location === 'all' ? array_column($this->locations, 'table') : [$this->locations[$location]['table']];
        
        $dailyData = [];
        foreach ($tables as $table) {
            $locationKey = array_keys(array_filter($this->locations, function($loc) use ($table) {
                return $loc['table'] === $table;
            }))[0] ?? 'unknown';

            $daily = DB::table($table)
                ->selectRaw('
                    DATE(tanggal) as date,
                    COUNT(*) as readings,
                    AVG(nilai_temperatur) as avg_temp,
                    MIN(nilai_temperatur) as min_temp,
                    MAX(nilai_temperatur) as max_temp
                ')
                ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $dailyData[$locationKey] = $daily->map(function($item) {
                return [
                    'date' => $item->date,
                    'readings' => $item->readings,
                    'avg_temp' => round($item->avg_temp, 2),
                    'min_temp' => round($item->min_temp, 2),
                    'max_temp' => round($item->max_temp, 2),
                ];
            });
        }

        return $dailyData;
    }

    private function getHourlyData($startDate, $endDate, $location)
    {
        $tables = $location === 'all' ? array_column($this->locations, 'table') : [$this->locations[$location]['table']];
        
        $hourlyData = [];
        foreach ($tables as $table) {
            $locationKey = array_keys(array_filter($this->locations, function($loc) use ($table) {
                return $loc['table'] === $table;
            }))[0] ?? 'unknown';

            $hourly = DB::table($table)
                ->selectRaw('
                    HOUR(tanggal) as hour,
                    AVG(nilai_temperatur) as avg_temp,
                    COUNT(*) as readings
                ')
                ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $hourlyData[$locationKey] = $hourly->map(function($item) {
                return [
                    'hour' => $item->hour,
                    'avg_temp' => round($item->avg_temp, 2),
                    'readings' => $item->readings,
                ];
            });
        }

        return $hourlyData;
    }

    private function getAlerts($startDate, $endDate, $location)
    {
        $tables = $location === 'all' ? array_column($this->locations, 'table') : [$this->locations[$location]['table']];
        
        $alerts = [];
        foreach ($tables as $table) {
            $locationKey = array_keys(array_filter($this->locations, function($loc) use ($table) {
                return $loc['table'] === $table;
            }))[0] ?? 'unknown';

            // High temperature alerts (>= 35°C)
            $highTempAlerts = DB::table($table)
                ->select('nilai_temperatur', 'tanggal')
                ->where('nilai_temperatur', '>=', 35)
                ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->orderBy('tanggal', 'desc')
                ->get();

            // Low temperature alerts (<= 20°C)
            $lowTempAlerts = DB::table($table)
                ->select('nilai_temperatur', 'tanggal')
                ->where('nilai_temperatur', '<=', 20)
                ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->orderBy('tanggal', 'desc')
                ->get();

            $alerts[$locationKey] = [
                'location_name' => $this->locations[$locationKey]['name'],
                'high_temp' => $highTempAlerts,
                'low_temp' => $lowTempAlerts,
                'total_alerts' => $highTempAlerts->count() + $lowTempAlerts->count()
            ];
        }

        return $alerts;
    }

    private function getStatistics($startDate, $endDate, $location)
    {
        $tables = $location === 'all' ? array_column($this->locations, 'table') : [$this->locations[$location]['table']];
        
        $totalReadings = 0;
        $allTemperatures = [];
        
        foreach ($tables as $table) {
            $readings = DB::table($table)
                ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->count();
            
            $temperatures = DB::table($table)
                ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->pluck('nilai_temperatur')
                ->toArray();
            
            $totalReadings += $readings;
            $allTemperatures = array_merge($allTemperatures, $temperatures);
        }

        $avgTemp = count($allTemperatures) > 0 ? array_sum($allTemperatures) / count($allTemperatures) : 0;
        $minTemp = count($allTemperatures) > 0 ? min($allTemperatures) : 0;
        $maxTemp = count($allTemperatures) > 0 ? max($allTemperatures) : 0;

        return [
            'total_readings' => $totalReadings,
            'avg_temperature' => round($avgTemp, 2),
            'min_temperature' => round($minTemp, 2),
            'max_temperature' => round($maxTemp, 2),
            'temperature_range' => round($maxTemp - $minTemp, 2),
            'normal_readings' => count(array_filter($allTemperatures, function($temp) {
                return $temp >= 25 && $temp < 35;
            })),
            'high_readings' => count(array_filter($allTemperatures, function($temp) {
                return $temp >= 35;
            })),
            'low_readings' => count(array_filter($allTemperatures, function($temp) {
                return $temp < 25;
            }))
        ];
    }

    private function getChartsData($startDate, $endDate, $location)
    {
        $tables = $location === 'all' ? array_column($this->locations, 'table') : [$this->locations[$location]['table']];
        
        $chartData = [
            'temperature_trend' => [],
            'temperature_distribution' => [],
            'hourly_average' => []
        ];

        foreach ($tables as $table) {
            $locationKey = array_keys(array_filter($this->locations, function($loc) use ($table) {
                return $loc['table'] === $table;
            }))[0] ?? 'unknown';

            // Temperature trend (daily)
            $trend = DB::table($table)
                ->selectRaw('DATE(tanggal) as date, AVG(nilai_temperatur) as avg_temp')
                ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $chartData['temperature_trend'][$locationKey] = $trend;

            // Hourly average
            $hourlyAvg = DB::table($table)
                ->selectRaw('HOUR(tanggal) as hour, AVG(nilai_temperatur) as avg_temp')
                ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $chartData['hourly_average'][$locationKey] = $hourlyAvg;
        }

        return $chartData;
    }

    private function getTemperatureStatus($temperature)
    {
        if ($temperature >= 35) {
            return ['status' => 'Tinggi', 'color' => 'red'];
        } elseif ($temperature >= 30) {
            return ['status' => 'Sedang', 'color' => 'orange'];
        } elseif ($temperature >= 25) {
            return ['status' => 'Normal', 'color' => 'green'];
        } else {
            return ['status' => 'Rendah', 'color' => 'blue'];
        }
    }

    private function getExportData($startDate, $endDate, $location)
    {
        $tables = $location === 'all' ? $this->locations : [$location => $this->locations[$location]];
        
        $exportData = [];
        foreach ($tables as $key => $locationInfo) {
            $data = DB::table($locationInfo['table'])
                ->select('id_perangkat', 'nilai_temperatur', 'tanggal')
                ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->orderBy('tanggal')
                ->get();

            foreach ($data as $record) {
                $exportData[] = [
                    'location' => $locationInfo['name'],
                    'device_id' => $record->id_perangkat,
                    'temperature' => $record->nilai_temperatur,
                    'datetime' => $record->tanggal,
                    'date' => Carbon::parse($record->tanggal)->format('Y-m-d'),
                    'time' => Carbon::parse($record->tanggal)->format('H:i:s'),
                    'status' => $this->getTemperatureStatus($record->nilai_temperatur)['status']
                ];
            }
        }

        return $exportData;
    }

    private function exportToCSV($data, $startDate, $endDate, $location)
    {
        $filename = "temperature_report_{$startDate}_to_{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Write CSV header
            fputcsv($file, [
                'Location', 'Device ID', 'Temperature (°C)', 
                'Date Time', 'Date', 'Time', 'Status'
            ]);
            
            // Write data rows
            foreach ($data as $row) {
                fputcsv($file, [
                    $row['location'],
                    $row['device_id'],
                    $row['temperature'],
                    $row['datetime'],
                    $row['date'],
                    $row['time'],
                    $row['status']
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportToJSON($data, $startDate, $endDate, $location)
    {
        $filename = "temperature_report_{$startDate}_to_{$endDate}.json";
        
        $exportData = [
            'metadata' => [
                'export_date' => now()->toISOString(),
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'location' => $location,
                'total_records' => count($data)
            ],
            'data' => $data
        ];

        return Response::json($exportData)
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    private function exportToPDF($data, $startDate, $endDate, $location)
    {
        // This would require a PDF library like TCPDF or DomPDF
        // For now, return a simple response
        return response()->json([
            'message' => 'PDF export feature coming soon',
            'alternative' => 'Please use CSV or JSON export for now'
        ], 501);
    }
}