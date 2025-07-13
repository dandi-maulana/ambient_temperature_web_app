<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HealthController extends Controller
{
    private $locations = [
        'kantin' => [
            'name' => 'Kantin Pertanian',
            'table' => 'tbl_kantin',
            'device' => 'SENSOR_KANTIN_001',
            'activity_type' => 'indoor_dining'
        ],
        'lapangan' => [
            'name' => 'Lapangan Futsal',
            'table' => 'tbl_lapangan',
            'device' => 'SENSOR_LAPANGAN_001',
            'activity_type' => 'outdoor_sports'
        ],
        'parkiran' => [
            'name' => 'Area Parkiran',
            'table' => 'tbl_parkiran',
            'device' => 'SENSOR_PARKIR_001',
            'activity_type' => 'outdoor_walking'
        ],
        'food_court' => [
            'name' => 'Food Court',
            'table' => 'tbl_food_court',
            'device' => 'SENSOR_FOODCOURT_001',
            'activity_type' => 'indoor_dining'
        ],
        'taman' => [
            'name' => 'Taman Kampus',
            'table' => 'tbl_taman',
            'device' => 'SENSOR_TAMAN_001',
            'activity_type' => 'outdoor_relaxation'
        ],
        'real_time' => [
            'name' => 'Area Umum',
            'table' => 'tbl_temperatur',
            'device' => 'SENSOR_REALTIME_001',
            'activity_type' => 'general'
        ]
    ];

    public function index(Request $request)
    {
        // Get current health data for all locations
        $healthData = $this->getHealthAssessment();
        
        // Get overall campus health status
        $campusHealth = $this->getCampusHealthStatus($healthData);
        
        // Get health recommendations
        $recommendations = $this->getHealthRecommendations($healthData);
        
        // Get health trends (last 7 days)
        $healthTrends = $this->getHealthTrends();
        
        // Get activity recommendations per location
        $activityRecommendations = $this->getActivityRecommendations($healthData);
        
        // Get health alerts
        $healthAlerts = $this->getHealthAlerts();

        return view('health.index', compact(
            'healthData',
            'campusHealth', 
            'recommendations',
            'healthTrends',
            'activityRecommendations',
            'healthAlerts'
        ));
    }

    private function getHealthAssessment()
    {
        $healthData = [];
        
        foreach ($this->locations as $key => $location) {
            // Get latest temperature
            $latestData = DB::table($location['table'])
                ->select('nilai_temperatur', 'tanggal')
                ->orderBy('tanggal', 'desc')
                ->first();

            if ($latestData) {
                $temp = $latestData->nilai_temperatur;
                $healthStatus = $this->calculateHealthStatus($temp, $location['activity_type']);
                
                // Get temperature statistics for the day
                $dailyStats = DB::table($location['table'])
                    ->selectRaw('AVG(nilai_temperatur) as avg_temp, MAX(nilai_temperatur) as max_temp, MIN(nilai_temperatur) as min_temp')
                    ->whereDate('tanggal', today())
                    ->first();

                $healthData[$key] = [
                    'location_name' => $location['name'],
                    'current_temp' => $temp,
                    'health_status' => $healthStatus,
                    'daily_avg' => round($dailyStats->avg_temp ?? $temp, 1),
                    'daily_max' => round($dailyStats->max_temp ?? $temp, 1),
                    'daily_min' => round($dailyStats->min_temp ?? $temp, 1),
                    'activity_type' => $location['activity_type'],
                    'last_update' => $latestData->tanggal,
                    'recommendations' => $this->getLocationRecommendations($temp, $location['activity_type'])
                ];
            }
        }

        return $healthData;
    }

    private function calculateHealthStatus($temperature, $activityType)
    {
        // Different health thresholds based on activity type
        $thresholds = [
            'outdoor_sports' => [
                'excellent' => [18, 25],
                'good' => [25, 28],
                'moderate' => [28, 32],
                'poor' => [32, 35],
                'dangerous' => [35, 100]
            ],
            'indoor_dining' => [
                'excellent' => [20, 26],
                'good' => [26, 29],
                'moderate' => [29, 33],
                'poor' => [33, 36],
                'dangerous' => [36, 100]
            ],
            'outdoor_walking' => [
                'excellent' => [18, 26],
                'good' => [26, 30],
                'moderate' => [30, 34],
                'poor' => [34, 37],
                'dangerous' => [37, 100]
            ],
            'outdoor_relaxation' => [
                'excellent' => [20, 27],
                'good' => [27, 31],
                'moderate' => [31, 35],
                'poor' => [35, 38],
                'dangerous' => [38, 100]
            ],
            'general' => [
                'excellent' => [20, 26],
                'good' => [26, 30],
                'moderate' => [30, 34],
                'poor' => [34, 37],
                'dangerous' => [37, 100]
            ]
        ];

        $threshold = $thresholds[$activityType] ?? $thresholds['general'];

        foreach ($threshold as $status => $range) {
            if ($temperature >= $range[0] && $temperature < $range[1]) {
                return [
                    'level' => $status,
                    'score' => $this->getHealthScore($status),
                    'color' => $this->getHealthColor($status),
                    'icon' => $this->getHealthIcon($status),
                    'description' => $this->getHealthDescription($status, $activityType)
                ];
            }
        }

        return [
            'level' => 'unknown',
            'score' => 0,
            'color' => 'gray',
            'icon' => 'fas fa-question',
            'description' => 'Status tidak diketahui'
        ];
    }

    private function getHealthScore($level)
    {
        $scores = [
            'excellent' => 100,
            'good' => 80,
            'moderate' => 60,
            'poor' => 40,
            'dangerous' => 20,
            'unknown' => 0
        ];

        return $scores[$level] ?? 0;
    }

    private function getHealthColor($level)
    {
        $colors = [
            'excellent' => 'green',
            'good' => 'blue',
            'moderate' => 'yellow',
            'poor' => 'orange',
            'dangerous' => 'red',
            'unknown' => 'gray'
        ];

        return $colors[$level] ?? 'gray';
    }

    private function getHealthIcon($level)
    {
        $icons = [
            'excellent' => 'fas fa-heart',
            'good' => 'fas fa-thumbs-up',
            'moderate' => 'fas fa-exclamation-circle',
            'poor' => 'fas fa-exclamation-triangle',
            'dangerous' => 'fas fa-skull-crossbones',
            'unknown' => 'fas fa-question'
        ];

        return $icons[$level] ?? 'fas fa-question';
    }

    private function getHealthDescription($level, $activityType)
    {
        $descriptions = [
            'outdoor_sports' => [
                'excellent' => 'Kondisi ideal untuk olahraga outdoor',
                'good' => 'Baik untuk aktivitas olahraga',
                'moderate' => 'Cukup aman, perhatikan hidrasi',
                'poor' => 'Berisiko heat stress, kurangi intensitas',
                'dangerous' => 'Berbahaya! Hindari olahraga outdoor'
            ],
            'indoor_dining' => [
                'excellent' => 'Suhu nyaman untuk makan',
                'good' => 'Kondisi baik untuk dining',
                'moderate' => 'Cukup nyaman, pastikan ventilasi',
                'poor' => 'Kurang nyaman, minum lebih banyak',
                'dangerous' => 'Terlalu panas, cari tempat sejuk'
            ],
            'outdoor_walking' => [
                'excellent' => 'Sempurna untuk jalan-jalan',
                'good' => 'Nyaman untuk aktivitas outdoor',
                'moderate' => 'Aman dengan perlindungan matahari',
                'poor' => 'Berisiko, batasi waktu outdoor',
                'dangerous' => 'Hindari aktivitas outdoor'
            ],
            'outdoor_relaxation' => [
                'excellent' => 'Ideal untuk bersantai outdoor',
                'good' => 'Nyaman untuk relaxation',
                'moderate' => 'Cari tempat teduh',
                'poor' => 'Kurang nyaman, pertimbangkan indoor',
                'dangerous' => 'Pindah ke tempat ber-AC'
            ],
            'general' => [
                'excellent' => 'Kondisi kesehatan optimal',
                'good' => 'Kondisi kesehatan baik',
                'moderate' => 'Perlu perhatian ekstra',
                'poor' => 'Berisiko untuk kesehatan',
                'dangerous' => 'Berbahaya untuk kesehatan'
            ]
        ];

        return $descriptions[$activityType][$level] ?? $descriptions['general'][$level] ?? 'Status tidak diketahui';
    }

    private function getLocationRecommendations($temperature, $activityType)
    {
        $recommendations = [];

        if ($temperature >= 35) {
            $recommendations[] = [
                'priority' => 'high',
                'icon' => 'fas fa-exclamation-triangle',
                'text' => 'Hindari aktivitas berat, segera cari tempat sejuk'
            ];
            $recommendations[] = [
                'priority' => 'high', 
                'icon' => 'fas fa-tint',
                'text' => 'Minum air setiap 15-20 menit'
            ];
        } elseif ($temperature >= 30) {
            $recommendations[] = [
                'priority' => 'medium',
                'icon' => 'fas fa-sun',
                'text' => 'Gunakan pelindung matahari jika outdoor'
            ];
            $recommendations[] = [
                'priority' => 'medium',
                'icon' => 'fas fa-clock',
                'text' => 'Batasi waktu aktivitas di area ini'
            ];
        } else {
            $recommendations[] = [
                'priority' => 'low',
                'icon' => 'fas fa-check',
                'text' => 'Kondisi aman untuk semua aktivitas'
            ];
        }

        // Activity-specific recommendations
        if ($activityType === 'outdoor_sports' && $temperature >= 28) {
            $recommendations[] = [
                'priority' => 'medium',
                'icon' => 'fas fa-running',
                'text' => 'Kurangi intensitas olahraga, istirahat lebih sering'
            ];
        }

        return $recommendations;
    }

    private function getCampusHealthStatus($healthData)
    {
        $totalScore = 0;
        $locationCount = count($healthData);

        foreach ($healthData as $data) {
            $totalScore += $data['health_status']['score'];
        }

        $averageScore = $locationCount > 0 ? $totalScore / $locationCount : 0;

        if ($averageScore >= 90) {
            $level = 'excellent';
        } elseif ($averageScore >= 70) {
            $level = 'good';
        } elseif ($averageScore >= 50) {
            $level = 'moderate';
        } elseif ($averageScore >= 30) {
            $level = 'poor';
        } else {
            $level = 'critical';
        }

        return [
            'score' => round($averageScore),
            'level' => $level,
            'color' => $this->getHealthColor($level),
            'description' => $this->getCampusHealthDescription($level),
            'locations_count' => $locationCount,
            'safe_locations' => collect($healthData)->where('health_status.score', '>=', 70)->count(),
            'risk_locations' => collect($healthData)->where('health_status.score', '<', 50)->count()
        ];
    }

    private function getCampusHealthDescription($level)
    {
        $descriptions = [
            'excellent' => 'Kondisi kampus sangat sehat dan aman untuk semua aktivitas',
            'good' => 'Kondisi kampus baik, aman untuk aktivitas normal',
            'moderate' => 'Kondisi kampus cukup, perlu perhatian pada beberapa area',
            'poor' => 'Kondisi kampus kurang sehat, batasi aktivitas outdoor',
            'critical' => 'Kondisi kampus tidak sehat, hindari aktivitas berat'
        ];

        return $descriptions[$level] ?? 'Status tidak diketahui';
    }

    private function getHealthRecommendations($healthData)
    {
        $recommendations = [];

        // General campus recommendations
        $riskLocations = collect($healthData)->where('health_status.score', '<', 50)->count();
        $totalLocations = count($healthData);

        if ($riskLocations > $totalLocations / 2) {
            $recommendations[] = [
                'category' => 'general',
                'priority' => 'high',
                'title' => 'Peringatan Cuaca Panas',
                'description' => 'Lebih dari setengah area kampus memiliki suhu tinggi',
                'actions' => [
                    'Batasi aktivitas outdoor antara pukul 10:00 - 16:00',
                    'Gunakan ruangan ber-AC untuk kegiatan penting',
                    'Sediakan air minum di semua area',
                    'Pantau kondisi kesehatan secara berkala'
                ]
            ];
        }

        // Hydration recommendations
        $highTempLocations = collect($healthData)->where('current_temp', '>=', 32)->count();
        if ($highTempLocations > 0) {
            $recommendations[] = [
                'category' => 'hydration',
                'priority' => 'medium',
                'title' => 'Rekomendasi Hidrasi',
                'description' => 'Beberapa area memiliki suhu tinggi yang memerlukan hidrasi ekstra',
                'actions' => [
                    'Minum air 200-300ml setiap 15-20 menit',
                    'Hindari minuman berkafein berlebihan',
                    'Konsumsi buah-buahan yang mengandung air tinggi',
                    'Gunakan elektrolit jika berkeringat banyak'
                ]
            ];
        }

        // Activity recommendations
        $recommendations[] = [
            'category' => 'activity',
            'priority' => 'low',
            'title' => 'Panduan Aktivitas',
            'description' => 'Tips melakukan aktivitas berdasarkan kondisi suhu saat ini',
            'actions' => [
                'Pilih waktu pagi (06:00-09:00) atau sore (17:00-19:00) untuk olahraga',
                'Gunakan pakaian berbahan ringan dan berwarna terang',
                'Istirahat di tempat teduh setiap 30 menit',
                'Kenali tanda-tanda heat exhaustion'
            ]
        ];

        return $recommendations;
    }

    private function getHealthTrends()
    {
        $trends = [];
        
        foreach ($this->locations as $key => $location) {
            $dailyData = DB::table($location['table'])
                ->selectRaw('DATE(tanggal) as date, AVG(nilai_temperatur) as avg_temp')
                ->where('tanggal', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $healthScores = $dailyData->map(function($item) use ($location) {
                $healthStatus = $this->calculateHealthStatus($item->avg_temp, $location['activity_type']);
                return [
                    'date' => $item->date,
                    'temperature' => round($item->avg_temp, 1),
                    'health_score' => $healthStatus['score']
                ];
            });

            $trends[$key] = [
                'name' => $location['name'],
                'data' => $healthScores
            ];
        }

        return $trends;
    }

    private function getActivityRecommendations($healthData)
    {
        $activities = [
            'outdoor_sports' => 'Olahraga Outdoor',
            'indoor_activities' => 'Aktivitas Indoor', 
            'walking' => 'Jalan Kaki',
            'social_gathering' => 'Kegiatan Sosial',
            'studying' => 'Belajar'
        ];

        $recommendations = [];

        foreach ($activities as $activityKey => $activityName) {
            $bestLocations = [];
            $avoidLocations = [];

            foreach ($healthData as $locationKey => $data) {
                if ($data['health_status']['score'] >= 70) {
                    $bestLocations[] = $data['location_name'];
                } elseif ($data['health_status']['score'] < 50) {
                    $avoidLocations[] = $data['location_name'];
                }
            }

            $recommendations[$activityKey] = [
                'name' => $activityName,
                'best_locations' => $bestLocations,
                'avoid_locations' => $avoidLocations,
                'recommendation' => $this->getActivityRecommendation($activityKey, $bestLocations, $avoidLocations)
            ];
        }

        return $recommendations;
    }

    private function getActivityRecommendation($activity, $bestLocations, $avoidLocations)
    {
        if (count($bestLocations) > 3) {
            return [
                'status' => 'recommended',
                'message' => 'Kondisi baik untuk aktivitas ini di sebagian besar area kampus'
            ];
        } elseif (count($bestLocations) > 0) {
            return [
                'status' => 'conditional', 
                'message' => 'Disarankan di area: ' . implode(', ', $bestLocations)
            ];
        } else {
            return [
                'status' => 'not_recommended',
                'message' => 'Tidak disarankan saat ini, pertimbangkan waktu lain atau lokasi indoor'
            ];
        }
    }

    private function getHealthAlerts()
    {
        $alerts = [];

        foreach ($this->locations as $key => $location) {
            // Check for extreme temperatures in last hour
            $recentData = DB::table($location['table'])
                ->where('tanggal', '>=', now()->subHour())
                ->where(function($query) {
                    $query->where('nilai_temperatur', '>=', 37)
                          ->orWhere('nilai_temperatur', '<=', 15);
                })
                ->orderBy('tanggal', 'desc')
                ->get();

            foreach ($recentData as $data) {
                $severity = $data->nilai_temperatur >= 37 ? 'critical' : 'warning';
                $type = $data->nilai_temperatur >= 37 ? 'heat' : 'cold';
                
                $alerts[] = [
                    'location' => $location['name'],
                    'severity' => $severity,
                    'type' => $type,
                    'temperature' => $data->nilai_temperatur,
                    'timestamp' => $data->tanggal,
                    'message' => $this->getAlertMessage($type, $data->nilai_temperatur, $location['name'])
                ];
            }
        }

        // Sort by timestamp, most recent first
        usort($alerts, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($alerts, 0, 10); // Return last 10 alerts
    }

    private function getAlertMessage($type, $temperature, $location)
    {
        if ($type === 'heat') {
            if ($temperature >= 40) {
                return "BAHAYA EKSTREM di {$location}! Suhu {$temperature}°C - Evakuasi segera!";
            } elseif ($temperature >= 38) {
                return "PERINGATAN TINGGI di {$location}! Suhu {$temperature}°C - Hindari area ini!";
            } else {
                return "Alert Panas di {$location}: {$temperature}°C - Batasi aktivitas";
            }
        } else {
            return "Alert Dingin di {$location}: {$temperature}°C - Gunakan pakaian hangat";
        }
    }

    // API endpoint for real-time health data
    public function getRealtimeHealthData()
    {
        $healthData = $this->getHealthAssessment();
        $campusHealth = $this->getCampusHealthStatus($healthData);
        $healthAlerts = $this->getHealthAlerts();

        return response()->json([
            'campus_health' => $campusHealth,
            'locations' => $healthData,
            'alerts' => $healthAlerts,
            'timestamp' => now()->toISOString()
        ]);
    }
}