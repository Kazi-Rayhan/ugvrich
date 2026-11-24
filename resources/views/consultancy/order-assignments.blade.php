<x-consultancy-dashboard :application="$application" page="assignments">
    <section class="overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-sm" aria-label="{{ __('My assignments') }}">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[42rem] text-left text-sm">
                <thead class="border-b border-ink-200 bg-ink-50 text-ink-700">
                    <tr>
                        <th scope="col" class="px-5 py-3">{{ __('Order') }}</th>
                        <th scope="col" class="px-5 py-3">{{ __('Work order') }}</th>
                        <th scope="col" class="px-5 py-3">{{ __('Status') }}</th>
                        <th scope="col" class="px-5 py-3">{{ __('Milestones') }}</th>
                        <th scope="col" class="px-5 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($assignments as $assignment)
                        @php
                            $order = $assignment->order;
                            $progress = \App\Services\OrderDelivery::progress($order);
                        @endphp
                        <tr>
                            <th scope="row" class="px-5 py-4">
                                {{ $order->number }}
                                @if ($order->proposal?->title)
                                    <div class="mt-1 font-normal text-ink-600">{{ $order->proposal->title }}</div>
                                @endif
                            </th>
                            <td class="px-5 py-4">{{ $order->workOrder?->number ?? '—' }}</td>
                            <td class="px-5 py-4">{{ __(\App\Models\Order::STATUSES[$order->status] ?? $order->status) }}</td>
                            <td class="px-5 py-4">
                                @if ($progress['total'])
                                    <span>{{ $progress['completed'] }} / {{ $progress['total'] }}</span>
                                    <progress class="mt-1 block h-2 w-20 accent-teal-700" aria-label="{{ __('Milestones complete') }}" value="{{ $progress['completed'] }}" max="{{ $progress['total'] }}"></progress>
                                @else
                                    <span class="text-ink-600">{{ __('Unplanned') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <a href="{{ route('expert.orders.show', $order) }}" class="rounded-lg border border-ink-200 px-3 py-1.5 font-semibold text-ink-800 hover:bg-ink-50">{{ __('Show') }}</a>
                                    <a href="{{ route('expert.orders.tasks', $order) }}" class="rounded-lg bg-teal-800 px-3 py-1.5 font-semibold text-white hover:bg-teal-900">{{ __('Tasks') }}</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-ink-600">{{ __('No assigned orders yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <div class="mt-4">{{ $assignments->links() }}</div>
</x-consultancy-dashboard>
