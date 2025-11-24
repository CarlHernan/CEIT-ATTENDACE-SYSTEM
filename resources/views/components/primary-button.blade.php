<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[var(--color-psits-800)] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-wide hover:bg-[var(--color-psits-700)] focus:bg-[var(--color-psits-700)] active:bg-[var(--color-psits-900)] focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
