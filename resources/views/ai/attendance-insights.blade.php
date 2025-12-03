<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-800">AI Insights</p>
                <h2 class="font-semibold text-xl text-blue-950 leading-tight">
                    Attendance Q&A
                </h2>
            </div>
            <a href="{{ url()->previous() }}" class="text-sm text-blue-800 hover:text-blue-900 font-semibold">Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="card p-5 space-y-4">
                <form id="ai-form" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-blue-900">Mode</label>
                            <select name="mode" id="mode" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm">
                                <option value="event">Event</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                        <div id="event-select">
                            <label class="text-xs font-semibold text-blue-900">Event</label>
                            <select name="event_id" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm">
                                @foreach ($events as $ev)
                                    <option value="{{ $ev->id }}" @selected(($selectedEventId ?? null) == $ev->id)>
                                        {{ $ev->title }} ({{ $ev->start_at->format('M d, Y') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="student-fields" class="hidden grid grid-cols-1 sm:grid-cols-3 gap-3 sm:col-span-2">
                            <div>
                                <label class="text-xs font-semibold text-blue-900">Student ID</label>
                                <input type="text" name="student_id" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm" placeholder="e.g., 2021-12345">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-blue-900">Start date</label>
                                <input type="date" name="start_date" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-blue-900">End date</label>
                                <input type="date" name="end_date" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-blue-900">Question</label>
                        <textarea name="question" rows="3" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm" placeholder="e.g., Which society had the highest attendance in this event?"></textarea>
                    </div>

                    <div class="flex items-center gap-3">
                        <button id="ask-btn" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">Ask AI</button>
                        <span id="ask-status" class="text-sm text-slate-500"></span>
                    </div>
                </form>
            </div>

            <div id="ai-answer" class="card p-5 hidden">
                <h3 class="text-lg font-semibold text-blue-900">AI Answer</h3>
                <div id="ai-answer-text" class="mt-2 text-slate-800 whitespace-pre-wrap"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modeSelect = document.getElementById('mode');
            const eventSelect = document.getElementById('event-select');
            const studentFields = document.getElementById('student-fields');
            const form = document.getElementById('ai-form');
            const askBtn = document.getElementById('ask-btn');
            const statusEl = document.getElementById('ask-status');
            const answerCard = document.getElementById('ai-answer');
            const answerText = document.getElementById('ai-answer-text');

            // Prefill event from query (selected in blade) – just ensure select visible on load.
            modeSelect.addEventListener('change', () => {
                if (modeSelect.value === 'event') {
                    eventSelect.classList.remove('hidden');
                    studentFields.classList.add('hidden');
                } else {
                    eventSelect.classList.add('hidden');
                    studentFields.classList.remove('hidden');
                }
            });

            // Trigger once to ensure correct visibility on load.
            modeSelect.dispatchEvent(new Event('change'));

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                askBtn.disabled = true;
                statusEl.textContent = 'Thinking...';
                answerCard.classList.add('hidden');

                const formData = new FormData(form);
                try {
                    const resp = await fetch('{{ route('ai.insights.ask') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    const data = await resp.json();
                    if (!resp.ok) throw new Error(data.message || 'AI request failed');
                    answerText.textContent = data.answer;
                    answerCard.classList.remove('hidden');
                    statusEl.textContent = 'Done.';
                } catch (err) {
                    statusEl.textContent = err.message;
                } finally {
                    askBtn.disabled = false;
                }
            });
        });
    </script>
</x-app-layout>
