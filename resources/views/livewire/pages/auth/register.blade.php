<?php

use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $id_number = '';
    public string $email = '';
    public string $course = '';
    public string $year_level = '';
    public ?string $section = null;
    public ?int $society_id = null;
    public string $position = 'Member';
    public string $qr_raw_text = '';
    public string $password = '';
    public string $password_confirmation = '';
    public $societies = [];
    public bool $is_society_officer = false;
    public bool $is_lsg_officer = false;
    public array $coursesBySociety = [
        'psits' => ['BSCS', 'BLIS', 'BSInfoSys'],
        'pice' => ['BSCE'],
        'icpep' => ['BSCpE'],
        'jiecep' => ['BSECE'],
        'psabe' => ['BSABE'],
    ];

    public function mount(): void
    {
        $this->societies = Society::orderBy('abbreviation')->get();
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'id_number' => ['required', 'string', 'max:50', 'unique:users,id_number'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'course' => ['required', 'string', 'max:100'],
            'year_level' => ['required', 'string', 'max:10'],
            'section' => ['nullable', 'string', 'max:50'],
            'society_id' => ['required', 'integer', 'exists:societies,id'],
            'position' => ['required', 'string', 'max:100'],
            'qr_raw_text' => ['nullable', 'string', 'max:500', 'unique:users,qr_raw_text'],
            'is_society_officer' => ['boolean'],
            'is_lsg_officer' => ['boolean'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $studentRole = Role::where('slug', 'student')->firstOrFail();

        $userData = [
            'name' => $validated['name'],
            'id_number' => $validated['id_number'],
            'email' => $validated['email'],
            'course' => $validated['course'],
            'year_level' => $validated['year_level'],
            'section' => $validated['section'] ?? null,
            'department' => 'CEIT',
            'qr_raw_text' => $validated['qr_raw_text'] ?: null,
            'is_society_officer' => $this->is_society_officer,
            'is_lsg_officer' => $this->is_lsg_officer,
            'role_id' => $studentRole->id,
            'password' => Hash::make($validated['password']),
        ];

        event(new Registered($user = User::create($userData)));

        $user->societies()->sync([
            $validated['society_id'] => ['position' => $validated['position']],
        ]);

        Auth::login($user);

        $this->redirect(route('student.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="space-y-6">
    <div class="text-center">
        <p class="text-sm uppercase tracking-wide text-blue-900 font-semibold">CEIT Digital Attendance</p>
        <h1 class="mt-1 text-2xl font-semibold text-blue-950">Student Registration</h1>
        <p class="mt-2 text-sm text-slate-600">Register once, pick your society, and use your ID QR for events.</p>
    </div>

    <form wire:submit="register" class="space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="__('Full name')" />
                <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="id_number" :value="__('ID Number')" />
                <x-text-input wire:model="id_number" id="id_number" class="block mt-1 w-full" type="text" name="id_number" required autocomplete="id_number" />
                <x-input-error :messages="$errors->get('id_number')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="society_id" :value="__('Society')" />
                <select wire:model="society_id" id="society_id" name="society_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    <option value="">{{ __('Select your society') }}</option>
                    @foreach ($societies as $society)
                        <option value="{{ $society->id }}">{{ $society->abbreviation }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('society_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="course" :value="__('Course')" />
                <select wire:model="course" id="course" name="course" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700" disabled>
                    <option value="">{{ __('Select society first') }}</option>
                </select>
                <x-input-error :messages="$errors->get('course')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="section" :value="__('Section (optional)')" />
                <x-text-input wire:model="section" id="section" class="block mt-1 w-full" type="text" name="section" autocomplete="off" placeholder="e.g., A or 1" />
                <x-input-error :messages="$errors->get('section')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="year_level" :value="__('Year Level')" />
                <select wire:model="year_level" id="year_level" name="year_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    <option value="">{{ __('Select year level') }}</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
                <x-input-error :messages="$errors->get('year_level')" class="mt-2" />
            </div>

                <div class="space-y-2 mt-4">
                    <div class="flex items-center gap-2">
                        <input wire:model="is_society_officer" id="is_society_officer" type="checkbox" class="rounded border-gray-300 text-blue-700 shadow-sm focus:ring-blue-700" />
                        <x-input-label for="is_society_officer" :value="__('I am a Society Officer')" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input wire:model="is_lsg_officer" id="is_lsg_officer" type="checkbox" class="rounded border-gray-300 text-blue-700 shadow-sm focus:ring-blue-700" />
                        <x-input-label for="is_lsg_officer" :value="__('I am an LSG Officer')" />
                    </div>
                </div>

            <div>
                <x-input-label for="position" :value="__('Position')" />
                <x-text-input wire:model="position" id="position" class="block mt-1 w-full" type="text" name="position" required placeholder="e.g., Member, PSITS-President" />
                <x-input-error :messages="$errors->get('position')" class="mt-2" />
            </div>

        </div>

        


        <div class="p-4 border border-slate-100 rounded-lg bg-white" wire:ignore>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h4 class="text-sm font-semibold text-blue-900">Scan your ID QR (optional)</h4>
                    <p class="text-xs text-slate-600">Camera permission required. We only store the raw text.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" id="start-qr-scan" class="px-3 py-2 text-sm font-semibold rounded-md bg-[var(--color-psits-800)] text-white hover:bg-[var(--color-psits-700)]">Start scan</button>
                    <button type="button" id="stop-qr-scan" class="px-3 py-2 text-sm font-semibold rounded-md bg-slate-200 text-blue-900 hover:bg-slate-300">Stop</button>
                </div>
            </div>
            <div id="qr-reader" class="mt-3 rounded-lg border border-slate-200 overflow-hidden"></div>
            <div id="qr-status" class="mt-2 text-xs text-slate-600"></div>
        </div>

        <div>
            <x-input-label for="qr_raw_text" :value="__('QR raw text (optional)')" />
            <x-text-input wire:model="qr_raw_text" id="qr_raw_text" class="block mt-1 w-full" type="text" name="qr_raw_text" placeholder="Paste QR value if available" />
            <x-input-error :messages="$errors->get('qr_raw_text')" class="mt-2" />
            <p class="text-xs text-slate-500 mt-1">We store the exact QR text for faster event scanning. Must be unique.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a class="text-sm text-blue-800 hover:text-blue-900 font-semibold" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered? Login') }}
            </a>

            <x-primary-button>
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script src="https://unpkg.com/html5-qrcode" defer></script>
    <script>
        const initCourseSelector = () => {
            const societySelect = document.getElementById('society_id');
            const courseSelect = document.getElementById('course');
            if (!societySelect || !courseSelect) return;

            // Avoid multiple bindings
            if (courseSelect.dataset.bound === 'true') return;
            courseSelect.dataset.bound = 'true';

            const courseMap = {
                1: ['BSCS', 'BLIS', 'BSInfoSys'],   // PSITS
                2: ['BSCE'],                        // PICE
                3: ['BSCpE'],                       // ICPEP
                4: ['BSECE'],                       // JIECEP
                5: ['BSABE'],                       // PSABE
            };

            const buildOptions = (societyId) => {
                courseSelect.innerHTML = '';
                if (!societyId || !courseMap[societyId]) {
                    courseSelect.append(new Option('Select society first', ''));
                    courseSelect.disabled = true;
                    return;
                }
                courseSelect.append(new Option('Select course', ''));
                courseMap[societyId].forEach((c) => {
                    courseSelect.append(new Option(c, c));
                });
                courseSelect.disabled = false;
            };

            // initial hydrate (use existing selected values if any)
            buildOptions(parseInt(societySelect.value || 0, 10));
            if (!courseSelect.value && !courseSelect.disabled) {
                courseSelect.value = @json(old('course', $course ?? ''));
            }

            societySelect.addEventListener('change', (e) => {
                buildOptions(parseInt(e.target.value || 0, 10));
                courseSelect.value = '';
                courseSelect.dispatchEvent(new Event('input', { bubbles: true }));
                courseSelect.dispatchEvent(new Event('change', { bubbles: true }));
            });
        };

        document.addEventListener('DOMContentLoaded', initCourseSelector);
        document.addEventListener('livewire:navigated', initCourseSelector);

        const initRegisterScanner = () => {
            if (!window.__registerQr) {
                window.__registerQr = { scanner: null, bound: false };
            }

            const readerId = 'qr-reader';
            const startBtn = document.getElementById('start-qr-scan');
            const stopBtn = document.getElementById('stop-qr-scan');
            const statusEl = document.getElementById('qr-status');

            function setStatus(msg, isError = false) {
                if (!statusEl) return;
                statusEl.textContent = msg;
                statusEl.className = 'mt-2 text-xs ' + (isError ? 'text-red-600' : 'text-slate-600');
            }

            async function startScanner() {
                if (!window.Html5Qrcode || !window.Html5QrcodeSupportedFormats) {
                    setStatus('Scanner library not loaded.', true);
                    return;
                }

                // stop any previous instance
                if (window.__registerQr.scanner) {
                    try {
                        await window.__registerQr.scanner.stop();
                        window.__registerQr.scanner.clear();
                    } catch (e) {}
                    window.__registerQr.scanner = null;
                }

                const container = document.getElementById(readerId);
                if (container) {
                    container.innerHTML = '';
                }

                const scanner = new Html5Qrcode(readerId);
                window.__registerQr.scanner = scanner;

                try {
                    const devices = await Html5Qrcode.getCameras();
                    if (!devices || !devices.length) {
                        setStatus('No camera found.', true);
                        return;
                    }
                    const cameraId = devices[0].id;
                    await scanner.start(
                        cameraId,
                        {
                            fps: 10,
                            qrbox: 280,
                            formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                        },
                        (decodedText) => {
                            const input = document.getElementById('qr_raw_text');
                            if (input) {
                                input.value = decodedText;
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            setStatus('Captured: ' + decodedText);
                            stopScanner();
                        },
                        () => {}
                    );
                    setStatus('Scanning... show your ID QR to the camera.');
                } catch (err) {
                    setStatus('Unable to start scanner: ' + err, true);
                }
            }

            async function stopScanner() {
                const scanner = window.__registerQr.scanner;
                if (scanner) {
                    try {
                        await scanner.stop();
                        scanner.clear();
                    } catch (e) {}
                    window.__registerQr.scanner = null;
                    setStatus('Scanner stopped.');
                }
            }

            if (!window.__registerQr.bound) {
                startBtn?.addEventListener('click', startScanner);
                stopBtn?.addEventListener('click', stopScanner);
                window.__registerQr.bound = true;
            }
        };

        document.addEventListener('DOMContentLoaded', initRegisterScanner);
        document.addEventListener('livewire:navigated', initRegisterScanner);
    </script>
</div>
