@extends('layouts.app')

@section('title', $locationInfo['name'] . ' - Detail Lokasi')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-blue-600">Dashboard</a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="{{ route('lokasi.index') }}" class="text-gray-600 hover:text-blue-600">Lokasi</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-gray-500">{{ $locationInfo['name'] }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-16 h-16 {{ $locationInfo['bg'] }} rounded-xl flex items-center justify-center mr-4">
                    <i class="{{ $locationInfo['icon'] }} {{ $locationInfo['text'] }} text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $locationInfo['name'] }}</h1>
                    <p class="text-gray-600">{{ $locationInfo['description'] }}</p>
                    <div class="flex items-center mt-2 space-x-4">
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-microchip mr-1"></i>{{ $locationInfo['device_id'] }}
                        </span>
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-clock mr-1"></i>{{ $locationInfo['operational_hours'] }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-4xl font-bold {{ $locationInfo['text'] }}">
                    <span id="current-temp">{{ number_format($locationInfo['current_temperature'], 1) }}</span>°C
                </div>
                <div class="flex items-center justify-end mt-2">
                    <span id="status-badge" class="px-3 py-1 text-sm font-medium {{ $locationInfo['bg'] }} {{ $locationInfo['text'] }} rounded-full">
                        {{ $locationInfo['status'] }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Update: <span id="last-update">{{ $locationInfo['last_update'] ? \Carbon\Carbon::parse($locationInfo['last_update'])->format('H:i:s') : '-' }}</span>
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Charts -->
        <div class="lg:col-span-2 space-y-6">
            <!-- 7 Days Trend Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Tren Suhu 7 Hari Terakhir</h2>
                <div style="height: 300px;">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>

            <!-- Today's Hourly Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Suhu Hari Ini (Per Jam)</h2>
                <div style="height: 250px;">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>

            <!-- Real-time Data Table -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Data Real-time Terbaru</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Suhu (°C)
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Perubahan
                                </th>
                            </tr>
                        </thead>
                        <tbody id="realtime-table" class="bg-white divide-y divide-gray-200">
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column - Statistics & Info -->
        <div class="space-y-6">
            <!-- Current Status -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Status Saat Ini</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Suhu Saat Ini</span>
                        <span id="current-display" class="font-bold {{ $locationInfo['text'] }}">{{ number_format($locationInfo['current_temperature'], 1) }}°C</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Status</span>
                        <span id="status-display" class="px-2 py-1 text-xs font-medium {{ $locationInfo['bg'] }} {{ $locationInfo['text'] }} rounded">
                            {{ $locationInfo['status'] }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Sensor</span>
                        <span class="flex items-center text-green-600">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            Online
                        </span>
                    </div>
                </div>
            </div>

            <!-- 7 Days Statistics -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Statistik 7 Hari</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Rata-rata</span>
                        <span class="font-bold text-blue-600">{{ $locationInfo['statistics']['avg_temp_7days'] }}°C</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Tertinggi</span>
                        <span class="font-bold text-red-600">{{ $locationInfo['statistics']['max_temp_7days'] }}°C</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Terendah</span>
                        <span class="font-bold text-blue-600">{{ $locationInfo['statistics']['min_temp_7days'] }}°C</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Pembacaan</span>
                        <span class="font-bold text-gray-800">{{ $locationInfo['statistics']['total_readings'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Location Details -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Detail Lokasi</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-600">Kapasitas</span>
                        <p class="text-gray-800">{{ $locationInfo['capacity'] }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Jam Operasional</span>
                        <p class="text-gray-800">{{ $locationInfo['operational_hours'] }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Device ID</span>
                        <p class="text-gray-800 font-mono text-sm">{{ $locationInfo['device_id'] }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Pembacaan Hari Ini</span>
                        <p class="text-gray-800">{{ $locationInfo['statistics']['today_readings'] }} kali</p>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Rekomendasi</h3>
                <div id="recommendations" class="space-y-3">
                    @if($locationInfo['current_temperature'] >= 35)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-red-600 mt-0.5 mr-2"></i>
                                <div>
                                    <p class="text-sm font-medium text-red-800">Suhu Sangat Tinggi</p>
                                    <p class="text-xs text-red-600 mt-1">Hindari aktivitas berat dan pastikan hidrasi yang cukup</p>
                                </div>
                            </div>
                        </div>
                    @elseif($locationInfo['current_temperature'] >= 30)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-circle text-yellow-600 mt-0.5 mr-2"></i>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800">Suhu Tinggi</p>
                                    <p class="text-xs text-yellow-600 mt-1">Disarankan minum air yang cukup dan istirahat di tempat teduh</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-600 mt-0.5 mr-2"></i>
                                <div>
                                    <p class="text-sm font-medium text-green-800">Kondisi Normal</p>
                                    <p class="text-xs text-green-600 mt-1">Suhu dalam kondisi nyaman untuk aktivitas</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-2">
                    <button onclick="exportData()" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-download mr-2"></i>Export Data
                    </button>
                    <button onclick="printReport()" class="w-full bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-print mr-2"></i>Cetak Laporan
                    </button>
                    <a href="{{ route('lokasi.index') }}" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-lg text-sm font-medium transition-colors text-center block">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Lokasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Chart configuration
    const chartData = @json($locationInfo['chartData']);
    const todayData = @json($locationInfo['todayData']);
    const locationKey = @json($locationInfo['key']);

    // Weekly trend chart
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    const weeklyChart = new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: chartData.map(item => new Date(item.date).toLocaleDateString('id-ID', { 
                month: 'short', 
                day: 'numeric' 
            })),
            datasets: [{
                label: 'Rata-rata',
                data: chartData.map(item => item.avg_temperature),
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Maksimum',
                data: chartData.map(item => item.max_temperature),
                borderColor: '#EF4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: false,
                tension: 0.4
            }, {
                label: 'Minimum',
                data: chartData.map(item => item.min_temperature),
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Suhu (°C)'
                    }
                }
            }
        }
    });

    // Hourly chart for today
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    const hourlyChart = new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: Array.from({length: 24}, (_, i) => i.toString().padStart(2, '0') + ':00'),
            datasets: [{
                label: 'Suhu (°C)',
                data: Array.from({length: 24}, (_, i) => {
                    const hourData = todayData.find(item => item.hour === i);
                    return hourData ? hourData.temperature : null;
                }),
                backgroundColor: function(context) {
                    const value = context.parsed.y;
                    if (value >= 35) return 'rgba(239, 68, 68, 0.7)';
                    if (value >= 30) return 'rgba(245, 158, 11, 0.7)';
                    if (value >= 25) return 'rgba(16, 185, 129, 0.7)';
                    return 'rgba(59, 130, 246, 0.7)';
                },
                borderColor: function(context) {
                    const value = context.parsed.y;
                    if (value >= 35) return '#EF4444';
                    if (value >= 30) return '#F59E0B';
                    if (value >= 25) return '#10B981';
                    return '#3B82F6';
                },
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Suhu (°C)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Jam'
                    }
                }
            }
        }
    });

    // Real-time updates
    const updateLocationData = async () => {
        try {
            const response = await fetch(`/api/location/${locationKey}/realtime`);
            const result = await response.json();
            
            // Update current temperature
            document.getElementById('current-temp').textContent = result.data.current_temperature;
            document.getElementById('current-display').textContent = result.data.current_temperature + '°C';
            
            // Update status
            const statusBadge = document.getElementById('status-badge');
            const statusDisplay = document.getElementById('status-display');
            statusBadge.textContent = result.data.status;
            statusDisplay.textContent = result.data.status;
            
            // Update last update time
            document.getElementById('last-update').textContent = new Date().toLocaleTimeString('id-ID');
            
            // Update recommendations
            updateRecommendations(result.data.current_temperature);
            
        } catch (error) {
            console.error('Error updating location data:', error);
        }
    };

    const updateRecommendations = (temperature) => {
        const recommendationsContainer = document.getElementById('recommendations');
        let recommendationHTML = '';
        
        if (temperature >= 35) {
            recommendationHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-600 mt-0.5 mr-2"></i>
                        <div>
                            <p class="text-sm font-medium text-red-800">Suhu Sangat Tinggi</p>
                            <p class="text-xs text-red-600 mt-1">Hindari aktivitas berat dan pastikan hidrasi yang cukup</p>
                        </div>
                    </div>
                </div>
            `;
        } else if (temperature >= 30) {
            recommendationHTML = `
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-yellow-600 mt-0.5 mr-2"></i>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Suhu Tinggi</p>
                            <p class="text-xs text-yellow-600 mt-1">Disarankan minum air yang cukup dan istirahat di tempat teduh</p>
                        </div>
                    </div>
                </div>
            `;
        } else {
            recommendationHTML = `
                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mt-0.5 mr-2"></i>
                        <div>
                            <p class="text-sm font-medium text-green-800">Kondisi Normal</p>
                            <p class="text-xs text-green-600 mt-1">Suhu dalam kondisi nyaman untuk aktivitas</p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        recommendationsContainer.innerHTML = recommendationHTML;
    };

    // Populate real-time table
    const populateRealtimeTable = () => {
        // This would typically fetch recent data from API
        // For now, we'll show placeholder data
        const tableBody = document.getElementById('realtime-table');
        const now = new Date();
        let tableHTML = '';
        
        for (let i = 0; i < 5; i++) {
            const time = new Date(now - (i * 5 * 60 * 1000)); // 5 minutes intervals
            const temp = {{ $locationInfo['current_temperature'] }} + (Math.random() * 2 - 1);
            const status = temp >= 35 ? 'Tinggi' : temp >= 30 ? 'Sedang' : 'Normal';
            const statusColor = temp >= 35 ? 'text-red-600' : temp >= 30 ? 'text-yellow-600' : 'text-green-600';
            const change = i === 0 ? 0 : (Math.random() * 1 - 0.5);
            const changeIcon = change > 0 ? 'fa-arrow-up text-red-600' : change < 0 ? 'fa-arrow-down text-blue-600' : 'fa-minus text-gray-600';
            
            tableHTML += `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${time.toLocaleTimeString('id-ID')}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        ${temp.toFixed(1)}°C
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColor} bg-opacity-10">
                            ${status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <i class="fas ${changeIcon} mr-1"></i>
                        ${Math.abs(change).toFixed(1)}°C
                    </td>
                </tr>
            `;
        }
        
        tableBody.innerHTML = tableHTML;
    };

    // Utility functions
    const exportData = () => {
        // Implementation for data export
        alert('Fitur export data akan segera tersedia');
    };

    const printReport = () => {
        window.print();
    };

    // Initialize
    populateRealtimeTable();
    
    // Start real-time updates every 10 seconds
    setInterval(updateLocationData, 10000);
    
    // Update table every 30 seconds
    setInterval(populateRealtimeTable, 30000);
</script>
@endpush
@endsection