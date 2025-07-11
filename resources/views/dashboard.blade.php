@extends('layouts.app')

@section('title', 'Dashboard - Pemantauan Suhu Luar Ruangan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column -->
        <div class="lg:col-span-2">
            <!-- Average Temperature -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 text-center mb-2">Rata-rata Suhu Luar Ruangan</h2>
                <div class="text-center">
                    <span id="average-temp" class="text-6xl font-bold text-green-500">{{ number_format($averageTemp, 1) }}</span>
                    <span class="text-2xl text-gray-600">°C</span>
                </div>
                <p class="text-center text-gray-600 mt-2">Rata-rata di semua lokasi yang dipantau, berdasarkan data sensor yang disimpan.</p>
                
                <!-- Chart -->
                <div class="mt-6">
                    <canvas id="temperatureChart" width="400" height="100"></canvas>
                </div>
                
                <!-- Dynamic Warning -->
                <div id="warning-message" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                        <span id="warning-text" class="text-sm text-yellow-800">Saat ini Suhu Area Kampus kurang sehat. Diharapkan Mahasiswa dan Pegawai minum air yang banyak.</span>
                    </div>
                </div>
            </div>

            <!-- Location Cards -->
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Lokasi</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Kantin Pertanian -->
                    <div id="card-kantin" class="bg-white rounded-lg shadow-sm p-4 {{ $data['kantin']['border'] }} border-l-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-800">Kantin Pertanian</h4>
                            <i id="icon-kantin" class="{{ $data['kantin']['icon'] }} {{ $data['kantin']['text'] }}"></i>
                        </div>
                        <div id="temp-kantin" class="text-3xl font-bold {{ $data['kantin']['text'] }} mb-1">{{ number_format($data['kantin']['nilai_temperatur'], 1) }}<span class="text-sm">°C</span></div>
                        <div id="status-kantin" class="text-sm text-gray-600 mb-3">Status: {{ $data['kantin']['status'] }}</div>
                        <div class="h-12 mb-3">
                            <canvas id="chart-kantin" width="100" height="30"></canvas>
                        </div>
                        <button onclick="window.location.href='{{ route('lokasi.detail', 'kantin') }}'" class="w-full bg-gray-100 text-gray-700 py-2 rounded text-sm hover:bg-gray-200">
                            <i class="fas fa-chart-line mr-1"></i> Detail
                        </button>
                    </div>

                    <!-- Lapangan Futsal -->
                    <div id="card-lapangan" class="bg-white rounded-lg shadow-sm p-4 {{ $data['lapangan']['border'] }} border-l-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-800">Lapangan Futsal</h4>
                            <i id="icon-lapangan" class="{{ $data['lapangan']['icon'] }} {{ $data['lapangan']['text'] }}"></i>
                        </div>
                        <div id="temp-lapangan" class="text-3xl font-bold {{ $data['lapangan']['text'] }} mb-1">{{ number_format($data['lapangan']['nilai_temperatur'], 1) }}<span class="text-sm">°C</span></div>
                        <div id="status-lapangan" class="text-sm text-gray-600 mb-3">Status: {{ $data['lapangan']['status'] }}</div>
                        <div class="h-12 mb-3">
                            <canvas id="chart-lapangan" width="100" height="30"></canvas>
                        </div>
                        <button onclick="window.location.href='{{ route('lokasi.detail', 'lapangan') }}'" class="w-full bg-gray-100 text-gray-700 py-2 rounded text-sm hover:bg-gray-200">
                            <i class="fas fa-chart-line mr-1"></i> Detail
                        </button>
                    </div>

                    <!-- Parkiran -->
                    <div id="card-parkiran" class="bg-white rounded-lg shadow-sm p-4 {{ $data['parkiran']['border'] }} border-l-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-800">Parkiran</h4>
                            <i id="icon-parkiran" class="{{ $data['parkiran']['icon'] }} {{ $data['parkiran']['text'] }}"></i>
                        </div>
                        <div id="temp-parkiran" class="text-3xl font-bold {{ $data['parkiran']['text'] }} mb-1">{{ number_format($data['parkiran']['nilai_temperatur'], 1) }}<span class="text-sm">°C</span></div>
                        <div id="status-parkiran" class="text-sm text-gray-600 mb-3">Status: {{ $data['parkiran']['status'] }}</div>
                        <div class="h-12 mb-3">
                            <canvas id="chart-parkiran" width="100" height="30"></canvas>
                        </div>
                        <button onclick="window.location.href='{{ route('lokasi.detail', 'parkiran') }}'" class="w-full bg-gray-100 text-gray-700 py-2 rounded text-sm hover:bg-gray-200">
                            <i class="fas fa-chart-line mr-1"></i> Detail
                        </button>
                    </div>

                    <!-- Foodcourt -->
                    <div id="card-food_court" class="bg-white rounded-lg shadow-sm p-4 {{ $data['food_court']['border'] }} border-l-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-800">Foodcourt</h4>
                            <i id="icon-food_court" class="{{ $data['food_court']['icon'] }} {{ $data['food_court']['text'] }}"></i>
                        </div>
                        <div id="temp-food_court" class="text-3xl font-bold {{ $data['food_court']['text'] }} mb-1">{{ number_format($data['food_court']['nilai_temperatur'], 1) }}<span class="text-sm">°C</span></div>
                        <div id="status-food_court" class="text-sm text-gray-600 mb-3">Status: {{ $data['food_court']['status'] }}</div>
                        <div class="h-12 mb-3">
                            <canvas id="chart-foodcourt" width="100" height="30"></canvas>
                        </div>
                        <button onclick="window.location.href='{{ route('lokasi.detail', 'food_court') }}'" class="w-full bg-gray-100 text-gray-700 py-2 rounded text-sm hover:bg-gray-200">
                            <i class="fas fa-chart-line mr-1"></i> Detail
                        </button>
                    </div>

                    <!-- Taman -->
                    <div id="card-taman" class="bg-white rounded-lg shadow-sm p-4 {{ $data['taman']['border'] }} border-l-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-800">Taman</h4>
                            <i id="icon-taman" class="{{ $data['taman']['icon'] }} {{ $data['taman']['text'] }}"></i>
                        </div>
                        <div id="temp-taman" class="text-3xl font-bold {{ $data['taman']['text'] }} mb-1">{{ number_format($data['taman']['nilai_temperatur'], 1) }}<span class="text-sm">°C</span></div>
                        <div id="status-taman" class="text-sm text-gray-600 mb-3">Status: {{ $data['taman']['status'] }}</div>
                        <div class="h-12 mb-3">
                            <canvas id="chart-taman" width="100" height="30"></canvas>
                        </div>
                        <button onclick="window.location.href='{{ route('lokasi.detail', 'taman') }}'" class="w-full bg-gray-100 text-gray-700 py-2 rounded text-sm hover:bg-gray-200">
                            <i class="fas fa-chart-line mr-1"></i> Detail
                        </button>
                    </div>

                    <!-- Suhu Real-Time -->
                    <div id="card-real_time" class="bg-white rounded-lg shadow-sm p-4 {{ $data['real_time']['border'] }} border-l-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-800">Suhu Real-Time</h4>
                            <i id="icon-real_time" class="{{ $data['real_time']['icon'] }} {{ $data['real_time']['text'] }}"></i>
                        </div>
                        <div id="temp-real_time" class="text-3xl font-bold {{ $data['real_time']['text'] }} mb-1">{{ number_format($data['real_time']['nilai_temperatur'], 1) }}<span class="text-sm">°C</span></div>
                        <div id="status-real_time" class="text-sm text-gray-600 mb-3">Status: {{ $data['real_time']['status'] }}</div>
                        <div class="h-12 mb-3">
                            <canvas id="chart-realtime" width="100" height="30"></canvas>
                        </div>
                        <button onclick="window.location.href='{{ route('lokasi.detail', 'real_time') }}'" class="w-full bg-gray-100 text-gray-700 py-2 rounded text-sm hover:bg-gray-200">
                            <i class="fas fa-chart-line mr-1"></i> Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-1">
            <!-- Informasi -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi</h3>
                
                <!-- Suhu Tertinggi -->
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-thermometer-full text-red-500 mr-2"></i>
                        <span class="text-sm text-gray-600">Suhu Tertinggi Tercatat</span>
                    </div>
                    <div id="highest-temp" class="text-2xl font-bold text-red-500">{{ number_format($highestTemp, 1) }}°C</div>
                    <div class="text-xs text-gray-500">Kemarin pukul 15:15</div>
                </div>

                <!-- Suhu Terendah -->
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-thermometer-empty text-blue-500 mr-2"></i>
                        <span class="text-sm text-gray-600">Suhu Terendah Tercatat</span>
                    </div>
                    <div id="lowest-temp" class="text-2xl font-bold text-blue-500">{{ number_format($lowestTemp, 1) }}°C</div>
                    <div class="text-xs text-gray-500">Kemarin pukul 17:30</div>
                </div>
            </div>

            <!-- Laporan Berdasarkan Lokasi -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Laporan Berdasarkan Lokasi</h3>
                
                <!-- Dynamic Alerts -->
                <div id="reports-container" class="space-y-3">
                    @foreach($reports as $report)
                    <div class="{{ $report['bg'] }} border {{ $report['border'] }} rounded-lg p-3">
                        <div class="flex items-center">
                            <i class="{{ $report['icon'] }} text-{{ $report['color'] }}-600 mr-2 text-sm"></i>
                            <div>
                                <div class="text-sm font-medium {{ $report['text'] }}">{{ $report['message'] }}</div>
                                <div class="text-xs text-{{ $report['color'] }}-600">{{ $report['time'] }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Main Temperature Chart
    const ctx = document.getElementById('temperatureChart').getContext('2d');
    const chartData = @json($chartData);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => new Date(item.date).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' })),
            datasets: [{
                data: chartData.map(item => item.temperature),
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                borderWidth: 2
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
                x: {
                    display: false
                },
                y: {
                    display: false
                }
            }
        }
    });

    // Color mapping for different temperature statuses
    const getColorByTemperature = (temp) => {
        if (temp >= 35) return { color: '#EF4444', border: 'border-red-500', text: 'text-red-500' };
        if (temp >= 30) return { color: '#F59E0B', border: 'border-orange-500', text: 'text-orange-500' };
        if (temp >= 25) return { color: '#10B981', border: 'border-green-500', text: 'text-green-500' };
        return { color: '#3B82F6', border: 'border-blue-500', text: 'text-blue-500' };
    };

    // Mini charts for each location
    const createMiniChart = (canvasId, color) => {
        const ctx = document.getElementById(canvasId).getContext('2d');
        const data = Array.from({length: 20}, () => Math.random() * 10 + 25);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map((_, i) => i),
                datasets: [{
                    data: data,
                    borderColor: color,
                    backgroundColor: color + '20',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
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
                    x: {
                        display: false
                    },
                    y: {
                        display: false
                    }
                }
            }
        });
    };

    // Create mini charts with initial colors
    const initialData = @json($data);
    Object.keys(initialData).forEach(location => {
        const temp = initialData[location].nilai_temperatur;
        const colors = getColorByTemperature(temp);
        const chartId = location === 'real_time' ? 'chart-realtime' : 
                       location === 'food_court' ? 'chart-foodcourt' : 
                       `chart-${location}`;
        createMiniChart(chartId, colors.color);
    });

    // Real-time update function
    const updateDashboard = (data) => {
        // Update average temperature
        const avgColors = getColorByTemperature(data.averageTemp);
        document.getElementById('average-temp').textContent = data.averageTemp;
        document.getElementById('average-temp').className = `text-6xl font-bold ${avgColors.text}`;

        // Update highest and lowest temperatures
        document.getElementById('highest-temp').textContent = data.highestTemp + '°C';
        document.getElementById('lowest-temp').textContent = data.lowestTemp + '°C';

        // Update location cards
        Object.keys(data.data).forEach(location => {
            const locationData = data.data[location];
            const colors = getColorByTemperature(locationData.nilai_temperatur);
            
            // Update temperature display
            const tempElement = document.getElementById(`temp-${location}`);
            if (tempElement) {
                tempElement.innerHTML = `${locationData.nilai_temperatur}<span class="text-sm">°C</span>`;
                tempElement.className = `text-3xl font-bold ${colors.text} mb-1`;
            }

            // Update status
            const statusElement = document.getElementById(`status-${location}`);
            if (statusElement) {
                statusElement.textContent = `Status: ${locationData.status}`;
            }

            // Update icon
            const iconElement = document.getElementById(`icon-${location}`);
            if (iconElement) {
                iconElement.className = `${locationData.icon} ${colors.text}`;
            }

            // Update card border
            const cardElement = document.getElementById(`card-${location}`);
            if (cardElement) {
                cardElement.className = cardElement.className.replace(/border-\w+-500/, colors.border);
            }
        });

        // Update warning message based on average temperature
        const warningElement = document.getElementById('warning-message');
        const warningText = document.getElementById('warning-text');
        
        if (data.averageTemp >= 35) {
            warningElement.className = 'bg-red-50 border border-red-200 rounded-lg p-4 mt-6';
            warningText.className = 'text-sm text-red-800';
            warningText.textContent = 'PERINGATAN CUACA EKSTREM! Suhu sangat tinggi. Hindari aktivitas luar ruangan dan pastikan minum air yang cukup.';
        } else if (data.averageTemp >= 32) {
            warningElement.className = 'bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6';
            warningText.className = 'text-sm text-yellow-800';
            warningText.textContent = 'Saat ini Suhu Area Kampus cukup tinggi. Diharapkan Mahasiswa dan Pegawai minum air yang banyak.';
        } else {
            warningElement.className = 'bg-green-50 border border-green-200 rounded-lg p-4 mt-6';
            warningText.className = 'text-sm text-green-800';
            warningText.textContent = 'Cuaca saat ini dalam kondisi nyaman untuk aktivitas di luar ruangan.';
        }

        // Update reports
        updateReports(data.reports);
    };

    // Update reports function
    const updateReports = (reports) => {
        const reportsContainer = document.getElementById('reports-container');
        reportsContainer.innerHTML = '';

        reports.forEach(report => {
            const reportDiv = document.createElement('div');
            reportDiv.className = `${report.bg} border ${report.border} rounded-lg p-3`;
            reportDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="${report.icon} text-${report.color}-600 mr-2 text-sm"></i>
                    <div>
                        <div class="text-sm font-medium ${report.text}">${report.message}</div>
                        <div class="text-xs text-${report.color}-600">${report.time}</div>
                    </div>
                </div>
            `;
            reportsContainer.appendChild(reportDiv);
        });
    };

    // Fetch real-time data
    const fetchRealtimeData = async () => {
        try {
            const response = await fetch('/api/realtime-data');
            const data = await response.json();
            updateDashboard(data);
            
            // Show notification for significant changes
            showNotificationIfNeeded(data);
        } catch (error) {
            console.error('Error fetching real-time data:', error);
        }
    };

    // Show notification for significant temperature changes
    let lastNotificationTime = 0;
    const showNotificationIfNeeded = (data) => {
        const now = Date.now();
        const timeSinceLastNotification = now - lastNotificationTime;
        
        // Only show notification every 30 seconds
        if (timeSinceLastNotification < 30000) return;

        const dangerousLocations = Object.keys(data.data).filter(location => 
            data.data[location].nilai_temperatur >= 35
        );

        if (dangerousLocations.length > 0) {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('Peringatan Suhu Tinggi!', {
                    body: `${dangerousLocations.length} lokasi memiliki suhu di atas 35°C`,
                    icon: '/favicon.ico'
                });
            }
            lastNotificationTime = now;
        }
    };

    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Start real-time updates (every 5 seconds)
    setInterval(fetchRealtimeData, 5000);

    // Add visual indicator for real-time updates
    const addUpdateIndicator = () => {
        const indicator = document.createElement('div');
        indicator.className = 'fixed top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm opacity-0 transition-opacity duration-300';
        indicator.textContent = 'Data diperbarui';
        document.body.appendChild(indicator);

        // Show indicator
        setTimeout(() => indicator.style.opacity = '1', 100);
        setTimeout(() => indicator.style.opacity = '0', 2000);
        setTimeout(() => document.body.removeChild(indicator), 2300);
    };

    // Show update indicator on data fetch
    const originalFetch = fetchRealtimeData;
    fetchRealtimeData = async () => {
        await originalFetch();
        addUpdateIndicator();
    };
</script>
@endpush