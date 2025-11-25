<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-800">Student</p>
                <h2 class="font-semibold text-xl text-blue-950 leading-tight">
                    {{ __('Student Dashboard') }}
                </h2>
            </div>
            <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">CEIT</span>
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
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="card p-5 bg-[var(--color-psits-100)] border-none shadow-none">
                    <p class="text-xs font-semibold text-blue-900 uppercase">Your society events</p>
                    <p class="text-2xl font-semibold text-blue-950">{{ $societyEvents->count() }}</p>
                </div>
                <div class="card p-5 shadow-sm">
                    <p class="text-xs font-semibold text-blue-900 uppercase">CEIT events</p>
                    <p class="text-2xl font-semibold text-blue-950">{{ $ceitEvents->count() }}</p>
                </div>
                <div class="card p-5 shadow-sm">
                    <p class="text-xs font-semibold text-blue-900 uppercase">Stay updated</p>
                    <p class="text-sm text-slate-700 mt-1">Check in early and keep your ID QR ready.</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="card p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-blue-900">Your Society Events</h3>
                        <span class="text-xs text-slate-500">Upcoming / recent</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($societyEvents as $ev)
                            <div class="border border-slate-100 rounded-lg p-3">
                                <p class="text-xs font-semibold text-blue-800 uppercase">{{ $ev->scope_label }}</p>
                                <p class="text-base font-semibold text-blue-950">{{ $ev->title }}</p>
                                <p class="text-sm text-slate-600">{{ $ev->start_at->format('M d, Y g:i A') }}</p>
                                @if($ev->location)
                                    <p class="text-xs text-slate-600 mt-1">📍 {{ $ev->location }}</p>
                                @endif
                                @php $audLabel = $audienceLabels[$ev->audience] ?? ucwords(str_replace('_',' ', $ev->audience)); @endphp
                                <p class="text-xs text-slate-500 mt-1">Audience: {{ $audLabel }} @if($ev->audience === 'others' && $ev->audience_notes) — {{ $ev->audience_notes }} @endif</p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-600">No events yet for your society.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-blue-900">CEIT Events</h3>
                        <span class="text-xs text-slate-500">Upcoming / recent</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($ceitEvents as $ev)
                            <div class="border border-slate-100 rounded-lg p-3">
                                <p class="text-xs font-semibold text-blue-800 uppercase">{{ $ev->scope_label }}</p>
                                <p class="text-base font-semibold text-blue-950">{{ $ev->title }}</p>
                                <p class="text-sm text-slate-600">{{ $ev->start_at->format('M d, Y g:i A') }}</p>
                                @if($ev->location)
                                    <p class="text-xs text-slate-600 mt-1">📍 {{ $ev->location }}</p>
                                @endif
                                @php $audLabel = $audienceLabels[$ev->audience] ?? ucwords(str_replace('_',' ', $ev->audience)); @endphp
                                <p class="text-xs text-slate-500 mt-1">Audience: {{ $audLabel }} @if($ev->audience === 'others' && $ev->audience_notes) — {{ $ev->audience_notes }} @endif</p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-600">No upcoming CEIT events.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
