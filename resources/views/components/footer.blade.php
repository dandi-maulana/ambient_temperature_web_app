<!-- Footer -->
<footer class="bg-white border-t border-gray-200 py-6 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="text-sm text-gray-600 mb-4 md:mb-0">
                © 2025 Pemantauan Suhu Luar Ruangan. All rights reserved.
            </div>
            <div class="flex space-x-6">
                <a href="#" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fab fa-twitter text-lg"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fab fa-facebook text-lg"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fab fa-instagram text-lg"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fab fa-linkedin text-lg"></i>
                </a>
            </div>
        </div>
        
        <!-- Additional Footer Info -->
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center text-xs text-gray-500">
                <div>
                    <span>🌡️ Sistem Monitoring Suhu Real-time</span>
                </div>
                <div class="mt-2 md:mt-0">
                    <span>Last updated: <span id="footer-timestamp">{{ now()->format('d/m/Y H:i') }}</span></span>
                </div>
            </div>
        </div>
    </div>
</footer>