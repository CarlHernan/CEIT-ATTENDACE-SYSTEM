<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-800">Events</p>
                <h2 class="font-semibold text-xl text-blue-950 leading-tight">
                    {{ __('Event List') }}
                </h2>
                @php
                    $role = auth()->user()->role?->slug;
                    $contextLabel = match ($role) {
                        'officer' => 'your society events',
                        'lsg_officer' => 'CEIT-LSG events',
                        default => 'events you can attend',
                    };
                @endphp
                <p class="text-sm text-slate-600">Showing {{ $contextLabel }}.</p>
            </div>
            @if (in_array(auth()->user()->role?->slug, ['officer','lsg_officer','admin']))
                <a href="{{ route('events.create') }}" class="inline-flex items-center px-4 py-2 bg-[var(--color-psits-800)] text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-[var(--color-psits-700)]">
                    + Create Event
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-3 rounded-lg bg-green-50 text-green-800 text-sm border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4">
                @forelse ($events as $event)
                    <a href="{{ route('events.show', $event) }}" class="card p-5 block hover:shadow-md transition">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold text-blue-700 uppercase">{{ $event->scope_label }}</p>
                                <h3 class="text-lg font-semibold text-blue-950">{{ $event->title }}</h3>
                                <p class="text-sm text-slate-600 mt-1 line-clamp-2">{{ $event->description }}</p>
                                <div class="mt-3 flex flex-wrap gap-3 text-sm text-slate-600">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="inline-block w-2 h-2 rounded-full bg-blue-700"></span>
                                        {{ $event->start_at->format('M d, Y g:i A') }}
                                    </span>
                                    @if ($event->location)
                                        <span class="inline-flex items-center gap-1">
                                            📍 {{ $event->location }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 text-slate-700">
                                        {{ ucfirst($event->attendance_mode) }}
                                    </span>
                                    @php
                                        $audienceLabels = [
                                            'society_members' => 'Society members',
                                            'society_officers' => 'Society officers',
                                            'others' => 'Others',
                                            'ceit_students' => 'All CEIT students',
                                            'lsg_officers' => 'CEIT-LSG officers',
                                            'all_officers' => 'All officers',
                                        ];
                                        $audLabel = $audienceLabels[$event->audience] ?? ucwords(str_replace('_',' ', $event->audience));
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 text-slate-700">
                                        Audience: {{ $audLabel }} @if($event->audience === 'others' && $event->audience_notes) - {{ $event->audience_notes }} @endif
                                    </span>
                                </div>
                            </div>
                            <div class="text-right text-xs text-slate-500">
                                Created by {{ $event->creator->name }}
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="card p-6 text-slate-600 text-sm">
                        No events yet.
                    </div>
                @endforelse
            </div>

            <div>
                {{ $events->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
