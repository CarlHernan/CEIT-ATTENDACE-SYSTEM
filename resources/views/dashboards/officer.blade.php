<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-800">Society Officer</p>
                <h2 class="font-semibold text-xl text-blue-950 leading-tight">
                    {{ __('Officer Dashboard') }}
                </h2>
            </div>
            <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">Events</span>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-3">
                <a href="{{ route('events.create') }}" class="card p-5 block hover:shadow-md transition">
                    <p class="text-xs font-semibold uppercase text-blue-800">Create event</p>
                    <p class="mt-1 text-sm text-slate-700">Prefilled with your society.</p>
                </a>
                <div class="card p-5">
                    <p class="text-xs font-semibold uppercase text-blue-800">Attendance tools</p>
                    <p class="mt-1 text-sm text-slate-700">Open an event → Go to Attendance for QR/manual check-in.</p>
                </div>
                <div class="card p-5">
                    <p class="text-xs font-semibold uppercase text-blue-800">Reporting</p>
                    <p class="mt-1 text-sm text-slate-700">Open an event → View Attendance Report for exports.</p>
                </div>
                <a href="{{ route('ai.insights') }}" class="card p-5 block hover:shadow-md transition">
                    <p class="text-xs font-semibold uppercase text-blue-800">AI Attendance Q&A</p>
                    <p class="mt-1 text-sm text-slate-700">Ask questions about your events and students based on current records.</p>
                </a>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="card p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-blue-900">Your Society Events</h3>
                        <span class="text-xs text-slate-500">Recent</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($manageEvents as $ev)
                            <a href="{{ route('events.show', $ev) }}" class="border border-slate-100 rounded-lg p-3 block hover:shadow-sm transition">
                                <p class="text-xs font-semibold text-blue-800 uppercase">{{ $ev->scope_label }}</p>
                                <p class="text-base font-semibold text-blue-950">{{ $ev->title }}</p>
                                <p class="text-sm text-slate-600">{{ $ev->start_at->format('M d, Y g:i A') }}</p>
                                @if($ev->location)
                                    <p class="text-xs text-slate-600 mt-1">📍 {{ $ev->location }}</p>
                                @endif
                            </a>
                        @empty
                            <p class="text-sm text-slate-600">No events yet for your society.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-blue-900">CEIT-Wide (LSG) Events</h3>
                        <span class="text-xs text-slate-500">Awareness</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($ceitAwareness as $ev)
                            <a href="{{ route('events.show', $ev) }}" class="border border-slate-100 rounded-lg p-3 block hover:shadow-sm transition">
                                <p class="text-xs font-semibold text-blue-800 uppercase">{{ $ev->scope_label }}</p>
                                <p class="text-base font-semibold text-blue-950">{{ $ev->title }}</p>
                                <p class="text-sm text-slate-600">{{ $ev->start_at->format('M d, Y g:i A') }}</p>
                                @if($ev->location)
                                    <p class="text-xs text-slate-600 mt-1">📍 {{ $ev->location }}</p>
                                @endif
                            </a>
                        @empty
                            <p class="text-sm text-slate-600">No CEIT-wide events yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
