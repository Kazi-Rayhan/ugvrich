@props(['names' => []])

{{-- The rail for a form taken in steps.

     It reads `step`, `seen`, `progress` and `name` from the stepForm component
     around it, so a page only has to say which steps it has.

     Two shapes, not two rails: a line of numbered nodes where there is room for
     one, and a single line of text with a bar where there is not. --}}
<div {{ $attributes->merge(['class' => 'sticky top-[84px] z-10 mb-6 rounded-[1.5rem] border border-ink-100 bg-white/90 px-5 py-4 shadow-[0_18px_44px_-44px_rgba(2,34,81,0.6)] backdrop-blur-md sm:px-6']) }}>

    {{-- Narrow screens: where you are, and how far through --}}
    <div class="lg:hidden">
        <div class="flex items-baseline justify-between gap-4">
            <p class="min-w-0 truncate text-[12.5px] font-semibold text-ink-500">
                <span x-text="@js(__('researcher.step.of', ['current' => '%current%', 'total' => '%total%'])).replace('%current%', visibleSteps.indexOf(step) + 1).replace('%total%', visibleSteps.length)"></span>
                <span class="text-ink-300">·</span>
                <span class="text-ink-900" x-text="name"></span>
            </p>

            <p class="font-numeric shrink-0 text-[12.5px] font-bold tabular-nums text-brand-700" x-text="progress + '%'"></p>
        </div>

        <div class="mt-2.5 h-1.5 overflow-hidden rounded-full bg-ink-100">
            <div class="h-full rounded-full bg-brand-600 transition-all duration-500" :style="'width: ' + progress + '%'"></div>
        </div>
    </div>

    {{-- Wide screens: the steps themselves, joined by a line that fills behind
         you. A step already been through can be gone back to by name. --}}
    <ol class="hidden lg:flex lg:items-start">
        @foreach ($names as $i => $label)
            <li x-show="visibleSteps.includes({{ $i }})" class="relative flex min-w-0 flex-1 flex-col items-center">

                {{-- The joining line, in two halves so each one can colour
                     itself from the step on its own side. --}}
                @if ($i > 0)
                    <span class="absolute start-0 top-[17px] h-[2px] w-1/2 rounded-full transition-colors duration-500"
                          :class="seen >= {{ $i }} ? 'bg-brand-500' : 'bg-ink-200'" aria-hidden="true"></span>
                @endif

                @if ($i < count($names) - 1)
                    <span class="absolute end-0 top-[17px] h-[2px] w-1/2 rounded-full transition-colors duration-500"
                          :class="seen > {{ $i }} ? 'bg-brand-500' : 'bg-ink-200'" aria-hidden="true"></span>
                @endif

                <button type="button" @click="go({{ $i }})"
                        :aria-current="step === {{ $i }} ? 'step' : null"
                        class="group relative z-10 flex w-full flex-col items-center gap-2 px-2">

                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 bg-white font-numeric text-[12.5px] font-bold transition duration-300"
                          :class="step === {{ $i }}
                              ? 'border-brand-600 bg-brand-600 text-white shadow-[0_8px_20px_-10px_var(--color-brand-600)] ring-4 ring-brand-500/15 scale-110'
                              : (seen > {{ $i }}
                                  ? 'border-brand-500 bg-brand-50 text-brand-700 group-hover:border-brand-600'
                                  : 'border-ink-200 text-ink-400 group-hover:border-ink-300')">

                        <span x-show="! (seen > {{ $i }} && step !== {{ $i }})">{{ $i + 1 }}</span>
                        <span x-show="seen > {{ $i }} && step !== {{ $i }}" x-cloak>
                            <x-ui-icon name="check" class="h-4 w-4" stroke="3" />
                        </span>
                    </span>

                    <span class="max-w-full truncate text-[12px] font-semibold tracking-tight transition-colors duration-300"
                          :class="step === {{ $i }} ? 'text-ink-950' : (seen > {{ $i }} ? 'text-ink-600' : 'text-ink-400')">
                        {{ $label }}
                    </span>
                </button>
            </li>
        @endforeach
    </ol>
</div>
