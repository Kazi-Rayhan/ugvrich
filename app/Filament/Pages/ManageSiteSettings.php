<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ServiceCategories\Schemas\ServiceCategoryForm;
use App\Models\Setting;
use App\Support\MeetingSlots;
use App\Support\Site;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSiteSettings extends Page implements HasSchemas
{
    public const ACCESS_PERMISSION = 'manage_site_settings';

    use InteractsWithSchemas;

    protected string $view = 'filament.pages.manage-site-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|\UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Site Settings';

    protected static ?string $navigationLabel = 'Site Settings';

    /** Settings stored as JSON arrays rather than plain strings. */
    protected const JSON_KEYS = [
        'mission_points',
        'why_choose',
        'who_we_serve',
        'research_activities',
        'partnership_types',
        'process_steps',
        'booking_closed_days',
    ];

    /** Of those, the ones whose items are themselves arrays (not plain strings). */
    protected const STRUCTURED_KEYS = ['why_choose', 'process_steps', 'booking_closed_days'];

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->isAdmin() || $user->hasPermission(self::ACCESS_PERMISSION));
    }

    public function mount(): void
    {
        $state = [];

        foreach (Setting::query()->get(['key', 'value', 'value_bn']) as $setting) {
            $state[$setting->key] = $this->decode($setting->key, $setting->value);

            // The Bangla twin is the same row, one column over.
            if ($setting->key !== 'pipeline_counts') {
                $state[$setting->key.'_bn'] = $this->decode($setting->key, $setting->value_bn);
            }
        }

        $this->form->fill($state);
    }

    /** A stored value as the form wants it: lists unpacked, everything else as-is. */
    protected function decode(string $key, mixed $value): mixed
    {
        if ($key === 'pipeline_counts') {
            $decoded = json_decode((string) $value, true);

            return is_array($decoded) ? $decoded : [];
        }

        if (! in_array($key, self::JSON_KEYS, true)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);
        $decoded = is_array($decoded) ? $decoded : [];

        // Simple repeaters need each item wrapped as ['value' => ...].
        return in_array($key, self::STRUCTURED_KEYS, true)
            ? $decoded
            : array_map(fn ($item) => ['value' => $item], $decoded);
    }

    /** A form value as the database wants it. */
    protected function encode(string $key, mixed $value): mixed
    {
        if (! in_array($key, self::JSON_KEYS, true)) {
            return $value;
        }

        $value = is_array($value) ? $value : [];

        // Unwrap simple repeaters back into a flat list of strings.
        if (! in_array($key, self::STRUCTURED_KEYS, true)) {
            $value = array_map(fn ($item) => is_array($item) ? ($item['value'] ?? '') : $item, $value);
        }

        return json_encode(array_values($value), JSON_UNESCAPED_UNICODE);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Languages')
                    ->contained(false)
                    ->tabs([
                        Tabs\Tab::make('English')->icon('heroicon-o-language')->schema([
                            Tabs::make()->tabs([

                            Tabs\Tab::make('Identity')->icon('heroicon-o-identification')->schema([
                                TextInput::make('site_name')->label('Site name')->required(),
                                TextInput::make('site_tagline')->label('Tagline'),
                                Textarea::make('site_motto')->label('Motto')->rows(2),
                            ]),

                            Tabs\Tab::make('Hero')->icon('heroicon-o-sparkles')->schema([
                                TextInput::make('hero_eyebrow')->label('Eyebrow'),
                                Textarea::make('hero_heading')->label('Heading')->rows(2),
                                TextInput::make('hero_highlight')
                                    ->label('Highlighted word')
                                    ->helperText('The first occurrence of this word in the heading gets the gradient treatment. Leave empty for none.'),
                                Textarea::make('hero_subheading')->label('Sub-heading')->rows(4),

                                FileUpload::make('hero_video')
                                    ->label('Hero video')
                                    ->disk('public')
                                    ->directory('hero')
                                    ->acceptedFileTypes(['video/mp4', 'video/webm'])
                                    ->helperText('MP4 or WebM, 16:9. Plays muted and looped behind the hero. Leave empty to use the bundled clip. The real ceiling is the server\'s upload_max_filesize, not this form.'),

                                FileUpload::make('hero_poster')
                                    ->label('Hero poster frame')
                                    ->image()
                                    ->disk('public')
                                    ->directory('hero')
                                    ->helperText('Shown while the video loads, and instead of it for visitors who ask for reduced motion.'),
                            ]),

                            Tabs\Tab::make('About')->icon('heroicon-o-information-circle')->schema([
                                Textarea::make('about_intro')->label('Intro paragraph')->rows(4),
                                Textarea::make('about_body')->label('Body paragraph')->rows(6),
                                Textarea::make('vision')->label('Vision statement')->rows(3),

                                FileUpload::make('vision_media')
                                    ->label('Vision photo')
                                    ->image()
                                    ->disk('public')
                                    ->directory('about')
                                    ->imageEditor()
                                    ->helperText('Portrait photo behind the vision statement on the home page. Leave empty to use the bundled campus photo.'),
                                Textarea::make('mission_statement')
                                    ->label('Mission statement')
                                    ->rows(3)
                                    ->helperText('One-sentence mission shown on the home page.'),
                                TextInput::make('mission_intro')->label('Mission lead-in (About page)'),

                                Repeater::make('mission_points')
                                    ->label('Mission points')
                                    ->simple(TextInput::make('value')->required())
                                    ->reorderable()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add mission point'),
                            ]),

                            Tabs\Tab::make('Why choose us')->icon('heroicon-o-star')->schema([
                                Repeater::make('why_choose')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextInput::make('title')->required(),
                                        TextInput::make('description')->required()->columnSpan(2),
                                        Select::make('icon')
                                            ->options(ServiceCategoryForm::ICONS)
                                            ->native(false),
                                    ])
                                    ->columns(4)
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state) => $state['title'] ?? null)
                                    ->defaultItems(0)
                                    ->addActionLabel('Add reason'),
                            ]),

                            Tabs\Tab::make('Audience')->icon('heroicon-o-user-group')->schema([
                                Repeater::make('who_we_serve')
                                    ->label('Who we serve')
                                    ->simple(TextInput::make('value')->required())
                                    ->reorderable()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add audience'),

                                Repeater::make('partnership_types')
                                    ->label('Partnership types')
                                    ->simple(TextInput::make('value')->required())
                                    ->reorderable()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add partnership type'),
                            ]),

                            Tabs\Tab::make('Research')->icon('heroicon-o-beaker')->schema([
                                Repeater::make('research_activities')
                                    ->label('Research & innovation activities')
                                    ->simple(TextInput::make('value')->required())
                                    ->reorderable()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add activity'),
                            ]),

                            Tabs\Tab::make('Process')->icon('heroicon-o-map')->schema([
                                Repeater::make('process_steps')
                                    ->label('How we work')
                                    ->schema([
                                        TextInput::make('title')->required(),
                                        Textarea::make('description')->required()->rows(3),
                                    ])
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state) => $state['title'] ?? null)
                                    ->defaultItems(0)
                                    ->addActionLabel('Add step'),
                            ]),

                            Tabs\Tab::make('Pipeline')->icon('heroicon-o-funnel')->schema([
                                Section::make('Innovation pipeline')
                                    ->description('How many projects are currently at each stage. Shown on the home page dashboard.')
                                    ->columns(4)
                                    ->schema(
                                        collect(config('rich.pipeline_stages'))
                                            ->map(fn ($label, $stage) => TextInput::make('pipeline_counts.'.$stage)
                                                ->label($label)
                                                ->numeric()
                                                ->minValue(0)
                                                ->default(0))
                                            ->values()
                                            ->all()
                                    ),
                            ]),

                            Tabs\Tab::make('Booking')->icon('heroicon-o-calendar-days')->schema([
                        Section::make('Consultancy meeting slots')
                            ->description('The public request form offers these slots, and rejects anything outside them. Changing a value here changes the form, the validation and the admin panel together.')
                            ->columns(3)
                            ->schema([
                                TimePicker::make('booking_opens')
                                    ->label('Opens')
                                    ->seconds(false)
                                    ->default(MeetingSlots::DEFAULT_OPENS)
                                    ->helperText('First slot starts here.'),

                                TimePicker::make('booking_closes')
                                    ->label('Closes')
                                    ->seconds(false)
                                    ->default(MeetingSlots::DEFAULT_CLOSES)
                                    ->helperText('Last slot ends here.'),

                                Select::make('booking_slot_minutes')
                                    ->label('Slot length')
                                    ->native(false)
                                    ->default(MeetingSlots::DEFAULT_MINUTES)
                                    ->options([15 => '15 minutes', 20 => '20 minutes', 30 => '30 minutes', 45 => '45 minutes', 60 => '60 minutes']),

                                TimePicker::make('booking_break_starts')
                                    ->label('Lunch break starts')
                                    ->seconds(false)
                                    ->default(MeetingSlots::DEFAULT_BREAK_STARTS)
                                    ->helperText('Leave both empty for no break.'),

                                TimePicker::make('booking_break_ends')
                                    ->label('Lunch break ends')
                                    ->seconds(false)
                                    ->default(MeetingSlots::DEFAULT_BREAK_ENDS)
                                    ->helperText('Slots running into the break are not offered.'),

                                CheckboxList::make('booking_closed_days')
                                    ->label('Closed days')
                                    ->options([
                                        0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
                                        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday',
                                    ])
                                    ->columns(4)
                                    ->default(MeetingSlots::DEFAULT_CLOSED_DAYS)
                                    ->helperText('No meetings are offered on these days.')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                    Tabs\Tab::make('Contact')->icon('heroicon-o-phone')->schema([
                                Section::make('Office')->columns(2)->schema([
                                    Textarea::make('contact_address')->rows(2)->columnSpanFull(),
                                    TextInput::make('contact_email')->email(),
                                    TextInput::make('contact_phone')->tel(),
                            TextInput::make('contact_whatsapp')
                                ->label('WhatsApp number')
                                ->tel()
                                ->helperText('With the country code, e.g. +880 1712 345678. Leave empty to hide the WhatsApp button.'),
                                    TextInput::make('contact_website')->url(),
                                    TextInput::make('contact_hours')->label('Office hours'),
                                ]),

                                Section::make('Social')->columns(2)->schema([
                                    TextInput::make('social_facebook')->label('Facebook')->url(),
                                    TextInput::make('social_linkedin')->label('LinkedIn')->url(),
                                    TextInput::make('social_x')->label('X')->url(),
                                    TextInput::make('social_youtube')->label('YouTube')->url(),
                                ]),
                            ]),
                        ])->contained(false),
                        ]),

                        Tabs\Tab::make('বাংলা')->icon('heroicon-o-language')->schema($this->banglaFields()),
                    ]),
            ]);
    }

    /**
     * The Bangla side of the same settings. Each field writes to the Bangla
     * column of its own row, so anything left empty keeps showing the English.
     *
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    protected function banglaFields(): array
    {
        $note = 'খালি রাখলে ইংরেজি লেখাটিই দেখানো হবে।';

        return [
            Tabs::make('Bangla')->contained(false)->tabs([

                Tabs\Tab::make('পরিচিতি')->icon('heroicon-o-identification')->schema([
                    TextInput::make('site_name_bn')->label('সাইটের নাম')->helperText($note),
                    TextInput::make('site_tagline_bn')->label('ট্যাগলাইন')->helperText($note),
                    Textarea::make('site_motto_bn')->label('মটো')->rows(2)->helperText($note),
                ]),

                Tabs\Tab::make('হিরো')->icon('heroicon-o-sparkles')->schema([
                    TextInput::make('hero_eyebrow_bn')->label('উপরের ছোট লেখা')->helperText($note),
                    Textarea::make('hero_heading_bn')->label('শিরোনাম')->rows(2)->helperText($note),
                    TextInput::make('hero_highlight_bn')
                        ->label('হাইলাইট করা শব্দ')
                        ->helperText('শিরোনামের এই শব্দটি রঙিন হয়ে দেখাবে। '.$note),
                    Textarea::make('hero_subheading_bn')->label('উপ-শিরোনাম')->rows(4)->helperText($note),
                ]),

                Tabs\Tab::make('পরিচিতি পাতা')->icon('heroicon-o-information-circle')->schema([
                    Textarea::make('about_intro_bn')->label('শুরুর অনুচ্ছেদ')->rows(4)->helperText($note),
                    Textarea::make('about_body_bn')->label('মূল অনুচ্ছেদ')->rows(6)->helperText($note),
                    Textarea::make('vision_bn')->label('ভিশন')->rows(3)->helperText($note),
                    Textarea::make('mission_statement_bn')->label('মিশন')->rows(3)->helperText($note),
                    TextInput::make('mission_intro_bn')->label('মিশনের ভূমিকা')->helperText($note),

                    Repeater::make('mission_points_bn')
                        ->label('মিশনের পয়েন্ট')
                        ->simple(TextInput::make('value')->required())
                        ->reorderable()
                        ->defaultItems(0)
                        ->helperText($note)
                        ->addActionLabel('পয়েন্ট যোগ করুন'),
                ]),

                Tabs\Tab::make('কেন আমরা')->icon('heroicon-o-star')->schema([
                    Repeater::make('why_choose_bn')
                        ->hiddenLabel()
                        ->schema([
                            TextInput::make('title')->label('শিরোনাম')->required(),
                            TextInput::make('description')->label('বর্ণনা')->required()->columnSpan(2),
                            Select::make('icon')->label('আইকন')->options(ServiceCategoryForm::ICONS)->native(false),
                        ])
                        ->columns(4)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state) => $state['title'] ?? null)
                        ->defaultItems(0)
                        ->helperText($note)
                        ->addActionLabel('কারণ যোগ করুন'),
                ]),

                Tabs\Tab::make('কাদের জন্য')->icon('heroicon-o-user-group')->schema([
                    Repeater::make('who_we_serve_bn')
                        ->label('আমরা যাদের সেবা দিই')
                        ->simple(TextInput::make('value')->required())
                        ->reorderable()
                        ->defaultItems(0)
                        ->helperText($note)
                        ->addActionLabel('যোগ করুন'),

                    Repeater::make('partnership_types_bn')
                        ->label('অংশীদারিত্বের ধরন')
                        ->simple(TextInput::make('value')->required())
                        ->reorderable()
                        ->defaultItems(0)
                        ->helperText($note)
                        ->addActionLabel('যোগ করুন'),
                ]),

                Tabs\Tab::make('গবেষণা')->icon('heroicon-o-beaker')->schema([
                    Repeater::make('research_activities_bn')
                        ->label('গবেষণা ও উদ্ভাবন কার্যক্রম')
                        ->simple(TextInput::make('value')->required())
                        ->reorderable()
                        ->defaultItems(0)
                        ->helperText($note)
                        ->addActionLabel('কার্যক্রম যোগ করুন'),
                ]),

                Tabs\Tab::make('প্রক্রিয়া')->icon('heroicon-o-map')->schema([
                    Repeater::make('process_steps_bn')
                        ->label('আমরা যেভাবে কাজ করি')
                        ->schema([
                            TextInput::make('title')->label('ধাপের নাম')->required(),
                            Textarea::make('description')->label('বর্ণনা')->required()->rows(3),
                        ])
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state) => $state['title'] ?? null)
                        ->defaultItems(0)
                        ->helperText($note)
                        ->addActionLabel('ধাপ যোগ করুন'),
                ]),

                Tabs\Tab::make('যোগাযোগ')->icon('heroicon-o-phone')->schema([
                    Section::make('অফিস')->columns(2)->schema([
                        Textarea::make('contact_address_bn')->label('ঠিকানা')->rows(2)->columnSpanFull()->helperText($note),
                        TextInput::make('contact_hours_bn')->label('অফিস সময়')->helperText($note),
                    ]),
                ]),
            ]),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        // `hero_heading_bn` belongs to the `hero_heading` row, in its Bangla column.
        $bangla = [];

        foreach ($state as $key => $value) {
            if (str_ends_with($key, '_bn')) {
                $bangla[substr($key, 0, -3)] = $value;
                unset($state[$key]);
            }
        }

        foreach ($state as $key => $value) {
            if ($key === 'pipeline_counts') {
                $counts = collect(array_keys(config('rich.pipeline_stages')))
                    ->mapWithKeys(fn ($stage) => [$stage => (int) (($value ?? [])[$stage] ?? 0)])
                    ->all();

                Setting::updateOrCreate(['key' => $key], ['value' => json_encode($counts), 'type' => 'json', 'group' => 'general']);

                continue;
            }

            $isJson = in_array($key, self::JSON_KEYS, true);

            $translation = array_key_exists($key, $bangla) ? $this->encode($key, $bangla[$key]) : null;

            // An empty Bangla field means "show the English text", not "show nothing".
            if (in_array($translation, ['', '[]', 'null', null], true)) {
                $translation = null;
            }

            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $this->encode($key, $value),
                    'value_bn' => $translation,
                    'type' => $isJson ? 'json' : 'text',
                    'group' => match (true) {
                        str_starts_with($key, 'contact_') => 'contact',
                        str_starts_with($key, 'social_') => 'social',
                        str_starts_with($key, 'hero_') => 'hero',
                        default => 'general',
                    },
                ],
            );
        }

        Site::flush();

        Notification::make()
            ->title('Site settings saved')
            ->body('The public site has been updated.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save changes')
                ->action('save')
                ->keyBindings(['mod+s']),

            Action::make('view_site')
                ->label('View site')
                ->url(url('/'), shouldOpenInNewTab: true)
                ->color('gray')
                ->icon('heroicon-o-arrow-top-right-on-square'),
        ];
    }
}
