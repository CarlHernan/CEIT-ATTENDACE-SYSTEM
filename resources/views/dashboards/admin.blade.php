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
                <div class="rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 text-white p-6 shadow-[var(--shadow-card-strong)] card-animate">
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
                </div>

                {{-- Attendance Rate --}}
                <div class="rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white p-6 shadow-[var(--shadow-card-strong)] card-animate delay-1">
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
                <div class="rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white p-6 shadow-[var(--shadow-card-strong)] card-animate delay-2">
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
                <div class="rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white p-6 shadow-[var(--shadow-card-strong)] card-animate delay-3">
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
                        <button class="text-xs font-medium {{ $alert['type'] === 'warning' ? 'text-amber-700 hover:text-amber-900' : ($alert['type'] === 'success' ? 'text-emerald-700 hover:text-emerald-900' : 'text-blue-700 hover:text-blue-900') }} whitespace-nowrap">
                            {{ $alert['action'] }} →
                        </button>
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
                        <a href="{{ route('events.index') }}" class="text-sm font-medium text-[var(--color-primary)] hover:text-[var(--color-primary-dark)]">
                            View all →
                        </a>
                    </div>
                    <div class="space-y-3">
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
                                <a href="{{ route('events.show', $event) }}" class="text-xs font-medium text-[var(--color-primary)] hover:text-[var(--color-primary-dark)] whitespace-nowrap">
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
                    <div class="p-4 rounded-lg border-2 border-slate-200 hover:border-[var(--color-primary)] transition-colors">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                {{ substr($stat['society']->abbreviation, 0, 2) }}
                            </div>
                            <p class="font-semibold text-sm text-[var(--color-ink-900)]">{{ $stat['society']->abbreviation }}</p>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-600">Members</span>
                                <span class="font-medium text-[var(--color-ink-900)]">{{ $stat['members_count'] }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-600">Events (month)</span>
                                <span class="font-medium text-[var(--color-ink-900)]">{{ $stat['events_this_month'] }}</span>
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

            {{-- Quick Actions --}}
            <div class="grid gap-4 md:grid-cols-3">
                <a href="{{ route('events.create') }}" class="rounded-2xl bg-gradient-to-br from-[var(--color-navy-900)] to-[var(--color-primary)] text-white p-6 shadow-[var(--shadow-card-strong)] card-animate hover:scale-105 transition-transform">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-white/20 p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Create Event</p>
                            <p class="text-xs text-white/80">Schedule new event</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('events.index') }}" class="rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white p-6 shadow-[var(--shadow-card-strong)] card-animate delay-1 hover:scale-105 transition-transform">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-white/20 p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Manage Events</p>
                            <p class="text-xs text-white/80">View all events</p>
                        </div>
                    </div>
                </a>

                <button class="rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white p-6 shadow-[var(--shadow-card-strong)] card-animate delay-2 hover:scale-105 transition-transform text-left">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-white/20 p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Manage Societies</p>
                            <p class="text-xs text-white/80">Society administration</p>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
