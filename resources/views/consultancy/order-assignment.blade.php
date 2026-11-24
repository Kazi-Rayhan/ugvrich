@php
    use App\Models\WorkOrder;
    use Illuminate\Support\Carbon;

    $workOrder = $order->workOrder;
    $progress = \App\Services\OrderDelivery::progress($order);
    $detail = fn (string $key) => $workOrder?->detail($key);
    $date = fn ($value) => filled($value) ? Carbon::parse($value)->translatedFormat('j M Y') : null;
    $sector = WorkOrder::sector($workOrder?->sector);
    $labels = config('work_order_admin.sectors.'.$workOrder?->sector, []);
    $selected = fn (string $key, array $options) => collect($options)->only((array) $detail($key))->implode(', ');
    $fields = [
        'Order details' => [
            'Order number' => $order->number,
            'Order date' => $date($order->order_date),
            'Work order number' => $workOrder?->number,
            'Work order status' => WorkOrder::STATUSES[$workOrder?->status] ?? $workOrder?->status,
            'Issue date' => $date($workOrder?->issue_date),
            'Category' => $sector['name'] ?? null,
            'Subcategories' => $selected('services', $labels['services'] ?? $sector['services'] ?? []),
            'Proposal reference' => $detail('quotation_number') ?? $order->proposal?->number,
            'Revision' => $detail('revision'),
        ],
        'Client and coordination' => [
            'Client' => $detail('client.name') ?? $workOrder?->client_name,
            'Contact person' => $detail('client.contact'),
            'Phone / email' => $detail('client.phone_email'),
            'Address' => $detail('client.address'),
            'Service provider' => $workOrder ? config('work_order_admin.provider.name') : null,
            'Coordinator' => $detail('coordinator'),
        ],
        'Scope of work' => [
            'Summary' => $detail('summary'),
            'Included work' => $detail('included'),
            'Excluded work' => $detail('excluded'),
            'Revision rounds' => $detail('revisions'),
        ],
        'Schedule' => [
            'Start date' => $date($detail('start_date')),
            'Expected delivery' => $date($detail('end_date') ?? $order->expected_delivery_date),
            'Delivery mode' => config('work_order_admin.modes', [])[$detail('mode')] ?? null,
        ],
        'Attachments and approvals' => [
            'Attachments' => $selected('attachments', $labels['attachments'] ?? $sector['attachments'] ?? []),
            'Other attachments' => $detail('other_attachments'),
            'Client signatory' => $detail('client_signatory'),
            'Client signature date' => $date($detail('client_sign_date')),
            'UGV RICH signatory' => $detail('rich_signatory'),
            'UGV RICH signature date' => $date($detail('rich_sign_date')),
            'Technical approval' => match ($detail('tech_approval_status')) {
                'yes' => __('Yes'),
                'no' => __('No'),
                default => $detail('tech_approval'),
            },
            'Technical approval date' => $date($detail('tech_approval_date')),
        ],
    ];

    foreach ($labels['stages'] ?? [] as $index => [$title]) {
        $fields['Schedule'][$title] = $date($detail("stage_dates.{$index}"));
    }
    foreach ($sector['info'] ?? [] as [$heading, $parts]) {
        foreach ($parts as $part) {
            if ($part[0] === 'text') {
                continue;
            }

            $value = $detail('info.'.$part[1]);
            if ($part[0] === 'check' && ! $value) {
                continue;
            }

            $label = $labels['info_labels'][$part[1]] ?? ($part[0] === 'check' ? $part[2] : str($part[1])->headline()->toString());
            $fields['Service requirements'][$label] = $part[0] === 'check' ? __('Yes') : $value;
        }
    }
@endphp

<x-consultancy-dashboard :application="$application" page="assignments">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('expert.orders.index') }}" class="text-sm font-semibold text-teal-800">{{ __('Back to assignments') }}</a>
        <a href="{{ route('expert.orders.tasks', $order) }}" class="rounded-lg bg-teal-800 px-3 py-2 text-sm font-semibold text-white">{{ __('Open tasks') }}</a>
    </div>
    <header class="mb-4 rounded-2xl border border-ink-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-semibold text-teal-800">{{ $workOrder?->number ?? $order->number }}</p>
        <h2 class="mt-1 font-display text-xl font-bold">{{ $order->proposal?->title ?? __('Work order details') }}</h2>
        @if ($progress['total'])
            <div class="mt-4 flex justify-between gap-3 text-sm text-ink-700">
                <span>{{ __(':done of :total milestones complete', ['done' => $progress['completed'], 'total' => $progress['total']]) }}</span>
                <strong>{{ $progress['percent'] }}%</strong>
            </div>
            <progress class="mt-2 block h-2 w-full accent-teal-700" aria-label="{{ __('Delivery progress') }}" value="{{ $progress['percent'] }}" max="100"></progress>
        @endif
    </header>
    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ($fields as $group => $rows)
            @php($rows = array_filter($rows, fn ($value) => filled($value)))
            @if ($rows !== [])
                <section class="rounded-2xl border border-ink-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-3 font-display font-bold">{{ __($group) }}</h3>
                    <dl class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1.5fr)]">
                        @foreach ($rows as $label => $value)
                            <dt class="text-sm font-medium text-ink-600">{{ __($label) }}</dt>
                            <dd class="whitespace-pre-line break-words text-sm text-ink-950">{{ $value }}</dd>
                        @endforeach
                    </dl>
                </section>
            @endif
        @endforeach
        @if ($order->items->isNotEmpty())
            <section class="rounded-2xl border border-ink-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 font-display font-bold">{{ __('Agreed services') }}</h3>
                <ul class="divide-y divide-ink-100">
                    @foreach ($order->items as $item)
                        <li class="py-2 text-sm">
                            <strong>{{ $item->service_name }}</strong>
                            @if (filled($item->description))
                                <p class="mt-1 whitespace-pre-line text-ink-600">{{ $item->description }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
        @if (! empty($sector['deliverables']))
            <section class="rounded-2xl border border-ink-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 font-display font-bold">{{ __('Deliverables and acceptance') }}</h3>
                <ul class="divide-y divide-ink-100">
                    @foreach ($sector['deliverables'] as [$title, $output, $acceptance])
                        <li class="py-2 text-sm">
                            <strong>{{ $title }}</strong>
                            <p class="text-ink-600">{{ $output }}</p>
                            <p class="text-ink-600">{{ $acceptance }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-consultancy-dashboard>
