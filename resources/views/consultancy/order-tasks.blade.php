@php
    use App\Services\OrderDelivery;
@endphp

<x-consultancy-dashboard :application="$application" page="assignments">
    <style>
        .assignment { --rule:#d7e1e3; --ink:#102d34; --quiet:#425b62; --accent:#086a63; color:var(--ink); }
        .assignment summary { list-style:none; cursor:pointer; }
        .assignment summary::-webkit-details-marker { display:none; }
        .assignment summary:focus-visible, .assignment button:focus-visible, .assignment select:focus-visible { outline:2px solid var(--accent); outline-offset:3px; }
        .assignment .milestone[open] .disclosure { transform:rotate(90deg); }
        .assignment .disclosure { transition:transform .16s ease; }
        .assignment progress { display:block; width:100%; height:.55rem; accent-color:var(--accent); }
        .assignment .board { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.9rem; }
        .assignment .lane { min-width:0; border:1px solid var(--rule); border-radius:.9rem; background:#f3f6f7; padding:.7rem; transition:background .15s,border-color .15s; }
        .assignment .lane[data-lane="doing"] { background:#f0f7fb; }
        .assignment .lane[data-lane="done"] { background:#eef8f2; }
        .assignment .lane.is-over { border-color:var(--accent); background:#d7f2e9; }
        .assignment .lane-content { display:grid; gap:.6rem; min-height:4.5rem; align-content:start; }
        .assignment .lane-content:has([data-task]) .empty-lane { display:none; }
        .assignment .task { background:#fff; border:1px solid var(--rule); border-radius:.7rem; padding:.75rem; box-shadow:0 2px 3px #102d3409; overflow-wrap:anywhere; }
        .assignment .task[draggable="true"] { cursor:grab; }
        .assignment .task[aria-busy="true"], .assignment .task.is-dragging { opacity:.5; }
        .assignment .move-task { margin-top:.6rem; display:flex; flex-wrap:wrap; gap:.4rem; align-items:center; }
        .assignment .move-task select { min-width:0; max-width:100%; background:#fff; border:1px solid var(--rule); border-radius:.4rem; padding:.3rem; }
        .assignment .move-task button { border-radius:.4rem; background:#e7f4ef; color:#07524b; font-weight:700; padding:.35rem .55rem; cursor:pointer; }
        @media (max-width:850px) { .assignment .board { grid-template-columns:minmax(0,1fr); } }
    </style>

    @if (session('status'))
        <p role="status" class="mb-3 rounded-lg bg-teal-50 p-3 text-sm text-teal-900">{{ session('status') }}</p>
    @endif
    @error('status')
        <p role="alert" class="mb-3 rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $message }}</p>
    @enderror
    <p data-board-notice role="status" aria-live="polite" hidden class="mb-3 rounded-lg bg-white p-3 text-sm text-ink-900"></p>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('expert.orders.index') }}" class="text-sm font-semibold text-teal-800">{{ __('Back to assignments') }}</a>
        <a href="{{ route('expert.orders.show', $order) }}" class="text-sm font-semibold text-teal-800">{{ __('Show work order') }}</a>
    </div>
    <p class="mb-4 text-sm text-ink-600">{{ __('Drag tasks between columns or choose a status and select Move.') }}</p>

    <div class="space-y-5" data-order-boards>
        @php
            $progress = OrderDelivery::progress($order);
            $percent = $progress['total'] ? (int) round(100 * $progress['completed'] / $progress['total']) : 0;
            $title = $order->proposal?->title ?: $order->number;
        @endphp
        <article class="assignment overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-sm" aria-label="{{ $title }}">
            <header class="border-b border-ink-200 px-5 py-5 sm:px-7">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        @if ($title !== $order->number)
                            <p class="mb-1 text-xs font-bold tracking-wide text-teal-800">{{ $order->number }}</p>
                        @endif
                        <h2 class="font-display text-xl font-bold tracking-tight sm:text-2xl">{{ $title }}</h2>
                        @if ($order->status === 'cancelled')
                            <p class="mt-1 text-sm font-semibold text-red-800">{{ __('Cancelled') }}</p>
                        @endif
                    </div>

                </div>
                <div class="mt-4 flex justify-between gap-3 text-sm font-semibold">
                    <span>{{ __(':done of :total milestones complete', ['done' => $progress['completed'], 'total' => $progress['total']]) }}</span>
                    <span>{{ $percent }}%</span>
                </div>
                <progress class="mt-2" max="100" value="{{ $percent }}" aria-label="{{ __('Milestones complete') }}">{{ $percent }}%</progress>
            </header>
            <div class="space-y-3 px-4 py-4 sm:px-6">
                @forelse ($order->delivery_milestones ?? [] as $milestone)
                    @php
                        $editable = $order->status !== 'cancelled' && ! $milestone['completed_at'];
                        $groups = collect($milestone['tasks'])->groupBy('status');
                    @endphp
                    <details class="milestone rounded-xl border border-ink-200" id="milestone-{{ $milestone['id'] }}" @if ($editable) open @endif>
                        <summary class="flex flex-wrap items-center gap-3 px-3 py-3 sm:px-4">
                            <span class="disclosure text-teal-800" aria-hidden="true">▸</span>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold">{{ $milestone['title'] }}</h3>
                                @if ($milestone['due'])
                                    <span class="text-xs text-ink-600">{{ __('Due :date', ['date' => \Illuminate\Support\Carbon::parse($milestone['due'])->translatedFormat('j M Y')]) }}</span>
                                @endif
                            </div>
                            <span class="rounded-full bg-ink-50 px-3 py-1 text-xs font-semibold" data-task-progress>
                                @if ($milestone['completed_at'])
                                    {{ __('Complete') }}
                                @else
                                    <span data-done-count>{{ $groups->get('done', collect())->count() }}</span> / {{ count($milestone['tasks']) }} {{ __('tasks done') }}
                                @endif
                            </span>
                        </summary>
                        <div class="board px-3 pb-3 sm:px-4 sm:pb-4" data-milestone-board>
                            @foreach (OrderDelivery::TASK_STATUSES as $status => $label)
                                @php($tasks = $groups->get($status, collect()))
                                <div class="lane" data-lane="{{ $status }}" aria-label="{{ __($label) }}">
                                    <h4 class="mb-2 flex items-center justify-between gap-2 px-1 text-sm font-bold">
                                        {{ __($label) }}
                                        <span data-lane-count class="rounded-full border border-ink-200 bg-white px-2 text-xs">{{ $tasks->count() }}</span>
                                    </h4>
                                    <div class="lane-content">
                                        <span class="empty-lane rounded-lg border border-dashed border-ink-200 px-2 py-3 text-center text-xs text-ink-600">
                                            {{ $editable ? __('Drop a task here') : __('No tasks') }}
                                        </span>
                                        @foreach ($tasks as $task)
                                            <article class="task" data-task draggable="{{ $editable ? 'true' : 'false' }}">
                                                <h5 class="text-sm font-bold">{{ $task['title'] }}</h5>
                                                @if ($task['description'])
                                                    <p class="mt-1 whitespace-pre-line text-xs leading-relaxed text-ink-600">{{ $task['description'] }}</p>
                                                @endif
                                                @if ($editable)
                                                    <form class="move-task" method="post" action="{{ route('expert.orders.tasks.update', [$order, $milestone['id'], $task['id']]) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select class="text-xs" name="status" aria-label="{{ __('Status for :task', ['task' => $task['title']]) }}">
                                                            @foreach (OrderDelivery::TASK_STATUSES as $choice => $choiceLabel)
                                                                <option value="{{ $choice }}" @selected($choice === $status)>{{ __($choiceLabel) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button class="text-xs" type="submit">{{ __('Move') }}</button>
                                                    </form>
                                                @endif
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @empty
                    <p class="px-2 py-3 text-sm text-ink-600">{{ __('The delivery team has not added any milestones yet.') }}</p>
                @endforelse
            </div>
        </article>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-order-boards]');
            const notice = document.querySelector('[data-board-notice]');
            let dragged = null;
            let highlighted = null;

            function highlight(lane) {
                highlighted?.classList.remove('is-over');
                highlighted = lane;
                highlighted?.classList.add('is-over');
            }

            async function move(card, lane) {
                const form = card.querySelector('form');
                if (!form || card.hasAttribute('aria-busy')) return;

                const from = card.closest('[data-lane]');
                const status = form.elements.status;
                if (from === lane) return;
                card.setAttribute('aria-busy', 'true');
                form.querySelector('button').disabled = true;
                status.value = lane.dataset.lane;
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: { Accept: 'application/json' },
                    });
                    if (!response.ok) throw new Error();

                    lane.querySelector('.lane-content').append(card);
                    const board = lane.closest('[data-milestone-board]');
                    for (const column of [from, lane]) {
                        column.querySelector('[data-lane-count]').textContent = column.querySelectorAll('[data-task]').length;
                    }
                    board.closest('.milestone').querySelector('[data-done-count]').textContent =
                        board.querySelectorAll('[data-lane="done"] [data-task]').length;
                    notice.textContent = @js(__('Task updated.'));
                } catch {
                    status.value = from.dataset.lane;
                    notice.textContent = @js(__('Could not move this task. Refresh the board and try again.'));
                } finally {
                    notice.hidden = false;
                    card.removeAttribute('aria-busy');
                    form.querySelector('button').disabled = false;
                }
            }

            root.addEventListener('submit', (event) => {
                if (!event.target.matches('.move-task')) return;
                event.preventDefault();
                const card = event.target.closest('[data-task]');
                const lane = card.closest('[data-milestone-board]').querySelector(
                    `[data-lane="${CSS.escape(event.target.elements.status.value)}"]`
                );
                if (lane) move(card, lane);
            });

            root.addEventListener('dragstart', (event) => {
                const card = event.target.closest('[data-task][draggable="true"]');
                if (!card || card.hasAttribute('aria-busy') || event.target.closest('form')) return;
                dragged = card;
                card.classList.add('is-dragging');
                event.dataTransfer.setData('text/plain', card.querySelector('h5').textContent);
                event.dataTransfer.effectAllowed = 'move';
            });

            root.addEventListener('dragover', (event) => {
                const lane = event.target.closest('[data-lane]');
                const isTarget = dragged && lane && lane.closest('[data-milestone-board]') === dragged.closest('[data-milestone-board]');
                highlight(isTarget ? lane : null);
                if (!isTarget) return;
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
            });

            root.addEventListener('drop', (event) => {
                const lane = event.target.closest('[data-lane]');
                if (!dragged || !lane || lane.closest('[data-milestone-board]') !== dragged.closest('[data-milestone-board]')) return;
                event.preventDefault();
                move(dragged, lane);
                highlight(null);
            });

            root.addEventListener('dragend', () => {
                dragged?.classList.remove('is-dragging');
                dragged = null;
                highlight(null);
            });
        })();
    </script>
</x-consultancy-dashboard>
