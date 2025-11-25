<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-800">CEIT-LSG</p>
                <h2 class="font-semibold text-xl text-blue-950 leading-tight">
                    {{ __('LSG Dashboard') }}
                </h2>
            </div>
            <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">Overview</span>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-3">
                <a href="{{ route('events.create') }}" class="card p-5 hover:shadow-md transition block">
                    <p class="text-xs font-semibold uppercase text-blue-800">Create CEIT/LSG event</p>
                    <p class="mt-1 text-sm text-slate-700">Audience options: CEIT students, LSG officers, or all officers.</p>
                </a>
                <a href="{{ route('events.index') }}" class="card p-5 hover:shadow-md transition block">
                    <p class="text-xs font-semibold uppercase text-blue-800">Manage events</p>
                    <p class="mt-1 text-sm text-slate-700">Open any CEIT-wide event to access attendance and exports.</p>
                </a>
                <div class="card p-5">
                    <p class="text-xs font-semibold uppercase text-blue-800">Analytics</p>
                    <p class="mt-1 text-sm text-slate-700">From an event page, use “View Analytics” for society breakdowns and absent lists.</p>
                </div>
            </div>

            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-blue-900">LSG-Created Events</h3>
                    <span class="text-xs text-slate-500">Recent</span>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @forelse ($lsgEvents as $ev)
                        <a href="{{ route('events.show', $ev) }}" class="border border-slate-100 rounded-lg p-3 block hover:shadow-sm transition">
                            <p class="text-xs font-semibold text-blue-800 uppercase">{{ $ev->scope_label }}</p>
                            <p class="text-base font-semibold text-blue-950">{{ $ev->title }}</p>
                            <p class="text-sm text-slate-600">{{ $ev->start_at->format('M d, Y g:i A') }}</p>
                            @if($ev->location)
                                <p class="text-xs text-slate-600 mt-1">?? {{ $ev->location }}</p>
                            @endif
                        </a>
                    @empty
                        <p class="text-sm text-slate-600">No LSG events yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="card p-6 space-y-3">
                <h3 class="text-lg font-semibold text-blue-900">How to use this dashboard</h3>
                <ul class="list-disc list-inside text-sm text-slate-700 space-y-2">
                    <li>Create CEIT-wide events (audience = ceit_students) or officer-wide sessions (all_officers / lsg_officers).</li>
                    <li>Open an event → “View Analytics” to see attendance, society breakdowns, and export present/absent lists.</li>
                    <li>Use “Go to Attendance” in the event page to handle live time-in/out.</li>
                    <li>Exports honor filters; absent lists only populate after an event has ended.</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
