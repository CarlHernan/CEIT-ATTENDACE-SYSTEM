<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-800">{{ $event->scope_label }}</p>
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
                            <x-input-label for="society_id" :value="__('Society')" />
                            @if ($role === 'lsg_officer' || $event->society_id === null)
                                <input type="hidden" name="society_id" value="">
                                <x-text-input id="society_readonly" type="text" class="mt-1 block w-full" value="{{ old('society_readonly', 'CEIT-LSG event') }}" readonly />
                            @elseif ($role === 'officer')
                                @php
                                    $selectedSocietyId = old('society_id', $event->society_id ?? $defaultSocietyId);
                                    $selectedSociety = $societies->firstWhere('id', $selectedSocietyId) ?? $societies->first();
                                @endphp
                                <input type="hidden" name="society_id" value="{{ $selectedSocietyId }}">
                                <x-text-input id="society_readonly" type="text" class="mt-1 block w-full" value="{{ $selectedSociety?->abbreviation }}" readonly />
                                <x-input-error :messages="$errors->get('society_id')" class="mt-2" />
                            @else
                                <select id="society_id" name="society_id" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700">
                                    @foreach ($societies as $society)
                                        <option value="{{ $society->id }}" @selected(old('society_id', $event->society_id) == $society->id)>{{ $society->abbreviation }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('society_id')" class="mt-2" />
                            @endif
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="attendance_mode" :value="__('Attendance Mode')" />
                                <select id="attendance_mode" name="attendance_mode" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700">
                                    @foreach (['qr' => 'QR scanning', 'manual' => 'Manual ID', 'hybrid' => 'Hybrid'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('attendance_mode', $event->attendance_mode ?? 'hybrid') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('attendance_mode')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="late_threshold_minutes" :value="__('Late threshold (minutes)')" />
                                <x-text-input id="late_threshold_minutes" name="late_threshold_minutes" type="number" min="0" max="720" class="mt-1 block w-full" value="{{ old('late_threshold_minutes', $event->late_threshold_minutes ?? 5) }}" />
                                <x-input-error :messages="$errors->get('late_threshold_minutes')" class="mt-2" />
                            </div>
                        </div>
                        

                        
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="audience" :value="__('Audience')" />
                            <select id="audience" name="audience" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700" data-toggle-notes>
                                @php
                                    $labels = [
                                        'society_members' => 'Society members',
                                        'society_officers' => 'Society officers only',
                                        'others' => 'Others (see note)',
                                        'ceit_students' => 'All CEIT students',
                                        'lsg_officers' => 'CEIT-LSG officers',
                                        'all_officers' => 'All officers (society + LSG)',
                                    ];
                                @endphp
                                @foreach ($audiences as $aud)
                                    <option value="{{ $aud }}" @selected(old('audience', $event->audience) === $aud)>{{ $labels[$aud] ?? ucwords(str_replace('_',' ', $aud)) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('audience')" class="mt-2" />
                        </div>

                        <div data-notes-container class="{{ old('audience', $event->audience) === 'others' ? '' : 'hidden' }}">
                            <x-input-label for="audience_notes" :value="__('Audience notes (shown to students)')" />
                            <x-text-input id="audience_notes" name="audience_notes" type="text" class="mt-1 block w-full" value="{{ old('audience_notes', $event->audience_notes) }}" placeholder="e.g., Officers + volunteers" />
                            <x-input-error :messages="$errors->get('audience_notes')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <x-primary-button>{{ __('Update Event') }}</x-primary-button>
                    </div>
                </form>

                <div class="mt-4 flex justify-end">
                    <form method="POST" action="{{ route('events.cancel', $event) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-semibold" onclick="return confirm('Cancel this event?')">Cancel event</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const audienceSelect = document.querySelector('[data-toggle-notes]');
            const notesField = document.querySelector('[data-notes-container]');

            if (!audienceSelect || !notesField) return;

            audienceSelect.addEventListener('change', (event) => {
                if (event.target.value === 'others') {
                    notesField.classList.remove('hidden');
                } else {
                    notesField.classList.add('hidden');
                    const input = notesField.querySelector('input');
                    if (input) input.value = '';
                }
            });
        });
    </script>
</x-app-layout>
