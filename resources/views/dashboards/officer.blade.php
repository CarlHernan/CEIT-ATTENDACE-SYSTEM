<x-app-layout>
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
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('events.create') }}" class="rounded-2xl bg-[var(--color-navy-900)] text-white p-5 shadow-[var(--shadow-card-strong)] block transition transform hover:-translate-y-0.5">
                    <div class="flex flex-col gap-3">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                        </svg>
                        <p class="text-sm font-semibold text-white">Create Event</p>
                    </div>
                </a>
                <div class="rounded-2xl bg-[var(--color-navy-900)] text-white p-5 shadow-[var(--shadow-card-strong)]">
                    <div class="flex flex-col gap-3">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h10m-7 5h7" />
                        </svg>
                        <p class="text-sm font-semibold text-white">Attendance Tools</p>
                    </div>
                </div>
                <div class="rounded-2xl bg-[var(--color-navy-900)] text-white p-5 shadow-[var(--shadow-card-strong)]">
                    <div class="flex flex-col gap-3">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4-4 4 4 6-6" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h.01" />
                        </svg>
                        <p class="text-sm font-semibold text-white">Reporting</p>
                    </div>
                </div>
                <a href="{{ route('ai.insights') }}" class="rounded-2xl bg-[var(--color-navy-900)] text-white p-5 shadow-[var(--shadow-card-strong)] block transition transform hover:-translate-y-0.5">
                    <div class="flex flex-col gap-3">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 4.036a2.25 2.25 0 014.374 0l.19.77a2.25 2.25 0 001.542 1.63l.758.226a2.25 2.25 0 011.39 3.084l-.312.72a2.25 2.25 0 000 1.72l.312.72a2.25 2.25 0 01-1.39 3.084l-.758.226a2.25 2.25 0 00-1.542 1.63l-.19.77a2.25 2.25 0 01-4.374 0l-.19-.77a2.25 2.25 0 00-1.542-1.63l-.758-.226a2.25 2.25 0 01-1.39-3.084l.312-.72a2.25 2.25 0 000-1.72l-.312-.72a2.25 2.25 0 011.39-3.084l.758-.226a2.25 2.25 0 001.542-1.63l.19-.77z" />
                        </svg>
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
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5h10.5a2.25 2.25 0 012.25 2.25v8.25A2.25 2.25 0 0117.25 20.25H6.75A2.25 2.25 0 014.5 18V9.75A2.25 2.25 0 016.75 7.5zm0 0V5.25A1.5 1.5 0 018.25 3.75h.75a1.5 1.5 0 011.5 1.5V7.5m-3.75 0h6.75m0-2.25A1.5 1.5 0 0115.75 3.75h.75a1.5 1.5 0 011.5 1.5V7.5" />
                                        </svg>
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
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5h10.5a2.25 2.25 0 012.25 2.25v8.25A2.25 2.25 0 0117.25 20.25H6.75A2.25 2.25 0 014.5 18V9.75A2.25 2.25 0 016.75 7.5zm0 0V5.25A1.5 1.5 0 018.25 3.75h.75a1.5 1.5 0 011.5 1.5V7.5m-3.75 0h6.75m0-2.25A1.5 1.5 0 0115.75 3.75h.75a1.5 1.5 0 011.5 1.5V7.5" />
                                        </svg>
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
