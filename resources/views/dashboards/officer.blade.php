<x-app-layout>
    @once
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    @endonce
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--color-navy-900)] text-white text-lg font-semibold">
                CEIT
            </div>
            <div>
                <h2 class="text-2xl font-semibold text-[var(--color-ink-900)] leading-tight">
                    {{ __('Officer Dashboard') }}
                </h2>
                <p class="text-sm text-slate-600">Plan, monitor, and report society events</p>
            </div>
        </div>
    </x-slot>

    @php
        $audienceLabels = [
            'society_members' => 'Society members',
            'society_officers' => 'Society officers only',
            'others' => 'Others',
            'ceit_students' => 'All CEIT students',
            'lsg_officers' => 'CEIT-LSG officers',
            'all_officers' => 'All officers',
        ];
    @endphp

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('events.create') }}" class="rounded-2xl bg-gradient-to-r from-[var(--color-navy-900)] to-[var(--color-psits-700)] text-white p-5 shadow-[var(--shadow-card-strong)] block transition transform hover:-translate-y-0.5">
                    <div class="flex flex-col gap-3">
                        <i class="ri-add-circle-line text-3xl"></i>
                        <p class="text-sm font-semibold text-white">Create Event</p>
                    </div>
                </a>
                <div class="rounded-2xl bg-gradient-to-r from-[var(--color-navy-900)] to-[var(--color-psits-700)] text-white p-5 shadow-[var(--shadow-card-strong)]">
                    <div class="flex flex-col gap-3">
                        <i class="ri-qr-scan-2-line text-3xl"></i>
                        <p class="text-sm font-semibold text-white">Attendance Tools</p>
                    </div>
                </div>
                <div class="rounded-2xl bg-gradient-to-r from-[var(--color-navy-900)] to-[var(--color-psits-700)] text-white p-5 shadow-[var(--shadow-card-strong)]">
                    <div class="flex flex-col gap-3">
                        <i class="ri-file-chart-line text-3xl"></i>
                        <p class="text-sm font-semibold text-white">Reporting</p>
                    </div>
                </div>
                <a href="{{ route('ai.insights') }}" class="rounded-2xl bg-gradient-to-r from-[var(--color-navy-900)] to-[var(--color-psits-700)] text-white p-5 shadow-[var(--shadow-card-strong)] block transition transform hover:-translate-y-0.5">
                    <div class="flex flex-col gap-3">
                        <i class="ri-robot-line text-3xl"></i>
                        <p class="text-sm font-semibold text-white">AI Attendance Q&amp;A</p>
                    </div>
                </a>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-[var(--color-ink-900)]">Your Society Events</h3>
                        <span class="text-xs text-slate-500">Recent</span>
                    </div>
                    <div class="space-y-4">
                        @forelse ($manageEvents as $ev)
                            @php $audLabel = $audienceLabels[$ev->audience] ?? ucwords(str_replace('_',' ', $ev->audience)); @endphp
                            <a href="{{ route('events.show', $ev) }}" class="rounded-2xl bg-white border border-[var(--color-border-soft)] p-5 shadow-[var(--shadow-card-strong)] block transition transform hover:-translate-y-0.5">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="inline-flex items-center rounded-full bg-[var(--color-surface-200)] px-3 py-1 text-xs font-semibold text-[var(--color-navy-900)]">
                                        {{ $ev->scope_label }}
                                    </span>
                                    <div class="flex items-center gap-2 text-sm text-slate-500">
                                        <i class="ri-calendar-event-line text-base"></i>
                                        <span>
                                            {{ $ev->start_at?->format('M j, Y g:i A') }}
                                            @if($ev->end_at)
                                                <span class="text-slate-400">-</span>
                                                {{ $ev->end_at?->format('M j, Y g:i A') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <h4 class="mt-3 text-lg font-semibold text-[var(--color-ink-900)]">{{ $ev->title }}</h4>
                                <div class="mt-2 flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7.5-4.5 7.5-11.25a7.5 7.5 0 10-15 0C4.5 16.5 12 21 12 21z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 12.75a2.25 2.25 0 110-4.5 2.25 2.25 0 010 4.5z" />
                                    </svg>
                                    <span>{{ $ev->location ?? 'Location TBA' }}</span>
                                </div>
                                <div class="mt-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Audience</p>
                                    <span class="mt-2 inline-flex items-center rounded-full bg-[var(--color-surface-200)] px-3 py-1 text-xs font-semibold text-[var(--color-navy-900)]">
                                        {{ $audLabel }}@if($ev->audience === 'others' && $ev->audience_notes) - {{ $ev->audience_notes }}@endif
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-2xl bg-white border border-[var(--color-border-soft)] p-5 text-sm text-slate-600 shadow-[var(--shadow-card-strong)]">
                                No events yet for your society.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-[var(--color-ink-900)]">CEIT-Wide (LSG) Events</h3>
                        <span class="text-xs text-slate-500">Awareness</span>
                    </div>
                    <div class="space-y-4">
                        @forelse ($ceitAwareness as $ev)
                            @php $audLabel = $audienceLabels[$ev->audience] ?? ucwords(str_replace('_',' ', $ev->audience)); @endphp
                            <a href="{{ route('events.show', $ev) }}" class="rounded-2xl bg-white border border-[var(--color-border-soft)] p-5 shadow-[var(--shadow-card-strong)] block transition transform hover:-translate-y-0.5">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="inline-flex items-center rounded-full bg-[var(--color-surface-200)] px-3 py-1 text-xs font-semibold text-[var(--color-navy-900)]">
                                        {{ $ev->scope_label }}
                                    </span>
                                    <div class="flex items-center gap-2 text-sm text-slate-500">
                                        <i class="ri-calendar-event-line text-base"></i>
                                        <span>
                                            {{ $ev->start_at?->format('M j, Y g:i A') }}
                                            @if($ev->end_at)
                                                <span class="text-slate-400">-</span>
                                                {{ $ev->end_at?->format('M j, Y g:i A') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <h4 class="mt-3 text-lg font-semibold text-[var(--color-ink-900)]">{{ $ev->title }}</h4>
                                <div class="mt-2 flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7.5-4.5 7.5-11.25a7.5 7.5 0 10-15 0C4.5 16.5 12 21 12 21z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 12.75a2.25 2.25 0 110-4.5 2.25 2.25 0 010 4.5z" />
                                    </svg>
                                    <span>{{ $ev->location ?? 'Location TBA' }}</span>
                                </div>
                                <div class="mt-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Audience</p>
                                    <span class="mt-2 inline-flex items-center rounded-full bg-[var(--color-surface-200)] px-3 py-1 text-xs font-semibold text-[var(--color-navy-900)]">
                                        {{ $audLabel }}@if($ev->audience === 'others' && $ev->audience_notes) - {{ $ev->audience_notes }}@endif
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-2xl bg-white border border-[var(--color-border-soft)] p-5 text-sm text-slate-600 shadow-[var(--shadow-card-strong)]">
                                No CEIT-wide events yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
