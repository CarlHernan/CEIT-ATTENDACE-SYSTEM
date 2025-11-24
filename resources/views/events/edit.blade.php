<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-800">{{ $event->society->abbreviation }}</p>
                <h2 class="font-semibold text-xl text-blue-950 leading-tight">
                    {{ __('Edit Event') }}
                </h2>
            </div>
            <a href="{{ route('events.show', $event) }}" class="text-sm text-blue-800 hover:text-blue-900 font-semibold">Back to event</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card p-6">
                <form method="POST" action="{{ route('events.update', $event) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" value="{{ old('title', $event->title) }}" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700">{{ old('description', $event->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="start_at" :value="__('Start Date & Time')" />
                            <x-text-input id="start_at" name="start_at" type="datetime-local" class="mt-1 block w-full" value="{{ old('start_at', $event->start_at->format('Y-m-d\TH:i')) }}" required />
                            <x-input-error :messages="$errors->get('start_at')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="end_at" :value="__('End Date & Time (optional)')" />
                            <x-text-input id="end_at" name="end_at" type="datetime-local" class="mt-1 block w-full" value="{{ old('end_at', optional($event->end_at)->format('Y-m-d\TH:i')) }}" />
                            <x-input-error :messages="$errors->get('end_at')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="location" :value="__('Location')" />
                        <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" value="{{ old('location', $event->location) }}" />
                        <x-input-error :messages="$errors->get('location')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="attendance_mode" :value="__('Attendance Mode')" />
                            <select id="attendance_mode" name="attendance_mode" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700">
                                @foreach (['qr' => 'QR scanning', 'manual' => 'Manual ID', 'hybrid' => 'Hybrid'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('attendance_mode', $event->attendance_mode) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('attendance_mode')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="society_id" :value="__('Society')" />
                            @if ($role === 'lsg_officer')
                                <input type="hidden" name="society_id" value="{{ old('society_id', $event->society_id ?? $defaultSocietyId) }}">
                                <div class="mt-1 text-sm text-slate-700">CEIT (LSG event)</div>
                            @else
                                <select id="society_id" name="society_id" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700">
                                    @foreach ($societies as $society)
                                        <option value="{{ $society->id }}" @selected(old('society_id', $event->society_id) == $society->id)>{{ $society->abbreviation }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('society_id')" class="mt-2" />
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="type" :value="__('Type')" />
                            <select id="type" name="type" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700">
                                @foreach ($types as $type)
                                    <option value="{{ $type }}" @selected(old('type', $event->type) === $type)>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="template" :value="__('Template')" />
                            <select id="template" name="template" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($templates as $tpl)
                                    <option value="{{ $tpl }}" @selected(old('template', $event->template) === $tpl)>{{ $tpl }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('template')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="audience" :value="__('Audience')" />
                            <select id="audience" name="audience" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700">
                                @foreach ($audiences as $aud)
                                    <option value="{{ $aud }}" @selected(old('audience', $event->audience) === $aud)>{{ ucwords(str_replace('_',' ', $aud)) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('audience')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="audience_years" :value="__('Year scope (comma separated)')" />
                            <x-text-input id="audience_years" name="audience_years" type="text" class="mt-1 block w-full" value="{{ old('audience_years', $event->audience_years) }}" placeholder="e.g., 1,2" />
                            <x-input-error :messages="$errors->get('audience_years')" class="mt-2" />
                        </div>
                        <div class="flex items-center gap-2 mt-6">
                            <input type="checkbox" id="require_timeout" name="require_timeout" value="1" class="rounded border-gray-300 text-blue-700 focus:ring-blue-700" @checked(old('require_timeout', $event->require_timeout))>
                            <x-input-label for="require_timeout" :value="__('Require time-out')" />
                        </div>
                        <div>
                            <x-input-label for="visibility" :value="__('Visibility')" />
                            <select id="visibility" name="visibility" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700">
                                @foreach (['students' => 'Students', 'officers' => 'Officers only', 'all' => 'All roles'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('visibility', $event->visibility) === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('visibility')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="is_ceit_wide" name="is_ceit_wide" value="1" class="mt-1 rounded border-gray-300 text-blue-700 focus:ring-blue-700" @checked(old('is_ceit_wide', $event->is_ceit_wide))>
                        <div>
                            <x-input-label for="is_ceit_wide" :value="__('CEIT-wide event')" />
                            <p class="text-xs text-slate-500">Check only if the event is intended for all CEIT students (department scope).</p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <x-primary-button>{{ __('Update Event') }}</x-primary-button>

                        <form method="POST" action="{{ route('events.cancel', $event) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-semibold" onclick="return confirm('Cancel this event?')">Cancel event</button>
                        </form>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
