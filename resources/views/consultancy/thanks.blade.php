<x-researcher-auth :consultancy="true" :wide="true" title="Application received" lead="Thank you for applying to the UGV RICH Consultancy Wing.">
    <x-slot:header>
        <header class="receipt-hero relative isolate overflow-hidden px-5 pt-12 pb-20 text-center text-white sm:px-8 sm:pt-16 sm:pb-24">
            <div class="pointer-events-none absolute inset-0 -z-10 grid-overlay opacity-15" aria-hidden="true"></div>
            <div class="receipt-emblem relative mx-auto mb-6 grid size-24 place-items-center" aria-hidden="true">
                <span class="receipt-halo absolute inset-0 rounded-[2rem] border border-teal-200/50"></span>
                <span class="receipt-spark receipt-spark-first absolute top-1 -right-2 size-2.5 rounded-full bg-teal-200"></span>
                <span class="receipt-spark receipt-spark-second absolute -bottom-1 left-0 size-2 rounded-full bg-fuchsia-300"></span>
                <span class="receipt-spark receipt-spark-third absolute top-4 -left-3 size-1.5 rounded-full bg-blue-200"></span>
                <span class="receipt-mark relative grid size-[4.5rem] place-items-center rounded-3xl bg-linear-to-br from-teal-100 to-emerald-300 text-emerald-950 shadow-[0_0_45px_rgba(94,234,212,0.45)]">
                    <x-ui-icon name="check" class="size-9" />
                </span>
            </div>
            <h1 class="font-display text-4xl font-extrabold tracking-tight !text-white sm:text-5xl">Application received</h1>
            <p class="mx-auto mt-4 max-w-md text-sm leading-relaxed text-white/85 sm:text-base">Thank you for applying to the UGV RICH Consultancy Wing.</p>
        </header>
    </x-slot:header>

    <div class="receipt-details relative mx-auto -mt-9 max-w-4xl px-5 pb-8 sm:px-9 sm:pb-10">
        <section class="mx-auto flex max-w-xl flex-wrap items-center justify-between gap-3 rounded-2xl border border-indigo-100 border-l-4 border-l-indigo-500 bg-white px-5 py-4 shadow-lg shadow-indigo-950/10 sm:px-6" aria-labelledby="receipt-reference">
            <div>
                <h2 id="receipt-reference" class="text-sm font-bold text-indigo-800">Application reference</h2>
                <p class="mt-1 text-sm text-ink-600">Keep this number for any follow-up.</p>
            </div>
            <p class="break-all rounded-xl bg-indigo-50 px-3 py-2 font-mono text-xl font-bold tabular-nums text-indigo-800">#{{ session('application_reference') }}</p>
        </section>

        <p class="mt-6 text-center text-sm leading-relaxed text-ink-600" role="status">
            @if (session('confirmation_email_failed'))
                Your application was received. The confirmation email could not be sent. Keep your application reference for follow-up.
            @else
                We will email you when there is an update on your application.
            @endif
        </p>

        <div class="mt-6 flex justify-center">
            <a class="btn-lead" href="{{ route('login') }}">Back to sign in <x-ui-icon name="arrow-right" class="size-4" /></a>
        </div>
    </div>
</x-researcher-auth>
