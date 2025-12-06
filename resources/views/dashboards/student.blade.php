<x-app-layout>
    @once
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    @endonce
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-[var(--color-ink-900)] leading-tight">
                    {{ __('Student Dashboard') }}
                </h2>
                <p class="text-sm text-slate-600">College of Engineering and Information Technology</p>
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
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-gradient-to-r from-[var(--color-navy-900)] to-[var(--color-psits-700)] text-white p-6 shadow-[var(--shadow-card-strong)] card-animate">
                    <p class="text-sm font-semibold text-white/80">Society events attended</p>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-4xl font-semibold">{{ $societyEventsAttended }}</span>
                    </div>
                    <p class="text-sm text-white/70 mt-1">This semester</p>
                </div>
                <div class="rounded-2xl bg-gradient-to-r from-[var(--color-navy-900)] to-[var(--color-psits-700)] text-white p-6 shadow-[var(--shadow-card-strong)] card-animate delay-1">
                    <p class="text-sm font-semibold text-white/80">CEIT events attended</p>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-4xl font-semibold">{{ $ceitEventsAttended }}</span>
                    </div>
                    <p class="text-sm text-white/70 mt-1">This semester</p>
                </div>
                <div class="rounded-2xl bg-gradient-to-r from-[var(--color-navy-800)] to-[var(--color-psits-700)] text-white p-6 shadow-[var(--shadow-card-strong)] card-animate delay-2">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-12 items-center justify-center rounded-full bg-white/10">
                            <i class="ri-information-line text-xl text-white"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white/80">Stay updated</p>
                            <p class="text-sm text-white/80 mt-5">Check in early to avoid long queues. Attendance closes at the event's end time.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <h3 class="text-xl font-semibold text-[var(--color-ink-900)] card-animate">Your Society Events</h3>
                    <div class="space-y-4">
                        @forelse ($societyEvents as $ev)
                            @php $audLabel = $audienceLabels[$ev->audience] ?? ucwords(str_replace('_',' ', $ev->audience)); @endphp
                            <div class="rounded-2xl bg-white border border-[var(--color-border-soft)] p-5 shadow-[var(--shadow-card-strong)] card-animate">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="inline-flex items-center rounded-full bg-[var(--color-surface-200)] px-3 py-1 text-xs font-semibold text-[var(--color-navy-900)]">
                                        {{ $ev->scope_label }}
                                    </span>
                                    <div class="flex items-center gap-2 text-sm text-slate-500">
                                        <i class="ri-calendar-event-line text-base"></i>
                                        @php
                                            $start = $ev->start_at;
                                            $end = $ev->end_at;
                                            $sameDay = $start && $end && $start->isSameDay($end);
                                        @endphp
                                        <span>
                                            {{ $start?->format('M j, Y g:i A') }}
                                            @if($end)
                                                <span class="text-slate-400">-</span>
                                                {{ $sameDay ? $end->format('g:i A') : $end->format('M j, Y g:i A') }}
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
                            </div>
                        @empty
                            <div class="rounded-2xl bg-white border border-[var(--color-border-soft)] p-5 shadow-[var(--shadow-card-strong)] flex flex-col items-center justify-center gap-3 text-center text-sm text-slate-600 min-h-[198px] card-animate">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--color-surface-200)] text-[var(--color-navy-900)]">
                                    <i class="ri-calendar-event-line text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-[var(--color-ink-900)]">No society events yet</p>
                                    <p class="text-slate-500">Check back soon for upcoming sessions.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-xl font-semibold text-[var(--color-ink-900)] card-animate">CEIT Events</h3>
                    <div class="space-y-4">
                        @forelse ($ceitEvents as $ev)
                            @php $audLabel = $audienceLabels[$ev->audience] ?? ucwords(str_replace('_',' ', $ev->audience)); @endphp
                            <div class="rounded-2xl bg-white border border-[var(--color-border-soft)] p-5 shadow-[var(--shadow-card-strong)] card-animate delay-1">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="inline-flex items-center rounded-full bg-[var(--color-surface-200)] px-3 py-1 text-xs font-semibold text-[var(--color-navy-900)]">
                                        {{ $ev->scope_label }}
                                    </span>
                                    <div class="flex items-center gap-2 text-sm text-slate-500">
                                        <i class="ri-calendar-event-line text-base"></i>
                                        @php
                                            $start = $ev->start_at;
                                            $end = $ev->end_at;
                                            $sameDay = $start && $end && $start->isSameDay($end);
                                        @endphp
                                        <span>
                                            {{ $start?->format('M j, Y g:i A') }}
                                            @if($end)
                                                <span class="text-slate-400">-</span>
                                                {{ $sameDay ? $end->format('g:i A') : $end->format('M j, Y g:i A') }}
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
                            </div>
                        @empty
                            <div class="rounded-2xl bg-white border border-[var(--color-border-soft)] p-5 shadow-[var(--shadow-card-strong)] flex flex-col items-center justify-center gap-3 text-center text-sm text-slate-600 min-h-[198px] card-animate delay-1">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--color-surface-200)] text-[var(--color-navy-900)]">
                                    <i class="ri-megaphone-line text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-[var(--color-ink-900)]">No CEIT events posted</p>
                                    <p class="text-slate-500">Stay tuned for new announcements.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
