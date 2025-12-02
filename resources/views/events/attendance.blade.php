<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-800">{{ $event->society->abbreviation ?? 'CEIT-LSG' }}</p>
                <h2 class="font-semibold text-xl text-blue-950 leading-tight">
                    {{ __('Attendance for ') . $event->title }}
                </h2>
                <p class="text-sm text-slate-600">{{ $event->start_at->format('M d, Y g:i A') }}</p>
            </div>
            <a href="{{ route('events.show', $event) }}" class="text-sm text-blue-800 hover:text-blue-900 font-semibold">Back to event</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="card p-5 lg:col-span-2 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="card p-3 bg-[var(--color-psits-100)] border-none shadow-none">
                            <p class="text-xs font-semibold text-blue-900 uppercase">Total</p>
                            <p class="text-2xl font-semibold text-blue-950">{{ $counts['total'] ?? 0 }}</p>
                        </div>
                        <div class="card p-3 bg-white shadow-sm">
                            <p class="text-xs font-semibold text-blue-900 uppercase">Time In</p>
                            <p class="text-xl font-semibold text-blue-950">{{ $counts['time_in'] ?? 0 }}</p>
                        </div>
                        <div class="card p-3 bg-white shadow-sm">
                            <p class="text-xs font-semibold text-blue-900 uppercase">Time Out</p>
                            <p class="text-xl font-semibold text-blue-950">{{ $counts['time_out'] ?? 0 }}</p>
                        </div>
                        <div class="card p-3 bg-white shadow-sm">
                            <p class="text-xs font-semibold text-blue-900 uppercase">Society</p>
                            <p class="text-xl font-semibold text-blue-950">{{ $event->society->abbreviation ?? 'CEIT-LSG' }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2">
                            <label class="font-semibold text-sm text-blue-900">Action</label>
                            <select id="mode" class="rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm">
                                <option value="time_in">Time In</option>
                                <option value="time_out">Time Out</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="font-semibold text-sm text-blue-900">Method</label>
                            <select id="method" class="rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm">
                                <option value="qr">QR</option>
                                <option value="manual">Manual</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="border rounded-lg p-4 bg-white space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-blue-900">QR Scan</h3>
                                <div class="flex items-center gap-2">
                                    <button id="qr-start" class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">Start</button>
                                    <button id="qr-stop" class="px-3 py-1 text-sm bg-slate-200 text-slate-800 rounded-lg hover:bg-slate-300">Stop</button>
                                </div>
                            </div>
                            <div id="qr-reader" class="border rounded-lg overflow-hidden min-h-[200px]"></div>
                            <p class="text-xs text-slate-500">Uses Html5Qrcode (camera permission required).</p>
                            <p id="qr-status" class="text-xs text-slate-600"></p>
                        </div>
                        <div class="border rounded-lg p-4 bg-white space-y-3">
                            <h3 class="text-lg font-semibold text-blue-900">Manual Entry</h3>
                            <label class="text-sm text-blue-900 font-medium" for="id_number">ID Number</label>
                            <input id="id_number" type="text" class="w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700" placeholder="Enter ID number">
                            <button id="manual-submit" class="inline-flex items-center px-4 py-2 bg-[var(--color-psits-800)] text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-[var(--color-psits-700)]">
                                Record Attendance
                            </button>
                            <p class="text-xs text-slate-500">Use when QR is damaged.</p>
                        </div>
                    </div>
                </div>

                <div class="card p-5">
                    <h3 class="text-lg font-semibold text-blue-900">Last Scan</h3>
                    <div id="last-result" class="mt-3 text-sm text-slate-700">
                        Waiting for scan or manual entry...
                    </div>
                    <div id="last-error" class="mt-2 text-sm text-red-600 hidden"></div>
                </div>
            </div>

            <div class="card p-5 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-blue-900">Attendance Records</h3>
                        <span class="text-xs text-slate-500">Apply filters and export results</span>
                    </div>
                    <a href="{{ route('events.attendance.export', $event) }}?{{ http_build_query($filters) }}" class="inline-flex items-center px-4 py-2 bg-[var(--color-psits-800)] text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-[var(--color-psits-700)]">
                        Export Excel
                    </a>
                </div>

                <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <input type="hidden" name="page" value="1">
                    <div>
                        <label class="text-xs font-semibold text-blue-900">Course</label>
                        <input type="text" name="course" value="{{ $filters['course'] }}" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm" placeholder="e.g., BSIT">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-blue-900">Year</label>
                        <input type="text" name="year_level" value="{{ $filters['year_level'] }}" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm" placeholder="e.g., 3">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-blue-900">Society</label>
                        <select name="society" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm">
                            <option value="">Any</option>
                            @foreach ($societies as $society)
                                <option value="{{ $society->id }}" @selected($filters['society'] == $society->id)>{{ $society->abbreviation }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-4 py-2 bg-[var(--color-psits-800)] text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-[var(--color-psits-700)]">Apply</button>
                        <a href="{{ route('events.attendance', $event) }}" class="px-3 py-2 rounded-lg border text-sm font-semibold text-blue-900">Reset</a>
                    </div>
                </form>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                    <div class="border border-slate-100 rounded-lg p-3">
                        <p class="text-xs font-semibold text-blue-900 uppercase">By Course</p>
                        <ul class="mt-2 space-y-1 text-sm text-slate-700">
                            @forelse ($courseCounts as $row)
                                <li class="flex justify-between"><span>{{ $row->course ?? 'N/A' }}</span><span class="font-semibold">{{ $row->total }}</span></li>
                            @empty
                                <li class="text-slate-500 text-xs">No data</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="border border-slate-100 rounded-lg p-3">
                        <p class="text-xs font-semibold text-blue-900 uppercase">By Year</p>
                        <ul class="mt-2 space-y-1 text-sm text-slate-700">
                            @forelse ($yearCounts as $row)
                                <li class="flex justify-between"><span>{{ $row->year_level ?? 'N/A' }}</span><span class="font-semibold">{{ $row->total }}</span></li>
                            @empty
                                <li class="text-slate-500 text-xs">No data</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-slate-600">
                            <tr>
                                <th class="py-2 pr-4">Name</th>
                                <th class="py-2 pr-4">Course/Year</th>
                                <th class="py-2 pr-4">Time In</th>
                                <th class="py-2 pr-4">Time Out</th>
                                <th class="py-2 pr-4">Method</th>
                                <th class="py-2 pr-4">Recorded By</th>
                            </tr>
                        </thead>
                        <tbody id="attendance-rows" class="divide-y divide-slate-100">
                            @foreach ($records as $record)
                                <tr data-student="{{ $record->user->id }}">
                                    <td class="py-2 pr-4 text-blue-950 font-semibold student-name">{{ $record->user->name }}</td>
                                    <td class="py-2 pr-4 text-slate-700 student-course">{{ $record->user->course }} / {{ $record->user->year_level }}</td>
                                    <td class="py-2 pr-4 text-slate-700 student-time-in">{{ optional($record->time_in)->format('M d, Y g:i A') ?: '—' }}</td>
                                    <td class="py-2 pr-4 text-slate-700 student-time-out">{{ optional($record->time_out)->format('M d, Y g:i A') ?: '—' }}</td>
                                    <td class="py-2 pr-4 capitalize text-slate-700 student-method">{{ $record->method }}</td>
                                    <td class="py-2 pr-4 text-slate-700 student-recorder">{{ $record->recorder->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $records->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="toast" class="hidden fixed bottom-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm text-white bg-green-600"></div>

    <script src="https://unpkg.com/html5-qrcode" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modeEl = document.getElementById('mode');
            const methodEl = document.getElementById('method');
            const lastResult = document.getElementById('last-result');
            const lastError = document.getElementById('last-error');
            const idInput = document.getElementById('id_number');
            const manualBtn = document.getElementById('manual-submit');
            const csrf = '{{ csrf_token() }}';
            const recordUrl = '{{ route('events.attendance.record', $event) }}';
            const toast = document.getElementById('toast');

            function showToast(message, type = 'success') {
                if (!toast) return;
                toast.textContent = message;
                toast.classList.remove('hidden');
                toast.style.backgroundColor = type === 'error' ? '#dc2626' : '#16a34a';
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 2000);
            }

            function upsertRow(data) {
                const tbody = document.getElementById('attendance-rows');
                if (!tbody) return;

                const existing = tbody.querySelector(`tr[data-student="${data.student.id}"]`);
                const rowHtml = `
                    <td class="py-2 pr-4 text-blue-950 font-semibold student-name">${data.student.name}</td>
                    <td class="py-2 pr-4 text-slate-700 student-course">${data.student.course} / ${data.student.year_level}</td>
                    <td class="py-2 pr-4 text-slate-700 student-time-in">${data.record.time_in ?? '—'}</td>
                    <td class="py-2 pr-4 text-slate-700 student-time-out">${data.record.time_out ?? '—'}</td>
                    <td class="py-2 pr-4 capitalize text-slate-700 student-method">${data.record.method}</td>
                    <td class="py-2 pr-4 text-slate-700 student-recorder">${data.recorded_by ?? '—'}</td>
                `;

                if (existing) {
                    existing.innerHTML = rowHtml;
                } else {
                    const tr = document.createElement('tr');
                    tr.setAttribute('data-student', data.student.id);
                    tr.innerHTML = rowHtml;
                    tbody.prepend(tr);
                }
            }

            function showSuccess(data, rawDisplay) {
                lastError.classList.add('hidden');
                const statusLine = data.record.time_in && data.record.time_out
                    ? `<span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Completed</span>`
                    : `<span class="inline-flex items-center px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-semibold">${data.record.time_in ? 'Time-out needed' : 'Time-in recorded'}</span>`;

                lastResult.innerHTML = `
                    <div class="flex items-center gap-2">
                        <div class="text-green-700 font-semibold">${data.student.name}</div>
                        ${statusLine}
                    </div>
                    <div class="text-slate-700">${data.student.course} / ${data.student.year_level}</div>
                    <div class="text-slate-600 text-sm">Time In: ${data.record.time_in ?? '—'}</div>
                    <div class="text-slate-600 text-sm">Time Out: ${data.record.time_out ?? '—'}</div>
                    <div class="text-slate-600 text-sm capitalize">Method: ${data.record.method}</div>
                    <div class="text-slate-600 text-sm mt-1">Raw: ${rawDisplay ?? data.raw_value ?? '—'}</div>
                `;
                upsertRow(data);
                showToast('Attendance recorded');
            }

            function showError(message, rawDisplay) {
                lastError.textContent = message;
                lastError.classList.remove('hidden');
                if (rawDisplay) {
                    lastResult.innerHTML = `<div class="text-slate-700 text-sm">Raw: ${rawDisplay}</div>`;
                }
                showToast(message, 'error');
            }

            async function recordAttendance(payload, rawDisplay = null) {
                try {
                    const response = await fetch(recordUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        showError(data.message || 'Failed', rawDisplay || data.raw_value);
                        return;
                    }
                    showSuccess(data, rawDisplay || data.raw_value);
                } catch (err) {
                    showError(err.message, rawDisplay);
                }
            }

            manualBtn.addEventListener('click', () => {
                const id = idInput.value.trim();
                if (!id) {
                    showError('Enter an ID number.');
                    return;
                }
                recordAttendance({
                    mode: modeEl.value,
                    method: methodEl.value,
                    id_number: id,
                }, `ID: ${id}`);
            });

            const qrStatus = document.getElementById('qr-status');
            const qrStartBtn = document.getElementById('qr-start');
            const qrStopBtn = document.getElementById('qr-stop');
            let qrInstance = null;
            let scannerActive = false;

            function setQrStatus(message) {
                if (qrStatus) qrStatus.textContent = message || '';
            }

            function ensureHtml5Qrcode() {
                return new Promise((resolve, reject) => {
                    if (window.Html5Qrcode) return resolve();
                    let script = document.getElementById('html5qrcode-script');
                    if (!script) {
                        script = document.createElement('script');
                        script.id = 'html5qrcode-script';
                        script.src = 'https://unpkg.com/html5-qrcode';
                        script.async = true;
                        document.head.appendChild(script);
                    }
                    script.onload = () => resolve();
                    script.onerror = () => reject(new Error('QR library failed to load'));
                });
            }

            async function startScanner() {
                try {
                    await ensureHtml5Qrcode();
                } catch (err) {
                    setQrStatus(err.message);
                    return;
                }
                if (scannerActive) return;

                // Reset container to avoid stale nodes that cause removeChild errors.
                const container = document.getElementById('qr-reader');
                if (container) {
                    container.innerHTML = '';
                }

                qrInstance = new Html5Qrcode('qr-reader');
                try {
                    await qrInstance.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: 250 },
                        (decodedText) => {
                            recordAttendance({
                                mode: modeEl.value,
                                method: 'qr',
                                qr_raw_text: decodedText,
                            }, decodedText);
                        }
                    );
                    scannerActive = true;
                    setQrStatus('Scanner running. Point camera at the QR.');
                } catch (err) {
                    setQrStatus('Unable to start scanner: ' + err.message);
                }
            }

            async function stopScanner() {
                if (qrInstance && scannerActive) {
                    try {
                        await qrInstance.stop();
                        await qrInstance.clear();
                    } catch (err) {
                        console.warn('Stop scanner error', err);
                    }
                }
                scannerActive = false;
                setQrStatus('Scanner stopped.');
            }

            if (qrStartBtn) {
                qrStartBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    startScanner();
                });
            }
            if (qrStopBtn) {
                qrStopBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    stopScanner();
                });
            }

            // Auto-start on load
            startScanner();
        });
    </script>
</x-app-layout>
