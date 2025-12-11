<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CEIT Digital Attendance') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/ceit-logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/ceit-logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-[var(--color-surface)]">
        <div class="min-h-screen relative overflow-hidden">
            <div class="absolute inset-0 opacity-80" aria-hidden="true">
                <div class="absolute -top-24 -left-24 w-64 h-64 bg-[var(--color-psits-100)] rounded-full blur-3xl"></div>
                <div class="absolute top-10 right-0 w-80 h-80 bg-[var(--color-psits-800)]/10 rounded-full blur-3xl"></div>
            </div>

            <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-10 relative">
                <div class="mb-6 text-center">
                    <a href="/" wire:navigate class="inline-flex items-center gap-4">
                        <img src="{{ asset('images/ceit-logo.png') }}" alt="CEIT logo" class="w-14 h-14 rounded-full object-cover">
                        <div class="text-left">
                            <p class="text-sm uppercase tracking-wide text-blue-900 font-semibold">CEIT</p>
                            <p class="text-2xl font-semibold text-blue-950 leading-tight">Digital Attendance</p>
                        </div>
                    </a>
                </div>

                <div class="w-full sm:max-w-xl mt-6 px-6 py-6 bg-white shadow-lg overflow-hidden rounded-2xl border border-slate-100 card-animate">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
