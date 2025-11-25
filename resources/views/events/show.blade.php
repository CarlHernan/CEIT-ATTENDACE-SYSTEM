<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-800">{{ $event->scope_label }}</p>
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
                @php
                    $audienceLabels = [
                        'society_members' => 'Society members',
                        'society_officers' => 'Society officers only',
                        'others' => 'Others (see note)',
                        'ceit_students' => 'All CEIT students',
                        'lsg_officers' => 'CEIT-LSG officers',
                        'all_officers' => 'All officers (society + LSG)',
                    ];
                    $audienceLabel = $audienceLabels[$event->audience] ?? ucwords(str_replace('_', ' ', $event->audience));
                @endphp
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
                        Audience: {{ $audienceLabel }} @if($event->audience === 'others' && $event->audience_notes) ({{ $event->audience_notes }}) @endif
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

                @php
                    $authUser = auth()->user();
                    $role = $authUser->role?->slug;
                    $canManage = false;

                    if ($role === 'admin') {
                        $canManage = true;
                    } elseif ($role === 'lsg_officer') {
                        $canManage = $event->society_id === null || $event->creator?->role?->slug === 'lsg_officer';
                    } elseif ($role === 'officer') {
                        $canManage = $authUser->societies()->pluck('societies.id')->contains($event->society_id);
                    }
                @endphp

                @if ($canManage)
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
