<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/ceit-logo.png') }}" alt="CEIT logo" class="h-12 w-12 rounded-full object-cover">
            <div>
                <h2 class="text-2xl font-semibold text-[var(--color-ink-900)] leading-tight">
                    {{ __('Admin Dashboard') }}
                </h2>
                <p class="text-sm text-slate-600">System administration and controls</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl bg-[var(--color-navy-900)] text-white p-6 shadow-[var(--shadow-card-strong)]">
                    <p class="text-sm font-semibold text-white">Manage societies</p>
                    <p class="mt-2 text-sm text-white/80">Maintain society records and officer access.</p>
                </div>
                <div class="rounded-2xl bg-[var(--color-navy-900)] text-white p-6 shadow-[var(--shadow-card-strong)]">
                    <p class="text-sm font-semibold text-white">System settings</p>
                    <p class="mt-2 text-sm text-white/80">Configure integrations, AI, and automation defaults.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
