<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-[var(--color-ink-900)] leading-tight">
                    {{ __('Admin Dashboard') }}
                </h2>
                <p class="text-sm text-slate-600">System administration and analytics overview</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Key Metrics Top Row --}}
            <div class="grid gap-4 md:grid-cols-4">
                {{-- Total Users --}}
                <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'all-users-modal')" class="rounded-2xl bg-gradient-to-r from-[var(--color-navy-900)] to-[var(--color-psits-700)] text-white p-6 shadow-[var(--shadow-card-strong)] card-animate transition transform hover:-translate-y-0.5 hover:shadow-lg text-left w-full">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-white/80">Total Users</p>
                            <p class="mt-2 text-3xl font-bold">{{ $totalUsers }}</p>
                            <p class="mt-1 text-xs text-white/70">{{ $totalStudents }} students, {{ $totalOfficers }} officers</p>
                        </div>
                        <div class="rounded-lg bg-white/20 p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                </button>

                {{-- Attendance Rate --}}
                <div class="rounded-2xl bg-gradient-to-r from-[var(--color-navy-900)] to-[var(--color-psits-700)] text-white p-6 shadow-[var(--shadow-card-strong)] card-animate delay-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-white/80">Attendance Rate</p>
                            <p class="mt-2 text-3xl font-bold">{{ $attendanceRate }}%</p>
                            <p class="mt-1 text-xs text-white/70">Last 30 days average</p>
                        </div>
                        <div class="rounded-lg bg-white/20 p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Events This Month --}}
                <div class="rounded-2xl bg-gradient-to-r from-[var(--color-navy-900)] to-[var(--color-psits-700)] text-white p-6 shadow-[var(--shadow-card-strong)] card-animate delay-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-white/80">Events This Month</p>
                            <p class="mt-2 text-3xl font-bold">{{ $eventsThisMonth }}</p>
                            <p class="mt-1 text-xs text-white/70">{{ $activeEventsCount }} active now</p>
                        </div>
                        <div class="rounded-lg bg-white/20 p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Active Societies --}}
                <div class="rounded-2xl bg-gradient-to-r from-[var(--color-navy-900)] to-[var(--color-psits-700)] text-white p-6 shadow-[var(--shadow-card-strong)] card-animate delay-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-white/80">Active Societies</p>
                            <p class="mt-2 text-3xl font-bold">{{ $totalSocieties }}</p>
                            <p class="mt-1 text-xs text-white/70">Total registered</p>
                        </div>
                        <div class="rounded-lg bg-white/20 p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Compliance Alerts --}}
            @if(count($alerts) > 0)
            <div class="space-y-3">
                @foreach($alerts as $alert)
                <div class="rounded-xl {{ $alert['type'] === 'warning' ? 'bg-amber-50 border border-amber-200' : ($alert['type'] === 'success' ? 'bg-emerald-50 border border-emerald-200' : 'bg-blue-50 border border-blue-200') }} p-4 card-animate">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            @if($alert['type'] === 'warning')
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            @elseif($alert['type'] === 'success')
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @else
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium {{ $alert['type'] === 'warning' ? 'text-amber-900' : ($alert['type'] === 'success' ? 'text-emerald-900' : 'text-blue-900') }}">
                                {{ $alert['message'] }}
                            </p>
                        </div>
                        @if(isset($alert['modal']))
                        <button x-data="" x-on:click="$dispatch('open-modal', '{{ $alert['modal'] }}')" class="text-xs font-medium {{ $alert['type'] === 'warning' ? 'text-amber-700 hover:text-amber-900' : ($alert['type'] === 'success' ? 'text-emerald-700 hover:text-emerald-900' : 'text-blue-700 hover:text-blue-900') }} whitespace-nowrap">
                            {{ $alert['action'] }} →
                        </button>
                        @else
                        <span class="text-xs font-medium {{ $alert['type'] === 'warning' ? 'text-amber-700' : ($alert['type'] === 'success' ? 'text-emerald-700' : 'text-blue-700') }} whitespace-nowrap">
                            {{ $alert['action'] }}
                        </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-2">
                {{-- Upcoming Events --}}
                <div class="rounded-2xl bg-white p-6 shadow-[var(--shadow-card-strong)] card-animate">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[var(--color-ink-900)]">Upcoming Events</h3>
                        <a href="{{ route('events.index') }}" class="text-sm font-medium text-[var(--color-psits-700)] hover:text-[var(--color-psits-900)]">
                            View all →
                        </a>
                    </div>
                    <div class="space-y-3 max-h-[288px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-slate-300 scrollbar-track-slate-100">
                        @forelse($upcomingEvents as $event)
                        <div class="p-3 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors border border-slate-200">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm text-[var(--color-ink-900)] truncate">{{ $event->title }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium {{ $event->is_ceit_wide ? 'bg-violet-100 text-violet-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ $event->is_ceit_wide ? 'CEIT-Wide' : ($event->society?->abbreviation ?? 'Society') }}
                                        </span>
                                        <span class="text-xs text-slate-600">
                                            {{ $event->start_at->format('M d, Y • h:i A') }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('events.show', $event) }}" class="text-xs font-medium text-[var(--color-psits-700)] hover:text-[var(--color-psits-900)] whitespace-nowrap">
                                    View
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-slate-600">No upcoming events</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Attendance Overview --}}
                <div class="rounded-2xl bg-white p-6 shadow-[var(--shadow-card-strong)] card-animate delay-1">
                    <h3 class="text-lg font-semibold text-[var(--color-ink-900)] mb-4">Attendance Overview</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gradient-to-r from-violet-50 to-purple-50 border border-violet-100">
                            <div>
                                <p class="text-sm font-medium text-slate-700">Total Records</p>
                                <p class="text-2xl font-bold text-[var(--color-ink-900)]">{{ number_format($totalAttendanceRecords) }}</p>
                            </div>
                            <div class="rounded-lg bg-violet-100 p-3">
                                <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 rounded-lg bg-blue-50 border border-blue-100">
                                <p class="text-xs font-medium text-slate-600">Today</p>
                                <p class="text-xl font-bold text-[var(--color-ink-900)] mt-1">{{ $attendanceToday }}</p>
                            </div>
                            <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                                <p class="text-xs font-medium text-slate-600">This Week</p>
                                <p class="text-xl font-bold text-[var(--color-ink-900)] mt-1">{{ $attendanceThisWeek }}</p>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-200">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-600">Average Rate (30d)</span>
                                <span class="font-semibold text-{{ $attendanceRate >= 80 ? 'emerald' : ($attendanceRate >= 60 ? 'amber' : 'red') }}-600">
                                    {{ $attendanceRate }}%
                                </span>
                            </div>
                            <div class="mt-2 w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-violet-500 to-purple-600 h-2 rounded-full transition-all duration-500" style="width: {{ $attendanceRate }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Society Activity Stats --}}
            <div class="rounded-2xl bg-white p-6 shadow-[var(--shadow-card-strong)] card-animate">
                <h3 class="text-lg font-semibold text-[var(--color-ink-900)] mb-4">Society Activity</h3>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach($societyStats as $stat)
                    @php
                        $slug = $stat['society']->slug;
                        $colorMap = [
                            'psits' => ['from' => 'from-blue-600', 'to' => 'to-blue-700', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700'],
                            'pice' => ['from' => 'from-amber-500', 'to' => 'to-orange-600', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700'],
                            'icpep' => ['from' => 'from-red-500', 'to' => 'to-red-600', 'bg' => 'bg-red-50', 'text' => 'text-red-700'],
                            'jiecep' => ['from' => 'from-emerald-500', 'to' => 'to-teal-600', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
                            'psabe' => ['from' => 'from-green-600', 'to' => 'to-green-700', 'bg' => 'bg-green-50', 'text' => 'text-green-700'],
                        ];
                        $colors = $colorMap[$slug] ?? ['from' => 'from-violet-500', 'to' => 'to-purple-600', 'bg' => 'bg-violet-50', 'text' => 'text-violet-700'];
                    @endphp
                    <div class="rounded-xl {{ $colors['bg'] }} p-4 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $colors['from'] }} {{ $colors['to'] }} flex items-center justify-center text-white shadow-lg">
                                <img src="{{ asset('images/logo/' . $slug . '.png') }}" alt="{{ $stat['society']->abbreviation }}" class="w-8 h-8 object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <span class="hidden text-sm font-bold">{{ substr($stat['society']->abbreviation, 0, 2) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm {{ $colors['text'] }} truncate">{{ $stat['society']->abbreviation }}</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-600 font-medium">Members</span>
                                <span class="font-bold {{ $colors['text'] }}">{{ $stat['members_count'] }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-600 font-medium">Events (month)</span>
                                <span class="font-bold {{ $colors['text'] }}">{{ $stat['events_this_month'] }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="rounded-2xl bg-white p-6 shadow-[var(--shadow-card-strong)] card-animate">
                <h3 class="text-lg font-semibold text-[var(--color-ink-900)] mb-4">Recent Activity</h3>
                <div class="space-y-3">
                    @forelse($recentEvents as $event)
                    <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-[var(--color-ink-900)]">
                                New event created: <span class="text-[var(--color-primary)]">{{ $event->title }}</span>
                            </p>
                            <p class="text-xs text-slate-600 mt-1">
                                By {{ $event->creator->name }} • {{ $event->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-slate-600 text-center py-4">No recent activity</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    {{-- All Users Modal --}}
    <x-modal name="all-users-modal" max-width="4xl">
        <div class="bg-white">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-semibold text-[var(--color-ink-900)]">All Users</h3>
                    <p class="text-sm text-slate-600">{{ $totalUsers }} total users in the system</p>
                </div>
                <button class="text-slate-400 hover:text-slate-600" x-on:click="$dispatch('close-modal', 'all-users-modal')">
                    <span class="sr-only">Close</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="max-h-[70vh] overflow-y-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 sticky top-0">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">ID Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Society</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($allUsers as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-[var(--color-navy-900)] to-[var(--color-psits-700)] flex items-center justify-center text-white text-sm font-semibold">
                                            {{ $user->initials() }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-[var(--color-ink-900)]">{{ $user->name }}</div>
                                        <div class="text-sm text-slate-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                {{ $user->id_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role?->slug === 'admin' ? 'bg-purple-100 text-purple-800' : ($user->role?->slug === 'lsg_officer' ? 'bg-blue-100 text-blue-800' : ($user->role?->slug === 'officer' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ $user->role?->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                {{ $user->course }} {{ $user->year_level ? '- Year ' . $user->year_level : '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                @if($user->societies->isNotEmpty())
                                    {{ $user->societies->pluck('abbreviation')->join(', ') }}
                                @else
                                    <span class="text-slate-400">None</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-modal>

    {{-- Inactive Students Modal --}}
    <x-modal name="inactive-students-modal" max-width="2xl">
        <div class="bg-white">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-semibold text-[var(--color-ink-900)]">Inactive Students</h3>
                    <p class="text-sm text-slate-600">Students with no attendance in the last 30 days</p>
                </div>
                <button class="text-slate-400 hover:text-slate-600" x-on:click="$dispatch('close-modal', 'inactive-students-modal')">
                    <span class="sr-only">Close</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="max-h-[70vh] overflow-y-auto">
                @if($inactiveStudents->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-slate-600">All students are active!</p>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">ID Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Course</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Society</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @foreach($inactiveStudents as $student)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-slate-400 to-slate-500 flex items-center justify-center text-white text-sm font-semibold">
                                                {{ $student->initials() }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-[var(--color-ink-900)]">{{ $student->name }}</div>
                                            <div class="text-sm text-slate-500">{{ $student->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ $student->id_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ $student->course }} {{ $student->year_level ? '- Year ' . $student->year_level : '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    @if($student->societies->isNotEmpty())
                                        {{ $student->societies->pluck('abbreviation')->join(', ') }}
                                    @else
                                        <span class="text-slate-400">None</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </x-modal>
</x-app-layout>
