<form method="POST" action="{{ route('expert.sign') }}" class="min-w-0 rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-7" id="expert-onboarding">
    @csrf
    <input type="hidden" name="policy_version" value="{{ $application->engagement_policy['version'] }}">

    <div class="border-b border-ink-100 pb-5">
        <h2 class="font-display text-[17px] font-bold text-ink-950">{{ __('consultancy_engagement.onboarding') }}</h2>
        <p class="mt-1 text-[13.5px] leading-relaxed text-ink-600">{{ __('consultancy_engagement.review_one_at_a_time') }}</p>
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <progress data-review-progress class="h-2 min-w-24 flex-1 accent-brand-600" max="{{ count($reviewItems) + 1 }}" value="0" aria-label="{{ __('consultancy_engagement.onboarding') }}"></progress>
            <span data-review-count data-template="{{ __('consultancy_engagement.review_progress') }}" aria-live="polite" class="text-[12.5px] font-semibold text-brand-700"></span>
        </div>
    </div>

    <div class="mt-5 space-y-3">
        @foreach ([...$reviewItems, 'consent' => ['title' => __('consultancy_engagement.electronic_consent'), 'description' => null]] as $key => $item)
            <details data-review-item @if ($loop->first) open @endif class="group rounded-xl border border-ink-200 bg-ink-50/50 open:border-brand-200 open:bg-brand-50/40 data-[locked]:opacity-65">
                <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-4 py-3.5 text-[13px] font-semibold text-ink-950 focus-visible:outline-2 focus-visible:outline-brand-600 [&::-webkit-details-marker]:hidden">
                    <span class="grid size-7 shrink-0 place-items-center rounded-full bg-white text-xs font-bold text-brand-700 ring-1 ring-brand-100 group-data-[complete]:bg-emerald-100 group-data-[complete]:text-emerald-800">
                        <span class="group-data-[complete]:hidden">{{ $loop->iteration }}</span>
                        <span class="hidden group-data-[complete]:block"><x-ui-icon name="check" class="size-4" /></span>
                    </span>
                    <span class="flex-1">{{ $item['title'] }}</span>
                    <x-ui-icon name="chevron-down" class="size-4 shrink-0 text-ink-500 group-open:rotate-180 motion-safe:transition-transform" />
                </summary>
                <div class="space-y-4 border-t border-brand-100 px-4 py-4 sm:px-5">
                    @if ($item['description'])
                        <p class="whitespace-pre-wrap break-words text-[13.5px] leading-relaxed text-ink-700">{{ $item['description'] }}</p>
                    @endif
                    <label class="flex cursor-pointer items-start gap-2.5 rounded-lg bg-white px-3 py-2.5 text-[13px] font-semibold text-ink-900">
                        @if ($key === 'consent')
                            <input type="checkbox" name="terms" value="1" @checked(old('terms')) required class="mt-0.5 size-4 accent-brand-600">
                            {{ __('consultancy_engagement.sign_accept') }}
                        @else
                            <input type="checkbox" name="reviewed[{{ $key }}]" value="1" @checked(old('reviewed.'.$key)) required class="mt-0.5 size-4 accent-brand-600">
                            {{ __('consultancy_engagement.review_item') }}
                        @endif
                    </label>
                </div>
            </details>
        @endforeach
    </div>

    <section hidden data-signature-panel class="mt-5 rounded-xl border border-brand-200 bg-brand-50/50 p-4 sm:p-5" aria-labelledby="signature-label">
        <h3 class="font-display font-bold text-ink-950" id="signature-label" tabindex="-1">{{ __('consultancy_engagement.signature') }}</h3>
        <p class="mb-3 text-sm text-ink-600" id="signature-help">{{ __('consultancy_engagement.signature_help') }}</p>
        <canvas width="720" height="240" class="block h-auto w-full touch-none rounded-xl border border-ink-300 bg-white" aria-labelledby="signature-label" aria-describedby="signature-help"></canvas>
        <input type="hidden" name="signature">
        <p hidden class="mt-2 text-sm text-rose-700" role="alert" data-signature-error>{{ __('consultancy_engagement.signature_error') }}</p>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <button type="button" data-clear class="rounded-lg px-2 py-2 text-[13px] font-semibold text-brand-700 hover:bg-brand-100">{{ __('consultancy_engagement.clear') }}</button>
            <button type="submit" class="btn-lead">{{ __('consultancy_engagement.activate') }}</button>
        </div>
    </section>
</form>

<script>
    (() => {
        const form = document.querySelector('#expert-onboarding');
        const items = [...form.querySelectorAll('[data-review-item]')];
        const checks = items.map(item => item.querySelector('input[type=checkbox]'));
        const signature = form.querySelector('[data-signature-panel]');
        const canvas = signature.querySelector('canvas');
        const context = canvas.getContext('2d');
        const error = signature.querySelector('[data-signature-error]');
        const progress = form.querySelector('[data-review-progress]');
        const count = form.querySelector('[data-review-count]');
        let pointer = null;
        let drawn = false;
        let next = 0;

        context.lineWidth = 4;
        context.lineCap = 'round';
        context.lineJoin = 'round';
        context.strokeStyle = '#14233c';

        function clearSignature() {
            context.clearRect(0, 0, canvas.width, canvas.height);
            drawn = false;
        }

        function syncReview(focus = false) {
            const firstUnchecked = checks.findIndex(check => !check.checked);
            next = firstUnchecked === -1 ? checks.length : firstUnchecked;
            items.forEach((item, index) => {
                item.open = index === next;
                item.toggleAttribute('data-locked', index > next);
                item.toggleAttribute('data-complete', index < next);
                item.querySelector('summary').setAttribute('aria-disabled', String(index > next));
            });

            progress.value = next;
            count.textContent = count.dataset.template.replace(':done', next).replace(':total', checks.length);
            signature.hidden = next !== checks.length;
            if (signature.hidden && drawn) clearSignature();

            if (focus) {
                (items[next]?.querySelector('summary') ?? signature.querySelector('h3')).focus();
            }
        }

        items.forEach((item, index) => {
            item.querySelector('summary').addEventListener('click', event => {
                if (index > next) event.preventDefault();
            });

            checks[index].addEventListener('change', () => {
                if (!checks[index].checked) {
                    checks.slice(index + 1).forEach(check => { check.checked = false; });
                }

                syncReview(true);
            });
        });
        syncReview();

        function position(event) {
            const bounds = canvas.getBoundingClientRect();
            return [
                (event.clientX - bounds.left) * canvas.width / bounds.width,
                (event.clientY - bounds.top) * canvas.height / bounds.height,
            ];
        }

        canvas.addEventListener('pointerdown', event => {
            if (pointer !== null || event.button !== 0) return;
            pointer = event.pointerId;
            canvas.setPointerCapture(pointer);
            context.beginPath();
            context.moveTo(...position(event));
        });
        canvas.addEventListener('pointermove', event => {
            if (event.pointerId !== pointer) return;
            context.lineTo(...position(event));
            context.stroke();
            drawn = true;
            error.hidden = true;
        });
        canvas.addEventListener('lostpointercapture', () => { pointer = null; });
        form.querySelector('[data-clear]').addEventListener('click', clearSignature);
        form.addEventListener('submit', event => {
            if (next !== checks.length || !drawn) {
                event.preventDefault();
                error.hidden = false;
                return;
            }

            form.elements.signature.value = canvas.toDataURL('image/png');
        });
    })();
</script>
