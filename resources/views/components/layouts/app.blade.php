<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — {{ $title ?? 'Dashboard' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-100">
    <div class="flex h-full">
        @include('partials.sidebar')
        <div class="flex flex-col flex-1 min-h-screen overflow-hidden">
            @include('partials.topbar')
            <main class="flex-1 overflow-y-auto bg-slate-950 p-6">
                {{-- Global flash messages --}}
                @if(session('success'))
                    <x-ui.alert type="success" class="mb-5">{{ session('success') }}</x-ui.alert>
                @endif
                @if(session('error'))
                    <x-ui.alert type="error" class="mb-5">{{ session('error') }}</x-ui.alert>
                @endif
                @if(session('warning'))
                    <x-ui.alert type="warning" class="mb-5">{{ session('warning') }}</x-ui.alert>
                @endif
                @if(session('info'))
                    <x-ui.alert type="info" class="mb-5">{{ session('info') }}</x-ui.alert>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
