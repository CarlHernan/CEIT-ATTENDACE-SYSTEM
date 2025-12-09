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
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="card p-5 space-y-4">
                <form id="ai-form" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-blue-900">Mode</label>
                            <select name="mode" id="mode" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm">
                                <option value="event">Event</option>
                                <option value="student">Student</option>
                                <option value="freeform">Freeform</option>
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
                                <input id="student_id" type="text" name="student_id" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm" placeholder="e.g., 2021-12345">
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

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold text-blue-900">Ask a question</label>
                            <span id="typing-indicator" class="text-xs text-slate-500 hidden">AI is typing…</span>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-inner">
                            <div id="chat-messages" class="space-y-3 h-72 max-h-72 overflow-y-auto pr-1 text-sm text-slate-800 flex flex-col">
                                <p class="text-slate-500 text-sm">Start the conversation to see answers here.</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <textarea name="question" id="question" rows="2" class="w-full rounded-lg border-gray-300 focus:border-blue-700 focus:ring-blue-700 text-sm" placeholder="e.g., Which society had the highest attendance in this event?"></textarea>
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex flex-wrap gap-2 text-xs">
                                    <span class="text-slate-500">Suggestions:</span>
                                    <button type="button" data-suggest="Which society had the highest attendance in this event?" class="px-2 py-1 rounded-full bg-slate-200 text-slate-700 hover:bg-slate-300">Top society</button>
                                    <button type="button" data-suggest="Who attended this event?" class="px-2 py-1 rounded-full bg-slate-200 text-slate-700 hover:bg-slate-300">Who attended</button>
                                    <button type="button" data-suggest="How many were late?" class="px-2 py-1 rounded-full bg-slate-200 text-slate-700 hover:bg-slate-300">Late count</button>
                                    <button type="button" data-suggest="Which events did this student attend in this range?" class="px-2 py-1 rounded-full bg-slate-200 text-slate-700 hover:bg-slate-300 student-only">Student events</button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button id="reset-chat" type="button" class="px-3 py-2 rounded-lg border text-sm font-semibold text-blue-900">Reset</button>
                                    <button id="ask-btn" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">Send</button>
                                </div>
                            </div>
                            <span id="ask-status" class="text-sm text-slate-500"></span>
                        </div>
                    </div>
                </form>
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
            const messagesEl = document.getElementById('chat-messages');
            const typingEl = document.getElementById('typing-indicator');
            const resetBtn = document.getElementById('reset-chat');
            const questionEl = document.getElementById('question');
            const studentIdEl = document.getElementById('student_id');
            const suggestionButtons = document.querySelectorAll('[data-suggest]');
            let selectionChanged = false;
            let history = [];

            // Prefill event from query (selected in blade) - just ensure select visible on load.
            modeSelect.addEventListener('change', () => {
                if (modeSelect.value === 'event') {
                    eventSelect.classList.remove('hidden');
                    studentFields.classList.add('hidden');
                    document.querySelectorAll('.student-only').forEach(btn => btn.classList.add('hidden'));
                } else if (modeSelect.value === 'student') {
                    eventSelect.classList.add('hidden');
                    studentFields.classList.remove('hidden');
                    document.querySelectorAll('.student-only').forEach(btn => btn.classList.remove('hidden'));
                    if (studentIdEl) studentIdEl.setAttribute('required', 'required');
                } else {
                    // freeform
                    eventSelect.classList.add('hidden');
                    studentFields.classList.add('hidden');
                    document.querySelectorAll('.student-only').forEach(btn => btn.classList.add('hidden'));
                    if (studentIdEl) studentIdEl.removeAttribute('required');
                }
                // Reset chat when mode changes
                renderMessages([]);
                selectionChanged = true;
                statusEl.textContent = 'Mode changed; chat reset.';
            });

            // Trigger once to ensure correct visibility on load.
            modeSelect.dispatchEvent(new Event('change'));

            function renderMessages(newHistory) {
                history = newHistory || [];
                messagesEl.innerHTML = '';
                if (!history.length) {
                    messagesEl.innerHTML = '<p class="text-slate-500 text-sm">Start the conversation to see answers here.</p>';
                    return;
                }
                history.forEach((msg) => {
                    const row = document.createElement('div');
                    row.className = 'flex ' + (msg.role === 'assistant' ? 'justify-start' : 'justify-end');
                    const bubble = document.createElement('div');
                    bubble.className = msg.role === 'assistant'
                        ? 'max-w-[80%] rounded-2xl bg-slate-100 px-3 py-2 text-slate-800 shadow-sm'
                        : 'max-w-[80%] rounded-2xl bg-blue-600 px-3 py-2 text-white shadow-sm';

                    const label = document.createElement('div');
                    label.className = 'text-[10px] uppercase tracking-wide mb-1 ' + (msg.role === 'assistant' ? 'text-slate-500' : 'text-blue-100/80');
                    label.textContent = msg.role === 'assistant' ? 'AI' : 'You';

                    const body = document.createElement('div');
                    body.className = 'whitespace-pre-wrap text-sm';
                    body.textContent = msg.content;

                    bubble.appendChild(label);
                    bubble.appendChild(body);
                    row.appendChild(bubble);
                    messagesEl.appendChild(row);
                });
                messagesEl.scrollTop = messagesEl.scrollHeight;
            }

            suggestionButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const text = btn.getAttribute('data-suggest') || '';
                    questionEl.value = text;
                    questionEl.focus();
                });
            });

            resetBtn.addEventListener('click', () => {
                renderMessages([]);
                selectionChanged = true;
                statusEl.textContent = 'Chat reset.';
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                askBtn.disabled = true;
                statusEl.textContent = '';
                typingEl.classList.remove('hidden');

                const formData = new FormData(form);
                if (selectionChanged) {
                    formData.append('reset', '1');
                }
                const userMsg = questionEl.value.trim();
                if (modeSelect.value === 'student' && studentIdEl && !studentIdEl.value.trim()) {
                    askBtn.disabled = false;
                    typingEl.classList.add('hidden');
                    statusEl.textContent = 'Please enter a Student ID for student mode.';
                    studentIdEl.focus();
                    return;
                }
                if (!userMsg) {
                    askBtn.disabled = false;
                    typingEl.classList.add('hidden');
                    return;
                }

                // Local echo of user message
                const localHistory = history ? [...history] : [];
                localHistory.push({ role: 'user', content: userMsg });
                renderMessages(localHistory);
                // Show animated dots bubble for AI
                const dotsRow = document.createElement('div');
                dotsRow.className = 'flex justify-start';
                const dotsBubble = document.createElement('div');
                dotsBubble.className = 'max-w-[80%] rounded-2xl bg-slate-100 px-3 py-2 text-slate-800 shadow-sm inline-flex items-center gap-2';
                const dotsLabel = document.createElement('div');
                dotsLabel.className = 'text-[10px] uppercase tracking-wide text-slate-500';
                dotsLabel.textContent = 'AI';
                const dots = document.createElement('div');
                dots.className = 'flex gap-1 items-center';
                dots.innerHTML = '<span class="dot dot-1"></span><span class="dot dot-2"></span><span class="dot dot-3"></span>';
                dotsBubble.appendChild(dotsLabel);
                dotsBubble.appendChild(dots);
                dotsRow.appendChild(dotsBubble);
                messagesEl.appendChild(dotsRow);
                messagesEl.scrollTop = messagesEl.scrollHeight;

                try {
                    const resp = await fetch('{{ route('ai.insights.chat') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    const data = await resp.json();
                    if (!resp.ok) throw new Error(data.message || 'AI request failed');
                    renderMessages(data.history || []);
                    statusEl.textContent = 'Done.';
                    questionEl.value = '';
                    selectionChanged = false;
                } catch (err) {
                    statusEl.textContent = err.message;
                } finally {
                    askBtn.disabled = false;
                    typingEl.classList.add('hidden');
                }
            });

            // Submit on Enter, newline with Shift+Enter
            questionEl.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    form.requestSubmit();
                }
            });
        });
    </script>
</x-app-layout>
