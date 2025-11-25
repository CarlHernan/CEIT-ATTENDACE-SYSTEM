<?php

use App\Models\User;
use App\Models\Society;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $course = '';
    public ?string $section = null;
    public string $year_level = '';
    public ?int $society_id = null;
    public string $position = 'Member';
    public $societies = [];
    public array $coursesBySociety = [
        'psits' => ['BSCS', 'BLIS', 'BSInfoSys'],
        'pice' => ['BSCE'],
        'icpep' => ['BSCpE'],
        'jiecep' => ['BSECE'],
        'psabe' => ['BSABE'],
    ];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->course = Auth::user()->course ?? '';
        $this->section = Auth::user()->section;
        $this->year_level = Auth::user()->year_level ?? '';
        $this->society_id = Auth::user()->societies()->first()?->id;
        $this->position = Auth::user()->societies()->first()?->pivot?->position ?? 'Member';
        $this->societies = Society::orderBy('abbreviation')->get();
    }

    public function updatedSocietyId($value): void
    {
        // Reset dependent course when society changes so options refresh cleanly.
        $this->course = '';
    }

    public function getCourseOptionsProperty(): array
    {
        $society = $this->society_id ? Society::find($this->society_id) : null;
        $slug = $society?->slug;

        return $slug ? ($this->coursesBySociety[$slug] ?? []) : [];
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'course' => ['required', 'string', 'max:100', function ($attribute, $value, $fail) {
                $society = Society::find($this->society_id);
                if (! $society) {
                    $fail('Select a society first.');
                    return;
                }
                $allowed = $this->coursesBySociety[$society->slug] ?? [];
                if (! in_array($value, $allowed, true)) {
                    $fail('Course must match your society.');
                }
            }],
            'section' => ['nullable', 'string', 'max:50'],
            'year_level' => ['required', 'string', 'max:10'],
            'society_id' => ['required', 'integer', 'exists:societies,id'],
            'position' => ['required', 'string', 'max:100'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'course' => $validated['course'],
            'section' => $validated['section'],
            'year_level' => $validated['year_level'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $user->societies()->sync([
            $validated['society_id'] => ['position' => $validated['position']],
        ]);

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile and CEIT details.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="society_id" :value="__('Society')" />
                <select wire:model.live="society_id" wire:change="$set('course', '')" id="society_id" name="society_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    <option value="">{{ __('Select your society') }}</option>
                    @foreach ($societies as $society)
                        <option value="{{ $society->id }}">{{ $society->abbreviation }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('society_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="position" :value="__('Position')" />
                <x-text-input wire:model="position" id="position" class="mt-1 block w-full" type="text" name="position" required placeholder="e.g., Member" />
                <x-input-error :messages="$errors->get('position')" class="mt-2" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <x-input-label for="course" :value="__('Course')" />
                <select wire:model="course" id="course" name="course" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700" @disabled(!$society_id) wire:key="course-{{ $society_id ?? 'none' }}">
                    <option value="">{{ $society_id ? 'Select course' : 'Select society first' }}</option>
                    @foreach ($this->courseOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('course')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="section" :value="__('Section (optional)')" />
                <x-text-input wire:model="section" id="section" class="mt-1 block w-full" type="text" name="section" placeholder="e.g., A or B" />
                <x-input-error :messages="$errors->get('section')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="year_level" :value="__('Year Level')" />
                <x-text-input wire:model="year_level" id="year_level" class="mt-1 block w-full" type="text" name="year_level" required autocomplete="off" />
                <x-input-error :messages="$errors->get('year_level')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
