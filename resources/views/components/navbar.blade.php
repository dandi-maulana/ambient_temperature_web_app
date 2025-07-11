<!-- Header Navbar - Fixed Position -->
<header class="bg-white shadow-sm fixed top-0 left-0 right-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
               <div
                    class="bg-blue-500 text-white w-12 h-12 rounded-full flex items-center justify-center text-sm font-bold">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/LOGO_UMA.png" alt="Profile"
                        class="w-full h-full rounded-full object-cover">
                </div>
                <span class="ml-3 text-lg font-semibold text-gray-800">Pemantauan Suhu Luar Ruangan</span>
            </div>
            <nav class="flex space-x-8">
                <a href="{{ route('dashboard') }}" class="{{ Request::routeIs('dashboard*') ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-600 hover:text-gray-900' }} text-sm font-medium transition-colors duration-200">Dashboard</a>
                <a href="{{ route('lokasi.index') }}" class="{{ Request::routeIs('lokasi*') ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-600 hover:text-gray-900' }} text-sm font-medium transition-colors duration-200">Lokasi</a>
                <a href="#" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors duration-200">Laporan</a>
                <a href="#" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors duration-200">Kesehatan</a>
            </nav>
        </div>
    </div>
</header>


