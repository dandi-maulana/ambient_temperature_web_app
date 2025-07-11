<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pemantauan Suhu Luar Ruangan')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Include Fixed Navbar -->
    @include('components.navbar')

    <!-- Main Content with Top Padding -->
    <main class="pt-16">
        @yield('content')
    </main>

    <!-- Include Footer -->
    @include('components.footer')

    @stack('scripts')
</body>
</html>