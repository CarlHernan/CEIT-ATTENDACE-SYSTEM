<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-800">{{ $event->society->abbreviation }}</p>
                <h2 class="font-semibold text-xl text-blue-950 leading-tight">
                    {{ $event->title }}
                </h2>
                <p class="text-sm text-slate-600">{{ $event->start_at->format('M d, Y g:i A') }} @if($event->end_at) - {{ $event->end_at->format('M d, Y g:i A') }} @endif</p>
            </div>
            <a href="{{ route('events.index') }}" class="text-sm text-blue-800 hover:text-blue-900 font-semibold">Back to events</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card p-6 space-y-4">
                <div class="flex flex-wrap gap-3 text-sm text-slate-700">
                    @if ($event->location)
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100">
                            📍 {{ $event->location }}
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-900">
                        Mode: {{ ucfirst($event->attendance_mode) }}
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100">
                        Type: {{ ucfirst($event->type) }}
                    </span>
                    @if ($event->template)
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100">
                            Template: {{ $event->template }}
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100">
                        Audience: {{ ucwords(str_replace('_',' ', $event->audience)) }} @if($event->audience === 'year_specific' && $event->audience_years) ({{ $event->audience_years }}) @endif
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100">
                        Visibility: {{ ucfirst($event->visibility) }}
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full {{ $event->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                        Status: {{ ucfirst($event->status) }}
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100">
                        Created by {{ $event->creator->name }}
                    </span>
                </div>

                <div class="prose max-w-none text-slate-800">
                    {!! nl2br(e($event->description)) !!}
                </div>

                @if (in_array(auth()->user()->role?->slug, ['officer','lsg_officer','admin']))
                    <div class="pt-4 flex items-center gap-3">
                        <a href="{{ route('events.attendance', $event) }}" class="inline-flex items-center px-4 py-2 bg-[var(--color-psits-800)] text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-[var(--color-psits-700)]">
                            Go to Attendance
                        </a>
                        <a href="{{ route('events.edit', $event) }}" class="text-sm text-blue-800 hover:text-blue-900 font-semibold">Edit</a>
                        @if($event->status !== 'cancelled')
                        <form method="POST" action="{{ route('events.cancel', $event) }}" onsubmit="return confirm('Cancel this event?')">
                            @csrf
                            @method('PATCH')
                            <button class="text-sm text-red-600 hover:text-red-700 font-semibold">Cancel</button>
                        </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
