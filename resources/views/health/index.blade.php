@extends('layouts.app')

@section('title', 'Kesehatan - Pemantauan Suhu Luar Ruangan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Status Kesehatan Kampus</h1>
        <p class="mt-2 text-gray-600">Monitor kondisi kesehatan berdasarkan suhu dan rekomendasi aktivitas</p>
    </div>

    <!-- Campus Health Overview -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Status Kesehatan Kampus</h2>
                <p class="text-gray-600">Penilaian kesehatan berdasarkan kondisi suhu di seluruh area</p>
            </div>
            <div class="text-center">
                <div class="relative inline-flex items-center justify-center w-24 h-24 mb-2">
                    <svg class="w-24 h-24 transform -rotate-90">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="transparent" 
                                class="text-gray-200" transform="translate(36, 36)"></circle>
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="transparent"
                                class="text-{{ $campusHealth['color'] }}-500" transform="translate(36, 36)"
                                stroke-dasharray="{{ 2 * 3.14159 * 10 }}"
                                stroke-dashoffset="{{ 2 * 3.14159 * 10 * (1 - $campusHealth['score'] / 100) }}"
                                stroke-linecap="round"></circle>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-bold text-{{ $campusHealth['color'] }}-600">{{ $campusHealth['score'] }}</span>
                    </div>
                </div>
                <div class="text-sm font-medium text-{{ $campusHealth['color'] }}-600 capitalize">{{ $campusHealth['level'] }}</div>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900">{{ $campusHealth['locations_count'] }}</div>
                <div class="text-sm text-gray-600">Total Area</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $campusHealth['safe_locations'] }}</div>
                <div class="text-sm text-gray-600">Area Aman</div>
            </div>
            <div class="text-center p-4 bg-red-50 rounded-lg">
                <div class="text-2xl font-bold text-red-600">{{ $campusHealth['risk_locations'] }}</div>
                <div class="text-sm text-gray-600">Area Berisiko</div>
            </div>
        </div>

        <div class="mt-4 p-4 bg-{{ $campusHealth['color'] }}-50 rounded-lg">
            <p class="text-{{ $campusHealth['color'] }}-800 text-sm">
                <i class="fas fa-info-circle mr-2"></i>{{ $campusHealth['description'] }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Location Health Status -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Location Health Cards -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Status Kesehatan per Lokasi</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($healthData as $key => $location)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-gray-900">{{ $location['location_name'] }}</h3>
                            <div class="flex items-center space-x-2">
                                <i class="{{ $location['health_status']['icon'] }} text-{{ $location['health_status']['color'] }}-500"></i>
                                <span class="text-sm font-medium text-{{ $location['health_status']['color'] }}-600">
                                    {{ $location['health_status']['score'] }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-2 mb-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Suhu Saat Ini:</span>
                                <span class="font-medium">{{ $location['current_temp'] }}°C</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Rata-rata Hari Ini:</span>
                                <span class="font-medium">{{ $location['daily_avg'] }}°C</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-{{ $location['health_status']['color'] }}-500 h-2 rounded-full" 
                                     style="width: {{ $location['health_status']['score'] }}%"></div>
                            </div>
                        </div>

                        <p class="text-xs text-{{ $location['health_status']['color'] }}-700 mb-3">
                            {{ $location['health_status']['description'] }}
                        </p>

                        <!-- Location Recommendations -->
                        @if(count($location['recommendations']) > 0)
                        <div class="space-y-1">
                            @foreach($location['recommendations'] as $rec)
                            <div class="flex items-start space-x-2 text-xs">
                                <i class="{{ $rec['icon'] }} 
                                    @if($rec['priority'] === 'high') text-red-500
                                    @elseif($rec['priority'] === 'medium') text-yellow-500
                                    @else text-green-500 @endif mt-0.5"></i>
                                <span class="text-gray-700">{{ $rec['text'] }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Health Trends Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Tren Kesehatan 7 Hari Terakhir</h2>
                <div class="h-64">
                    <canvas id="healthTrendChart"></canvas>
                </div>
            </div>

            <!-- Activity Recommendations -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Rekomendasi Aktivitas</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($activityRecommendations as $activity => $rec)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-900">{{ $rec['name'] }}</h3>
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($rec['recommendation']['status'] === 'recommended') bg-green-100 text-green-800
                                @elseif($rec['recommendation']['status'] === 'conditional') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                @if($rec['recommendation']['status'] === 'recommended') Direkomendasikan
                                @elseif($rec['recommendation']['status'] === 'conditional') Bersyarat
                                @else Tidak Disarankan @endif
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">{{ $rec['recommendation']['message'] }}</p>
                        
                        @if(count($rec['best_locations']) > 0)
                        <div class="text-xs text-green-700 mb-1">
                            <i class="fas fa-check-circle mr-1"></i>
                            Area terbaik: {{ implode(', ', array_slice($rec['best_locations'], 0, 2)) }}
                            @if(count($rec['best_locations']) > 2)
                                <span class="text-gray-500">+{{ count($rec['best_locations']) - 2 }} lainnya</span>
                            @endif
                        </div>
                        @endif
                        
                        @if(count($rec['avoid_locations']) > 0)
                        <div class="text-xs text-red-700">
                            <i class="fas fa-times-circle mr-1"></i>
                            Hindari: {{ implode(', ', array_slice($rec['avoid_locations'], 0, 2)) }}
                            @if(count($rec['avoid_locations']) > 2)
                                <span class="text-gray-500">+{{ count($rec['avoid_locations']) - 2 }} lainnya</span>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column - Recommendations & Alerts -->
        <div class="lg:col-span-1">
            <div class="sticky top-20 space-y-6">
                <!-- Health Alerts -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Alert Kesehatan</h3>
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @forelse($healthAlerts as $alert)
                        <div class="border-l-4 
                            @if($alert['severity'] === 'critical') border-red-500 bg-red-50
                            @else border-yellow-500 bg-yellow-50 @endif pl-4 py-2">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium 
                                        @if($alert['severity'] === 'critical') text-red-800
                                        @else text-yellow-800 @endif">
                                        {{ $alert['message'] }}
                                    </p>
                                    <p class="text-xs 
                                        @if($alert['severity'] === 'critical') text-red-600
                                        @else text-yellow-600 @endif mt-1">
                                        {{ \Carbon\Carbon::parse($alert['timestamp'])->diffForHumans() }}
                                    </p>
                                </div>
                                <i class="fas fa-{{ $alert['type'] === 'heat' ? 'fire' : 'snowflake' }} 
                                    @if($alert['severity'] === 'critical') text-red-500
                                    @else text-yellow-500 @endif"></i>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <i class="fas fa-shield-alt text-green-500 text-2xl mb-2"></i>
                            <p class="text-sm text-gray-600">Tidak ada alert kesehatan saat ini</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- General Health Recommendations -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Rekomendasi Umum</h3>
                    <div class="space-y-4">
                        @foreach($recommendations as $rec)
                        <div class="border-l-4 
                            @if($rec['priority'] === 'high') border-red-500
                            @elseif($rec['priority'] === 'medium') border-yellow-500
                            @else border-blue-500 @endif pl-4">
                            <h4 class="font-semibold text-gray-900 text-sm mb-1">{{ $rec['title'] }}</h4>
                            <p class="text-xs text-gray-600 mb-2">{{ $rec['description'] }}</p>
                            <ul class="space-y-1">
                                @foreach($rec['actions'] as $action)
                                <li class="text-xs text-gray-700 flex items-start">
                                    <i class="fas fa-chevron-right text-gray-400 mr-2 mt-1 text-xs"></i>
                                    {{ $action }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Health Tips -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Tips Kesehatan</h3>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-tint text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Hidrasi</h4>
                                <p class="text-xs text-gray-600">Minum air minimal 8 gelas per hari, lebih banyak saat cuaca panas</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-leaf text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Pakaian</h4>
                                <p class="text-xs text-gray-600">Gunakan pakaian berbahan ringan dan berwarna terang</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-sun text-yellow-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Perlindungan</h4>
                                <p class="text-xs text-gray-600">Gunakan sunscreen dan topi saat beraktivitas outdoor</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-purple-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Waktu Aktivitas</h4>
                                <p class="text-xs text-gray-600">Hindari aktivitas berat saat suhu tinggi (10:00-16:00)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contacts -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-red-900 mb-4">Kontak Darurat</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-red-800">Klinik Kampus:</span>
                            <a href="tel:+628123456789" class="text-sm font-medium text-red-600 hover:text-red-800">
                                <i class="fas fa-phone mr-1"></i>0812-3456-789
                            </a>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-red-800">Ambulans:</span>
                            <a href="tel:119" class="text-sm font-medium text-red-600 hover:text-red-800">
                                <i class="fas fa-ambulance mr-1"></i>119
                            </a>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-red-800">Security:</span>
                            <a href="tel:+628111222333" class="text-sm font-medium text-red-600 hover:text-red-800">
                                <i class="fas fa-shield-alt mr-1"></i>0811-1222-333
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Aksi Cepat</h3>
                    <div class="space-y-2">
                        <button onclick="window.print()" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-print mr-2"></i>Cetak Laporan Kesehatan
                        </button>
                        <button onclick="shareHealthStatus()" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-share mr-2"></i>Bagikan Status Kesehatan
                        </button>
                        <a href="{{ route('dashboard') }}" class="w-full bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors text-center block">
                            <i class="fas fa-tachometer-alt mr-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Health trends data
    const healthTrends = @json($healthTrends);

    // Health Trend Chart
    const trendCtx = document.getElementById('healthTrendChart').getContext('2d');
    const healthTrendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: []
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Skor Kesehatan'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Tanggal'
                    }
                }
            }
        }
    });

    // Populate health trend chart
    function populateHealthTrendChart() {
        const colors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#06B6D4'];
        let colorIndex = 0;
        
        const datasets = [];
        const allDates = new Set();
        
        Object.keys(healthTrends).forEach(location => {
            const data = healthTrends[location].data;
            data.forEach(item => allDates.add(item.date));
            
            datasets.push({
                label: healthTrends[location].name,
                data: data.map(item => ({x: item.date, y: item.health_score})),
                borderColor: colors[colorIndex % colors.length],
                backgroundColor: colors[colorIndex % colors.length] + '20',
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6
            });
            colorIndex++;
        });
        
        healthTrendChart.data.labels = Array.from(allDates).sort();
        healthTrendChart.data.datasets = datasets;
        healthTrendChart.update();
    }

    // Real-time health data update
    async function updateHealthData() {
        try {
            const response = await fetch('/api/health/realtime');
            const data = await response.json();
            
            // Update campus health score
            updateCampusHealthDisplay(data.campus_health);
            
            // Show new alerts if any
            if (data.alerts.length > 0) {
                showHealthAlert(data.alerts[0]);
            }
            
        } catch (error) {
            console.error('Error fetching health data:', error);
        }
    }

    function updateCampusHealthDisplay(campusHealth) {
        // Update score if element exists
        const scoreElement = document.querySelector('.campus-health-score');
        if (scoreElement) {
            scoreElement.textContent = campusHealth.score;
        }
    }

    function showHealthAlert(alert) {
        // Show browser notification if permissions granted
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Alert Kesehatan!', {
                body: alert.message,
                icon: '/favicon.ico',
                tag: 'health-alert'
            });
        }
    }

    // Share health status
    function shareHealthStatus() {
        const campusHealth = @json($campusHealth);
        const shareText = `Status Kesehatan Kampus: ${campusHealth.level} (${campusHealth.score}/100)\n${campusHealth.description}`;
        
        if (navigator.share) {
            navigator.share({
                title: 'Status Kesehatan Kampus',
                text: shareText,
                url: window.location.href
            });
        } else {
            // Fallback - copy to clipboard
            navigator.clipboard.writeText(shareText).then(() => {
                alert('Status kesehatan telah disalin ke clipboard');
            });
        }
    }

    // Heat stress warning system
    function checkHeatStress() {
        const highRiskLocations = @json(collect($healthData)->where('health_status.score', '<', 40)->count());
        
        if (highRiskLocations > 0) {
            const warningMessage = `Peringatan: ${highRiskLocations} area berisiko heat stress!`;
            
            // Show warning banner
            const warningBanner = document.createElement('div');
            warningBanner.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            warningBanner.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="font-medium">${warningMessage}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-red-200 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(warningBanner);
            
            // Auto remove after 10 seconds
            setTimeout(() => {
                if (warningBanner.parentElement) {
                    warningBanner.remove();
                }
            }, 10000);
        }
    }

    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        populateHealthTrendChart();
        checkHeatStress();
        
        // Update health data every 2 minutes
        setInterval(updateHealthData, 120000);
        
        // Check heat stress every 5 minutes
        setInterval(checkHeatStress, 300000);
    });
</script>
@endpush