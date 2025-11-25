<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-800">CEIT-LSG</p>
                <h2 class="font-semibold text-xl text-blue-950 leading-tight">
                    Event Analytics
                </h2>
                <p class="text-sm text-slate-600">{{ $event->title }} — {{ $event->start_at->format('M d, Y g:i A') }}</p>
            </div>
            <a href="{{ route('events.index') }}" class="text-sm text-blue-800 hover:text-blue-900 font-semibold">Back to events</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="card p-4">
                    <p class="text-xs font-semibold uppercase text-blue-800">Total attendees</p>
                    <p class="text-2xl font-semibold text-blue-950">{{ $summary['total'] ?? 0 }}</p>
                </div>
                <div class="card p-4">
                    <p class="text-xs font-semibold uppercase text-blue-800">Societies represented</p>
                    <p class="text-2xl font-semibold text-blue-950">{{ $summary['societies_represented'] ?? 0 }}</p>
                </div>
                <div class="card p-4">
                    <p class="text-xs font-semibold uppercase text-blue-800">Audience</p>
                    <p class="text-base font-semibold text-blue-950">{{ ucwords(str_replace('_',' ', $event->audience)) }}</p>
                </div>
            </div>

            <div class="card p-5 space-y-4">
                <form method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <x-input-label for="society" :value="__('Society')" />
                        <select name="society" id="society" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700">
                            <option value="">Any</option>
                            @foreach (\App\Models\Society::orderBy('abbreviation')->get() as $soc)
                                <option value="{{ $soc->id }}" @selected(request('society')==$soc->id)>{{ $soc->abbreviation }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="course" :value="__('Course')" />
                        <select name="course[]" id="course" multiple class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700">
                            @foreach (['BSCS','BLIS','BSInfoSys','BSCE','BSCpE','BSECE','BSABE'] as $course)
                                <option value="{{ $course }}" @selected(collect(request('course'))->contains($course))>{{ $course }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="section" :value="__('Section')" />
                        <x-text-input id="section" name="section" type="text" class="mt-1 block w-full" value="{{ request('section') }}" />
                    </div>
                    <div>
                        <x-input-label for="year_level" :value="__('Year level')" />
                        <x-text-input id="year_level" name="year_level" type="text" class="mt-1 block w-full" value="{{ request('year_level') }}" />
                    </div>
                    <div>
                        <x-input-label for="position" :value="__('Position')" />
                        <select id="position" name="position" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700">
                            <option value="">Any</option>
                            <option value="Member" @selected(request('position')==='Member')>Member</option>
                            <option value="Officer" @selected(request('position')==='Officer')>Officer</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-5 flex items-center gap-3">
                        <x-primary-button>Apply Filters</x-primary-button>
                        <a href="{{ route('lsg.analytics', $event) }}" class="text-sm text-blue-800 hover:text-blue-900">Clear</a>
                    </div>
                </form>
            </div>

            <div class="card p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-blue-900">Attendance</h3>
                    <div class="flex items-center gap-3">
                        <form method="GET" action="{{ route('lsg.analytics.export', $event) }}" class="flex items-center gap-2">
                            <select name="format" class="rounded-lg border-gray-300 text-sm">
                                <option value="xlsx">XLSX</option>
                                <option value="csv">CSV</option>
                            </select>
                            <input type="hidden" name="society" value="{{ request('society') }}">
                            <input type="hidden" name="course" value="{{ request('course') ? implode(',', (array) request('course')) : '' }}">
                            <input type="hidden" name="section" value="{{ request('section') }}">
                            <input type="hidden" name="year_level" value="{{ request('year_level') }}">
                            <input type="hidden" name="position" value="{{ request('position') }}">
                            <x-primary-button type="submit">Export Attendance</x-primary-button>
                        </form>
                        <form method="GET" action="{{ route('lsg.analytics.export-absent', $event) }}" class="flex items-center gap-2">
                            <select name="format" class="rounded-lg border-gray-300 text-sm">
                                <option value="xlsx">XLSX</option>
                                <option value="csv">CSV</option>
                            </select>
                            <input type="hidden" name="society" value="{{ request('society') }}">
                            <input type="hidden" name="course" value="{{ request('course') ? implode(',', (array) request('course')) : '' }}">
                            <input type="hidden" name="section" value="{{ request('section') }}">
                            <input type="hidden" name="year_level" value="{{ request('year_level') }}">
                            <input type="hidden" name="position" value="{{ request('position') }}">
                            <x-secondary-button type="submit">Export Absent</x-secondary-button>
                        </form>
                    </div>
                </div>

                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-slate-600">
                                <th class="px-3 py-2">Student ID</th>
                                <th class="px-3 py-2">Name</th>
                                <th class="px-3 py-2">Year</th>
                                <th class="px-3 py-2">Course</th>
                                <th class="px-3 py-2">Section</th>
                                <th class="px-3 py-2">Society</th>
                                <th class="px-3 py-2">Dept</th>
                                <th class="px-3 py-2">Position</th>
                                <th class="px-3 py-2">Time In</th>
                                <th class="px-3 py-2">Time Out</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($records as $record)
                                @php $user = $record->user; $soc = $user?->societies?->first(); @endphp
                                <tr>
                                    <td class="px-3 py-2">{{ $user?->id_number }}</td>
                                    <td class="px-3 py-2">{{ $user?->name }}</td>
                                    <td class="px-3 py-2">{{ $user?->year_level }}</td>
                                    <td class="px-3 py-2">{{ $user?->course }}</td>
                                    <td class="px-3 py-2">{{ $user?->section }}</td>
                                    <td class="px-3 py-2">{{ $soc?->abbreviation }}</td>
                                    <td class="px-3 py-2">{{ $user?->department ?? 'CEIT' }}</td>
                                    <td class="px-3 py-2">{{ $soc?->pivot?->position }}</td>
                                    <td class="px-3 py-2">{{ optional($record->time_in)->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2">{{ optional($record->time_out)->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2 font-semibold">{{ $record->computed_status ?? '' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="px-3 py-3 text-slate-500">No attendance records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div>
                    {{ $records->links() }}
                </div>
            </div>

            <div class="card p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-blue-900">Society breakdown</h3>
                </div>
                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-slate-600">
                                <th class="px-3 py-2">Society</th>
                                <th class="px-3 py-2">Attendee Count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($breakdown as $row)
                                @php $soc = \App\Models\Society::find($row->society_id); @endphp
                                <tr>
                                    <td class="px-3 py-2">{{ $soc?->abbreviation ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $row->total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-3 py-3 text-slate-500">No data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-blue-900">Absent attendees</h3>
                </div>
                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-slate-600">
                                <th class="px-3 py-2">Student ID</th>
                                <th class="px-3 py-2">Name</th>
                                <th class="px-3 py-2">Year</th>
                                <th class="px-3 py-2">Course</th>
                                <th class="px-3 py-2">Section</th>
                                <th class="px-3 py-2">Society</th>
                                <th class="px-3 py-2">Position</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($absent as $user)
                                @php $soc = $user->societies->first(); @endphp
                                <tr>
                                    <td class="px-3 py-2">{{ $user->id_number }}</td>
                                    <td class="px-3 py-2">{{ $user->name }}</td>
                                    <td class="px-3 py-2">{{ $user->year_level }}</td>
                                    <td class="px-3 py-2">{{ $user->course }}</td>
                                    <td class="px-3 py-2">{{ $user->section }}</td>
                                    <td class="px-3 py-2">{{ $soc?->abbreviation }}</td>
                                    <td class="px-3 py-2">{{ $soc?->pivot?->position }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-3 text-slate-500">No absent attendees based on current filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
