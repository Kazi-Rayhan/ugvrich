<x-layouts.app :title="__('site.actions.submit_idea')"
               :description="__('site.ideas.meta_description')">

    <x-page-hero
        :eyebrow="__('site.ideas.hero_eyebrow')"
        :title="__('site.ideas.hero_title')"
        :lead="__('site.ideas.hero_lead')"
        :breadcrumbs="[__('site.nav.startup') => route('startup'), __('site.actions.submit_idea') => null]" />

    <section class="bg-white py-14 sm:py-16">
        <div class="container-rich">

            @if ($errors->any())
                <div class="reveal mb-6 flex items-start gap-4 rounded-[1.5rem] border border-red-200 bg-red-50 p-6" role="alert">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <x-ui-icon name="x" class="h-5 w-5" stroke="2.5" />
                    </span>
                    <div>
                        <p class="font-display text-[16px] font-bold text-red-700">{{ __('site.forms.errors_title') }}</p>
                        <ul class="mt-2 space-y-1 text-[14px] text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>· {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{--
                Two steps. First, register: who is submitting (a person or a
                team), how to reach them, and a working title. Then the idea
                itself, as a file dropped or chosen in step two. Both steps stay
                in the DOM and are only hidden, so one submit sends everything
                and the browser's own validation still applies.
            --}}
            <form action="{{ route('ideas.store') }}" method="POST" enctype="multipart/form-data"
                  class="reveal overflow-hidden rounded-[2rem] border border-ink-100 bg-white shadow-[0_30px_70px_-50px_rgba(7,20,38,0.45)]"
                  x-data="{
                      step: {{ $errors->hasAny(['title', 'category', 'document']) && ! $errors->hasAny(['name', 'email', 'phone']) ? 2 : 1 }},
                      last: 2,
                      fields: {
                          name: @js(old('name', $innovator?->name ?? '')),
                          email: @js(old('email', $innovator?->email ?? '')),
                          phone: @js(old('phone', $innovatorPhone ?? '')),
                          title: @js(old('title', '')),
                      },
                      category: @js(old('category', '')),
                      file: null,
                      dragging: false,
                      take(files) {
                          if (! files || ! files.length) return;
                          this.$refs.file.files = files;
                          this.pick();
                      },
                      pick() {
                          const f = this.$refs.file.files[0];
                          this.file = f ? { name: f.name, size: f.size < 1048576 ? Math.max(1, Math.round(f.size / 1024)) + ' KB' : (f.size / 1048576).toFixed(1) + ' MB' } : null;
                      },
                      clear() {
                          this.$refs.file.value = '';
                          this.file = null;
                      },
                      advance() {
                          const fields = this.$refs['step' + this.step].querySelectorAll('input, textarea, select');
                          for (const field of fields) {
                              if (! field.checkValidity()) { field.reportValidity(); return }
                          }
                          this.step = Math.min(this.step + 1, this.last);
                          this.$refs.top.scrollIntoView({ behavior: 'smooth', block: 'start' });
                      },
                      back() {
                          this.step = Math.max(this.step - 1, 1);
                          this.$refs.top.scrollIntoView({ behavior: 'smooth', block: 'start' });
                      },
                  }">
                @csrf

                {{-- Honeypot --}}
                <div class="hidden" aria-hidden="true">
                    <label for="website">{{ __('site.forms.website') }}</label>
                    <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                </div>

                {{-- Progress: two steps on a dotted track that fills as you go --}}
                <div x-ref="top" class="relative isolate overflow-hidden border-b border-ink-100 bg-ink-50/70 px-6 py-6 sm:px-10 sm:py-7">
                    <div class="pointer-events-none absolute inset-0 -z-10 text-brand-700 grid-overlay opacity-40 [mask-image:linear-gradient(to_left,black,transparent_70%)]" aria-hidden="true"></div>

                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <h2 class="font-display text-2xl font-bold text-ink-950 sm:text-[28px]">{{ __('site.ideas.form_title') }}</h2>
                        <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white px-3.5 py-1.5 text-[12.5px] font-medium text-ink-600">
                            <x-ui-icon name="shield" class="h-4 w-4 text-brand-600" /> {{ __('site.ideas.confidential') }}
                        </span>
                    </div>

                    <div class="relative mx-auto mt-8 max-w-md">
                        <div class="absolute left-1/4 right-1/4 top-6 -translate-y-1/2 border-t-[3px] border-dotted border-ink-300" aria-hidden="true">
                            <div class="absolute left-0 -top-[3px] border-t-[3px] border-dotted border-brand-600 transition-[width] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)]"
                                 :style="`width: ${(step - 1) * 100}%`"></div>
                        </div>

                        <ol class="relative grid grid-cols-2">
                            @foreach ([[1, __('site.ideas.step_register'), 'users'], [2, __('site.ideas.step_submit'), 'upload']] as [$n, $label, $icon])
                                <li class="flex flex-col items-center text-center">
                                    <button type="button" @click="if (step > {{ $n }}) step = {{ $n }}"
                                            :disabled="step <= {{ $n }}"
                                            :aria-current="step === {{ $n }} ? 'step' : null"
                                            class="relative flex h-12 w-12 items-center justify-center rounded-full border-2 transition-all duration-500 disabled:cursor-default"
                                            :class="step > {{ $n }}
                                                ? 'border-brand-600 bg-brand-600 text-white shadow-[0_10px_24px_-10px_var(--color-brand-600)] hover:scale-105'
                                                : (step === {{ $n }}
                                                    ? 'scale-110 border-brand-600 bg-white text-brand-600 shadow-[0_0_0_6px_var(--color-brand-100),0_14px_30px_-12px_var(--color-brand-600)]'
                                                    : 'border-ink-200 bg-white text-ink-400')">
                                        <span x-show="step === {{ $n }}" class="absolute inset-0 animate-ping rounded-full bg-brand-400/25" aria-hidden="true"></span>
                                        <x-ui-icon name="check" class="h-5 w-5" stroke="2.6" x-show="step > {{ $n }}" x-cloak />
                                        <x-ui-icon :name="$icon" class="h-5 w-5" x-show="step <= {{ $n }}" />
                                    </button>
                                    <span class="mt-3 text-[10.5px] font-semibold uppercase tracking-[0.16em] transition-colors"
                                          :class="step >= {{ $n }} ? 'text-brand-600' : 'text-ink-400'">{{ __('site.consultancy.step', ['number' => $n]) }}</span>
                                    <span class="mt-0.5 block font-display text-[13px] font-semibold leading-snug transition-colors sm:text-[14.5px]"
                                          :class="step >= {{ $n }} ? 'text-ink-950' : 'text-ink-400'">{{ $label }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>

                <div class="px-6 py-8 sm:px-10 sm:py-10">

                    {{-- Step 1 — Register --}}
                    <fieldset x-ref="step1" x-show="step === 1" x-transition.opacity>
                        <legend class="sr-only">{{ __('site.ideas.step_register') }}</legend>
                        <p class="font-display text-[19px] font-bold text-ink-950">{{ __('site.ideas.step_register') }}</p>
                        <p class="mt-1.5 text-[14px] muted">{{ __('site.ideas.register_note') }}</p>

                        <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <x-form.field name="name" :label="__('site.ideas.name_label')" icon="users" required autocomplete="name" x-model="fields.name" />
                            <x-form.field name="email" :label="__('site.forms.email')" type="email" icon="mail" required autocomplete="email" x-model="fields.email"
                                          :readonly="(bool) $innovator" />
                            <x-form.field name="phone" :label="__('site.forms.phone')" type="tel" icon="phone" required autocomplete="tel" x-model="fields.phone" />
                        </div>
                    </fieldset>

                    {{-- Step 2 — Submit your idea: one file, dropped or chosen --}}
                    <fieldset x-ref="step2" x-show="step === 2" x-cloak x-transition.opacity>
                        <legend class="sr-only">{{ __('site.ideas.step_submit') }}</legend>
                        <p class="font-display text-[19px] font-bold text-ink-950">{{ __('site.ideas.step_submit') }}</p>
                        <p class="mt-1.5 text-[14px] muted">{{ __('site.ideas.submit_note') }}</p>

                        <div class="mt-7 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
                            <div>
                                {{-- The title, then the category, then the file: one column, one width --}}
                                <div class="mb-5">
                                    <x-form.field name="title" :label="__('site.ideas.title_label')" icon="lightbulb" required
                                                  :placeholder="__('site.ideas.title_placeholder')" maxlength="200" x-model="fields.title" />
                                </div>

                                {{-- The category, the same width as the drop zone below it --}}
                                <div>
                                    <label for="category" class="mb-2 block text-[13px] font-semibold text-ink-700">
                                        {{ __('site.ideas.category_label') }} <span class="text-brand-600">*</span>
                                    </label>
                                    <div class="relative">
                                        <x-ui-icon name="grid" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-ink-400" />
                                        <select id="category" name="category" required x-model="category"
                                                @class([
                                                    'w-full appearance-none rounded-2xl border bg-ink-50/60 py-3.5 pl-11 pr-10 text-[15px] text-ink-900 transition focus:bg-white focus:outline-none focus:ring-4',
                                                    'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('category'),
                                                    'border-ink-200 hover:border-ink-300 focus:border-brand-400 focus:ring-brand-100' => ! $errors->has('category'),
                                                ])>
                                            <option value="">{{ __('site.ideas.category_placeholder') }}</option>
                                            @foreach (\App\Support\Vocabulary::all('idea_categories') as $value => $label)
                                                <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <x-ui-icon name="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                                    </div>
                                </div>
                                @error('category')
                                    <p class="mt-2 text-[13px] text-red-600">{{ $message }}</p>
                                @enderror

                                <label for="document"
                                       @dragover.prevent="dragging = true" @dragenter.prevent="dragging = true"
                                       @dragleave.prevent="dragging = false"
                                       @drop.prevent="dragging = false; take($event.dataTransfer.files)"
                                       class="group relative mt-5 flex min-h-[17rem] cursor-pointer flex-col items-center justify-center gap-4 rounded-[1.75rem] border-2 border-dashed px-6 py-10 text-center transition duration-300"
                                       :class="dragging
                                           ? 'scale-[1.01] border-brand-500 bg-brand-50 ring-8 ring-brand-100'
                                           : (file ? 'border-brand-300 bg-brand-50/60' : 'border-ink-200 bg-ink-50/40 hover:border-brand-300 hover:bg-brand-50/40')">

                                    {{-- Empty: invite a drop --}}
                                    <div x-show="! file" class="flex flex-col items-center">
                                        <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-brand-600 shadow-[0_14px_30px_-16px_rgba(7,20,38,0.4)] ring-1 ring-ink-100 transition duration-500 group-hover:-translate-y-1"
                                              :class="dragging && '-translate-y-1 bg-brand-600 !text-white'">
                                            <x-ui-icon name="upload" class="h-7 w-7" />
                                        </span>
                                        <span class="mt-5 font-display text-[18px] font-bold text-ink-950">{{ __('site.ideas.drop_title') }}</span>
                                        <span class="mt-1.5 text-[14px] text-ink-500">
                                            {{ __('site.ideas.drop_or') }}
                                            <span class="font-semibold text-brand-700 underline-offset-4 group-hover:underline">{{ __('site.ideas.drop_browse') }}</span>
                                        </span>
                                        <span class="mt-4 rounded-full bg-white px-3.5 py-1.5 text-[12px] font-medium text-ink-500 ring-1 ring-ink-100">{{ __('site.ideas.drop_types') }}</span>
                                    </div>

                                    {{-- Chosen: the file, with a way to change it --}}
                                    <div x-show="file" x-cloak class="flex w-full max-w-sm flex-col items-center">
                                        <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-[0_14px_30px_-12px_var(--color-brand-600)]">
                                            <x-ui-icon name="document" class="h-7 w-7" />
                                        </span>
                                        <span class="mt-5 w-full truncate font-display text-[16px] font-bold text-ink-950" x-text="file?.name"></span>
                                        <span class="mt-1 text-[13px] text-ink-500" x-text="file?.size"></span>
                                        <span class="mt-5 flex items-center gap-3">
                                            <span class="rounded-full border border-ink-200 bg-white px-3.5 py-1.5 text-[12.5px] font-semibold text-ink-700">{{ __('site.ideas.drop_change') }}</span>
                                            <button type="button" @click.prevent="clear()"
                                                    class="rounded-full px-3 py-1.5 text-[12.5px] font-semibold text-red-600 transition hover:bg-red-50">{{ __('site.ideas.drop_remove') }}</button>
                                        </span>
                                    </div>
                                </label>
                                <input id="document" x-ref="file" type="file" name="document" required class="sr-only"
                                       @change="pick()"
                                       accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.png,.jpg,.jpeg">
                                @error('document')
                                    <p class="mt-2 text-[13px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- What is about to be sent --}}
                            <div class="rounded-[1.75rem] border border-ink-100 bg-ink-50/60 p-6">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('site.ideas.summary') }}</p>
                                <dl class="mt-4 space-y-3 text-[14px]">
                                    @foreach ([['name', __('site.ideas.name_label')], ['email', __('site.forms.email')], ['phone', __('site.forms.phone')], ['title', __('site.ideas.title_label')]] as [$key, $label])
                                        <div>
                                            <dt class="text-[12px] text-ink-500">{{ $label }}</dt>
                                            <dd class="mt-0.5 break-words font-medium text-ink-900" x-text="fields.{{ $key }} || '--'">--</dd>
                                        </div>
                                    @endforeach
                                </dl>
                                <button type="button" @click="step = 1"
                                        class="mt-5 inline-flex items-center gap-1.5 text-[13px] font-semibold text-brand-700">
                                    <x-ui-icon name="arrow-left" class="h-3.5 w-3.5" /> {{ __('site.consultancy.edit_details') }}
                                </button>
                            </div>
                        </div>
                    </fieldset>
                </div>

                {{-- Navigation --}}
                <div class="flex flex-col gap-4 border-t border-ink-100 bg-ink-50/70 px-6 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-10">
                    <p class="text-[13px] muted">
                        <span class="font-semibold text-brand-600">*</span> {{ __('site.consultancy.required_note') }} ·
                        {!! __('site.consultancy.step_of', ['current' => '<span x-text="step">1</span>', 'total' => 2]) !!}
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" x-show="step > 1" x-cloak @click="back()" class="btn-ghost">
                            <x-ui-icon name="arrow-left" class="h-4 w-4" /> {{ __('site.consultancy.back') }}
                        </button>

                        <button type="button" x-show="step < last" @click="advance()" class="btn-primary group justify-center">
                            {{ __('site.consultancy.continue') }}
                            <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                        </button>

                        <button type="submit" x-show="step === last" x-cloak class="btn-primary group justify-center">
                            {{ __('site.ideas.submit') }}
                            <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</x-layouts.app>
