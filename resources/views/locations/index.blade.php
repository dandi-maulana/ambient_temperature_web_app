@extends('layouts.app')

@section('title', 'Lokasi - Pemantauan Suhu Luar Ruangan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Lokasi Pemantauan</h1>
        <p class="mt-2 text-gray-600">Monitor detail suhu di berbagai lokasi kampus secara real-time</p>
    </div>

    <!-- Location Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($locationsData as $key => $location)
        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden {{ $location['border'] }} border-l-4">
            <!-- Location Header -->
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 {{ $location['bg'] }} rounded-lg flex items-center justify-center">
                            <i class="{{ $location['icon'] }} {{ $location['text'] }} text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $location['name'] }}</h3>
                            <p class="text-sm text-gray-500">{{ $location['device_id'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Current Temperature -->
                <div class="mb-4">
                    <div class="flex items-baseline">
                        <span class="text-3xl font-bold {{ $location['text'] }}">{{ number_format($location['current_temperature'], 1) }}</span>
                        <span class="text-lg text-gray-500 ml-1">°C</span>
                        <span class="ml-3 px-2 py-1 text-xs font-medium {{ $location['bg'] }} {{ $location['text'] }} rounded-full">
                            {{ $location['status'] }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        Terakhir update: {{ $location['last_update'] ? \Carbon\Carbon::parse($location['last_update'])->diffForHumans() : 'Tidak ada data' }}
                    </p>
                </div>

                <!-- Location Info -->
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-users w-4 h-4 mr-2"></i>
                        <span>Kapasitas: {{ $location['capacity'] }}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-clock w-4 h-4 mr-2"></i>
                        <span>{{ $location['operational_hours'] }}</span>
                    </div>
                </div>

                <!-- Description -->
                <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $location['description'] }}</p>

                <!-- Action Button -->
                <a href="{{ route('lokasi.detail', $key) }}" 
                   class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-chart-line mr-2"></i>
                    Lihat Detail
                </a>
            </div>

            <!-- Quick Stats -->
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                <div class="flex justify-between text-xs text-gray-500">
                    <span>Status Sensor</span>
                    <span class="flex items-center">
                        <div class="w-2 h-2 bg-green-400 rounded-full mr-1"></div>
                        Online
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Summary Statistics -->
    <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Ringkasan Sistem</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ count($locationsData) }}</div>
                <div class="text-sm text-gray-600">Total Lokasi</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">
                    {{ collect($locationsData)->where('status', 'Normal')->count() }}
                </div>
                <div class="text-sm text-gray-600">Status Normal</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">
                    {{ collect($locationsData)->where('status', 'Sedang')->count() }}
                </div>
                <div class="text-sm text-gray-600">Perlu Perhatian</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600">
                    {{ collect($locationsData)->where('status', 'Tinggi')->count() }}
                </div>
                <div class="text-sm text-gray-600">Status Tinggi</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto refresh every 30 seconds
    setInterval(() => {
        window.location.reload();
    }, 30000);
</script>
@endpush
@endsection