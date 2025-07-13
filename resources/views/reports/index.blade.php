@extends('layouts.app')

@section('title', 'Laporan - Pemantauan Suhu Luar Ruangan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Laporan Suhu</h1>
        <p class="mt-2 text-gray-600">Analisis data suhu dan laporan komprehensif</p>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('laporan.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Date Range -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ $startDate }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           value="{{ $endDate }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Location Filter -->
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                    <select id="location" 
                            name="location" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all" {{ $selectedLocation === 'all' ? 'selected' : '' }}>Semua Lokasi</option>
                        @foreach($reportData['locations'] as $key => $location)
                            <option value="{{ $key }}" {{ $selectedLocation === $key ? 'selected' : '' }}>
                                {{ $location['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Report Type -->
                <div>
                    <label for="report_type" class="block text-sm font-medium text-gray-700 mb-1">Jenis Laporan</label>
                    <select id="report_type" 
                            name="report_type" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="summary" {{ $reportType === 'summary' ? 'selected' : '' }}>Ringkasan</option>
                        <option value="detailed" {{ $reportType === 'detailed' ? 'selected' : '' }}>Detail</option>
                        <option value="analytics" {{ $reportType === 'analytics' ? 'selected' : '' }}>Analitik</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>Tampilkan Laporan
                </button>

                <!-- Export Buttons -->
                <div class="flex gap-2">
                    <button type="button" 
                            onclick="exportData('csv')" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        <i class="fas fa-file-csv mr-2"></i>Export CSV
                    </button>
                    
                    <button type="button" 
                            onclick="exportData('json')" 
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        <i class="fas fa-file-code mr-2"></i>Export JSON
                    </button>
                    
                    <button type="button" 
                            onclick="exportData('pdf')" 
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        <i class="fas fa-file-pdf mr-2"></i>Export PDF
                    </button>
                </div>

                <!-- Quick Date Presets -->
                <div class="flex gap-2 ml-auto">
                    <button type="button" onclick="setDateRange('today')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Hari Ini</button>
                    <button type="button" onclick="setDateRange('week')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">7 Hari</button>
                    <button type="button" onclick="setDateRange('month')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">30 Hari</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-database text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ number_format($reportData['statistics']['total_readings']) }}</h3>
                    <p class="text-sm text-gray-600">Total Pembacaan</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-thermometer-half text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $reportData['statistics']['avg_temperature'] }}°C</h3>
                    <p class="text-sm text-gray-600">Rata-rata Suhu</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <i class="fas fa-thermometer-full text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $reportData['statistics']['max_temperature'] }}°C</h3>
                    <p class="text-sm text-gray-600">Suhu Tertinggi</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $reportData['statistics']['high_readings'] }}</h3>
                    <p class="text-sm text-gray-600">Alert Suhu Tinggi</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Charts -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Temperature Trend Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Tren Suhu Harian</h2>
                <div class="h-80">
                    <canvas id="temperatureTrendChart"></canvas>
                </div>
            </div>

            <!-- Hourly Average Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Rata-rata Suhu per Jam</h2>
                <div class="h-64">
                    <canvas id="hourlyAverageChart"></canvas>
                </div>
            </div>

            <!-- Summary Table -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Ringkasan per Lokasi</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($reportData['summary'] as $key => $summary)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $summary['name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $summary['avg_temp'] }}°C
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $summary['min_temp'] }}°C
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $summary['max_temp'] }}°C
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        @if($summary['status']['color'] === 'red') bg-red-100 text-red-800
                                        @elseif($summary['status']['color'] === 'orange') bg-orange-100 text-orange-800
                                        @elseif($summary['status']['color'] === 'green') bg-green-100 text-green-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ $summary['status']['status'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column - Alerts & Info -->
        <div class="lg:col-span-1">
            <div class="sticky top-20 space-y-6">
                <!-- Temperature Distribution -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Distribusi Suhu</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-600">Normal (25-34°C)</span>
                            </div>
                            <span class="text-sm font-medium">{{ number_format($reportData['statistics']['normal_readings']) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-600">Tinggi (≥35°C)</span>
                            </div>
                            <span class="text-sm font-medium">{{ number_format($reportData['statistics']['high_readings']) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-600">Rendah (<25°C)</span>
                            </div>
                            <span class="text-sm font-medium">{{ number_format($reportData['statistics']['low_readings']) }}</span>
                        </div>
                    </div>
                    
                    <!-- Distribution Chart -->
                    <div class="mt-4 h-32">
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>

                <!-- Recent Alerts -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Alert Terbaru</h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @php $totalAlerts = 0; @endphp
                        @foreach($reportData['alerts'] as $location => $alerts)
                            @php $totalAlerts += $alerts['total_alerts']; @endphp
                            @if($alerts['total_alerts'] > 0)
                                <div class="border-l-4 border-yellow-400 pl-4">
                                    <h4 class="font-medium text-gray-900">{{ $alerts['location_name'] }}</h4>
                                    @if($alerts['high_temp']->count() > 0)
                                        <p class="text-sm text-red-600">
                                            <i class="fas fa-thermometer-full mr-1"></i>
                                            {{ $alerts['high_temp']->count() }} alert suhu tinggi
                                        </p>
                                    @endif
                                    @if($alerts['low_temp']->count() > 0)
                                        <p class="text-sm text-blue-600">
                                            <i class="fas fa-thermometer-empty mr-1"></i>
                                            {{ $alerts['low_temp']->count() }} alert suhu rendah
                                        </p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                        
                        @if($totalAlerts === 0)
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
                                <p class="text-sm text-gray-600">Tidak ada alert dalam periode ini</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Report Info -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Info Laporan</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Periode:</span>
                            <span class="font-medium">{{ $startDate }} - {{ $endDate }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Lokasi:</span>
                            <span class="font-medium">
                                @if($selectedLocation === 'all')
                                    Semua Lokasi
                                @else
                                    {{ $reportData['locations'][$selectedLocation]['name'] }}
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Jenis Laporan:</span>
                            <span class="font-medium capitalize">{{ $reportType }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Dibuat:</span>
                            <span class="font-medium">{{ now()->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Aksi Cepat</h3>
                    <div class="space-y-2">
                        <button onclick="printReport()" class="w-full bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-print mr-2"></i>Cetak Laporan
                        </button>
                        <button onclick="scheduleReport()" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-calendar mr-2"></i>Jadwalkan Laporan
                        </button>
                        <a href="{{ route('dashboard') }}" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors text-center block">
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
    // Chart data from backend
    const chartData = @json($reportData['charts']);
    const statistics = @json($reportData['statistics']);

    // Temperature Trend Chart
    const trendCtx = document.getElementById('temperatureTrendChart').getContext('2d');
    const trendChart = new Chart(trendCtx, {
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

    // Hourly Average Chart
    const hourlyCtx = document.getElementById('hourlyAverageChart').getContext('2d');
    const hourlyChart = new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: Array.from({length: 24}, (_, i) => i.toString().padStart(2, '0') + ':00'),
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

    // Distribution Chart (Doughnut)
    const distributionCtx = document.getElementById('distributionChart').getContext('2d');
    const distributionChart = new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Normal', 'Tinggi', 'Rendah'],
            datasets: [{
                data: [statistics.normal_readings, statistics.high_readings, statistics.low_readings],
                backgroundColor: ['#10B981', '#EF4444', '#3B82F6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Populate charts with data
    function populateCharts() {
        // Temperature trend
        const colors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#06B6D4'];
        let colorIndex = 0;
        
        const trendDatasets = [];
        const allDates = new Set();
        
        Object.keys(chartData.temperature_trend).forEach(location => {
            const data = chartData.temperature_trend[location];
            data.forEach(item => allDates.add(item.date));
            
            trendDatasets.push({
                label: getLocationName(location),
                data: data.map(item => ({x: item.date, y: parseFloat(item.avg_temp)})),
                borderColor: colors[colorIndex % colors.length],
                backgroundColor: colors[colorIndex % colors.length] + '20',
                tension: 0.4
            });
            colorIndex++;
        });
        
        trendChart.data.labels = Array.from(allDates).sort();
        trendChart.data.datasets = trendDatasets;
        trendChart.update();

        // Hourly average
        const hourlyDatasets = [];
        colorIndex = 0;
        
        Object.keys(chartData.hourly_average).forEach(location => {
            const data = chartData.hourly_average[location];
            const hourlyData = Array.from({length: 24}, (_, i) => {
                const hourData = data.find(item => item.hour === i);
                return hourData ? parseFloat(hourData.avg_temp) : null;
            });
            
            hourlyDatasets.push({
                label: getLocationName(location),
                data: hourlyData,
                backgroundColor: colors[colorIndex % colors.length] + '80',
                borderColor: colors[colorIndex % colors.length],
                borderWidth: 1
            });
            colorIndex++;
        });
        
        hourlyChart.data.datasets = hourlyDatasets;
        hourlyChart.update();
    }

    function getLocationName(key) {
        const locations = @json($reportData['locations']);
        return locations[key] ? locations[key].name : key;
    }

    // Export functions
    function exportData(format) {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = '{{ route("laporan.export") }}';
        
        const params = {
            start_date: document.getElementById('start_date').value,
            end_date: document.getElementById('end_date').value,
            location: document.getElementById('location').value,
            format: format
        };
        
        Object.keys(params).forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = params[key];
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    // Date range presets
    function setDateRange(preset) {
        const endDate = new Date();
        const startDate = new Date();
        
        switch(preset) {
            case 'today':
                break;
            case 'week':
                startDate.setDate(endDate.getDate() - 7);
                break;
            case 'month':
                startDate.setDate(endDate.getDate() - 30);
                break;
        }
        
        document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
        document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
    }

    // Quick actions
    function printReport() {
        window.print();
    }

    function scheduleReport() {
        alert('Fitur penjadwalan laporan akan segera tersedia');
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        populateCharts();
        
        // Auto-refresh every 5 minutes for real-time data
        setInterval(() => {
            if (document.getElementById('location').value !== 'all' || 
                document.getElementById('end_date').value === new Date().toISOString().split('T')[0]) {
                location.reload();
            }
        }, 300000);
    });
</script>
@endpush