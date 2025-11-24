<?php

use App\Livewire\Forms\LoginForm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;
    public string $portal = 'general';
    public string $role_preference = 'officer';

    public function mount(): void
    {
        if (request()->routeIs('officer.login')) {
            $this->portal = 'privileged';
            $this->role_preference = 'officer';
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        $user = Auth::user();

        if ($this->portal === 'privileged') {
            $this->validate([
                'role_preference' => ['required', 'in:officer,lsg,admin'],
            ]);

            $allowed = ['officer', 'lsg_officer', 'admin'];
            $slug = $user?->role?->slug;

            if (! $slug || ! in_array($slug, $allowed, true)) {
                Auth::logout();

                throw ValidationException::withMessages([
                    'form.email' => __('This portal is for officers/LSG/admin only.'),
                ]);
            }

            if ($this->role_preference === 'officer' && ! in_array($slug, ['officer', 'admin'], true)) {
                Auth::logout();

                throw ValidationException::withMessages([
                    'form.email' => __('Select the correct portal for your role.'),
                ]);
            }

            if ($this->role_preference === 'lsg' && ! in_array($slug, ['lsg_officer', 'admin'], true)) {
                Auth::logout();

                throw ValidationException::withMessages([
                    'form.email' => __('Select the correct portal for your role.'),
                ]);
            }
        }

        Session::regenerate();

        $this->redirectIntended(default: route($this->routeFor($user), absolute: false), navigate: true);
    }

    protected function routeFor(User $user): string
    {
        return match ($user->role?->slug) {
            'admin' => 'admin.dashboard',
            'lsg_officer' => 'lsg.dashboard',
            'officer' => 'officer.dashboard',
            default => 'student.dashboard',
        };
    }
}; ?>

<div class="space-y-6">
    <div class="text-center">
        <p class="text-sm uppercase tracking-wide text-blue-900 font-semibold">CEIT Digital Attendance</p>
        <h1 class="mt-1 text-2xl font-semibold text-blue-950">
            {{ $portal === 'privileged' ? 'Officer / LSG Login' : 'Student Login' }}
        </h1>
        <p class="mt-2 text-sm text-slate-600">
            {{ $portal === 'privileged' ? 'For society officers, CEIT-LSG, and admins.' : 'Login with your CEIT account to access events.' }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        @if ($portal === 'privileged')
            <div class="border rounded-md p-3 bg-slate-50">
                <p class="text-sm font-semibold text-slate-800 mb-2">Login as</p>
                <div class="flex flex-wrap gap-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="radio" wire:model="role_preference" value="officer" class="text-blue-700 border-gray-300 focus:ring-blue-700">
                        Society Officer
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="radio" wire:model="role_preference" value="lsg" class="text-blue-700 border-gray-300 focus:ring-blue-700">
                        CEIT-LSG
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="radio" wire:model="role_preference" value="admin" class="text-blue-700 border-gray-300 focus:ring-blue-700">
                        Admin
                    </label>
                </div>
                <x-input-error :messages="$errors->get('role_preference')" class="mt-2" />
            </div>
        @endif

        <div class="flex items-center justify-between mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <div class="flex items-center justify-between text-sm">
            @if ($portal === 'general')
                <a class="text-blue-800 font-semibold hover:text-blue-900" href="{{ route('officer.login') }}" wire:navigate>
                    {{ __('Login as Officer / LSG') }}
                </a>
            @else
                <a class="text-blue-800 font-semibold hover:text-blue-900" href="{{ route('login') }}" wire:navigate>
                    {{ __('Back to student login') }}
                </a>
            @endif
            <a class="text-slate-600 hover:text-slate-800" href="{{ route('register') }}" wire:navigate>
                {{ __('Need an account? Register') }}
            </a>
        </div>
    </form>
</div>
