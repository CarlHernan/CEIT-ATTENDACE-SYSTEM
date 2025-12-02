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
            <div class="card p-6 space-y-6">
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

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 text-sm text-slate-700">
                    @if ($event->location)
                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-50">
                            ?? <span>{{ $event->location }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 text-blue-900">
                        <span class="font-semibold">Mode:</span> {{ ucfirst($event->attendance_mode) }}
                    </div>
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-50">
                        <span class="font-semibold">Audience:</span> {{ $audienceLabel }} @if($event->audience === 'others' && $event->audience_notes) ({{ $event->audience_notes }}) @endif
                    </div>
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-50">
                        <span class="font-semibold">Late threshold:</span> {{ $event->late_threshold_minutes ?? 0 }} mins
                    </div>
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg {{ $event->status === 'cancelled' ? 'bg-red-50 text-red-800' : 'bg-green-50 text-green-800' }}">
                        <span class="font-semibold">Status:</span> {{ ucfirst($event->status) }}
                    </div>
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-50">
                        <span class="font-semibold">Created by:</span> {{ $event->creator->name }}
                    </div>
                </div>

                <div class="prose max-w-none text-slate-800">
                    {!! nl2br(e($event->description)) !!}
                </div>

                @php
                    $authUser = auth()->user();
                    $role = $authUser->role?->slug;
                    $canManage = false;
                    $canAttendance = false;
                    $canViewReport = false;

                    $isCeitWide = $event->audience === 'ceit_students';
                    $userSocietyIds = $authUser->societies()->pluck('societies.id');

                    if ($role === 'admin') {
                        $canManage = true;
                        $canAttendance = true;
                        $canViewReport = true;
                    } elseif ($role === 'lsg_officer') {
                        $canManage = $event->society_id === null || $event->creator?->role?->slug === 'lsg_officer';
                        $canAttendance = $canManage || $isCeitWide;
                        $canViewReport = in_array($event->audience, ['ceit_students', 'all_officers'], true);
                    } elseif ($role === 'officer') {
                        $isOwnSocietyEvent = $event->society_id && $userSocietyIds->contains($event->society_id);
                        $canManage = $isOwnSocietyEvent; // edit/cancel only their own events
                        $canAttendance = $isOwnSocietyEvent || $isCeitWide; // can record on CEIT-wide
                        $canViewReport = $isOwnSocietyEvent; // reports remain for their own events only
                    }
                @endphp

                <div class="flex flex-wrap justify-between">
                    @if ($canAttendance)
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('events.attendance', $event) }}" class="inline-flex items-center px-4 py-2 bg-[var(--color-psits-800)] text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-[var(--color-psits-700)]">
                            Go to Attendance
                        </a>

                        @if ($canViewReport && $role === 'officer')
                        <a href="{{ route('events.report', $event) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-blue-700">
                            View Attendance Report
                        </a>
                        @endif

                        @if ($canViewReport && $role === 'lsg_officer')
                        <a href="{{ route('lsg.analytics', $event) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-blue-700">
                            View Analytics
                        </a>
                        @endif
                    </div>
                    @endif

                    @if ($canManage)
                    <div class="flex flex-wrap items-center gap-3">
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
    </div>
</x-app-layout>
