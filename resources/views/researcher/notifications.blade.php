<x-layouts.researcher :title="__('research.notifications.title')">

    <div class="max-w-3xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="font-display text-[22px] font-bold tracking-tight text-ink-950 sm:text-[26px]">{{ __('research.notifications.title') }}</h2>
            </div>

            @if (auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('researcher.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn-ghost">{{ __('research.notifications.mark_all') }}</button>
                </form>
            @endif
        </div>

        @if ($notifications->isEmpty())
            <div class="rounded-[1.5rem] border border-dashed border-ink-200 bg-white px-6 py-16 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-ink-50 text-ink-400">
                    <x-ui-icon name="chat" class="h-6 w-6" />
                </span>
                <p class="mt-5 font-display text-[16px] font-bold text-ink-950">{{ __('research.notifications.none') }}</p>
                <p class="mx-auto mt-2 max-w-sm text-[13.5px] leading-relaxed text-ink-500">{{ __('research.notifications.none_lead') }}</p>
            </div>
        @else
            <ul class="divide-y divide-ink-100 overflow-hidden rounded-[1.5rem] border border-ink-100 bg-white">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $heading = trans()->has('research.events.'.($data['event'] ?? ''))
                            ? __('research.events.'.$data['event'])
                            : ($data['event'] ?? '');
                    @endphp

                    <li class="{{ $notification->read_at ? '' : 'bg-brand-50/40' }}">
                        <a href="{{ route('researcher.notifications.read', $notification->id) }}"
                           class="flex items-start gap-4 px-6 py-5 transition hover:bg-ink-50/70">

                            <span @class([
                                'mt-1.5 h-2 w-2 shrink-0 rounded-full',
                                'bg-brand-600' => ! $notification->read_at,
                                'bg-ink-200' => (bool) $notification->read_at,
                            ])></span>

                            <span class="min-w-0 flex-1">
                                <span class="block text-[14px] font-semibold text-ink-950">{{ $heading }}</span>
                                <span class="mt-0.5 block truncate text-[13.5px] text-ink-600">{{ $data['title'] ?? '' }}</span>

                                @if (! empty($data['body']))
                                    <span class="mt-1 block line-clamp-2 text-[12.5px] leading-relaxed text-ink-500">{{ $data['body'] }}</span>
                                @endif

                                <span class="mt-1.5 block text-[11.5px] text-ink-400">{{ $notification->created_at?->diffForHumans() }}</span>
                            </span>

                            <x-ui-icon name="arrow-right" class="mt-1 h-4 w-4 shrink-0 text-ink-300" />
                        </a>
                    </li>
                @endforeach
            </ul>

            <div>{{ $notifications->links() }}</div>
        @endif
    </div>
</x-layouts.researcher>
