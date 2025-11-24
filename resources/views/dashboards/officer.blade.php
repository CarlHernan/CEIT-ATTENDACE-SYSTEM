<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-800">Society Officer</p>
                <h2 class="font-semibold text-xl text-blue-950 leading-tight">
                    {{ __('Officer Dashboard') }}
                </h2>
            </div>
            <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">Events</span>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid gap-6 sm:grid-cols-2">
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-blue-900">Create event</h3>
                <p class="mt-2 text-sm text-slate-600">Set up your society event details and attendance mode.</p>
            </div>
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-blue-900">Attendance tools</h3>
                <p class="mt-2 text-sm text-slate-600">Launch QR scanner or manual ID entry for quick check-ins.</p>
            </div>
        </div>
    </div>
</x-app-layout>
