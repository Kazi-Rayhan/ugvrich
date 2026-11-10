<?php

namespace App\Filament\Resources\WorkOrders;

use App\Models\Client;
use App\Models\ConsultancyRequest;
use App\Models\Proposal;
use App\Models\WorkOrder;
use App\Support\Money;
use App\Support\Numerals;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

/**
 * The work order form, in the order and wording of the printed form
 * (sections ০১–১২). The sector decides the services, the section 04 rows,
 * the deliverables, stages, special conditions and attachments.
 */
class WorkOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(4)
                ->schema([
                    Select::make('sector')
                        ->label('সেবা খাত (Sector / form)')
                        ->options(WorkOrder::sectorOptions())
                        ->required()
                        ->native(false)
                        ->live()
                        ->columnSpan(2)
                        ->helperText('Chooses the form: WO-ICT-01, WO-EEE-01, …'),
                    DatePicker::make('issue_date')->label('ইস্যুর তারিখ')->default(now())->native(false),
                    Select::make('status')->options(WorkOrder::STATUSES)->default('draft')->required()->native(false),
                ]),

            Tabs::make('work_order')
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs([
                    Tab::make('০১–০২ পক্ষ ও সেবা')->icon('heroicon-o-users')->schema(self::partiesAndServices()),
                    Tab::make('০৩–০৪ পরিধি ও তথ্য')->icon('heroicon-o-clipboard-document-list')->schema(self::scopeAndInfo()),
                    Tab::make('০৫–০৬ হস্তান্তর ও সময়সূচি')->icon('heroicon-o-calendar-days')->schema(self::deliveryAndSchedule()),
                    Tab::make('০৭–০৮ মূল্য ও পরিশোধ')->icon('heroicon-o-banknotes')->schema(self::priceAndPayment()),
                    Tab::make('০৯–১১ শর্ত ও সংযুক্তি')->icon('heroicon-o-document-check')->schema(self::conditionsAndAttachments()),
                    Tab::make('১২ অনুমোদন')->icon('heroicon-o-pencil-square')->schema(self::approval()),
                ]),
        ]);
    }

    /* ------------------------------------------------------------ ০১–০২ */

    protected static function partiesAndServices(): array
    {
        return [
            Section::make('কার্যাদেশের তথ্য')
                ->columns(3)
                ->compact()
                ->schema([
                    TextInput::make('details.quotation_number')->label('প্রস্তাব / মূল্যপ্রস্তাব নম্বর')->maxLength(60),
                    TextInput::make('details.revision')->label('সংশোধন / সংস্করণ')->placeholder('e.g. ০১ / ২০২৬')->maxLength(30),
                    Select::make('proposal_id')
                        ->label('Linked proposal')
                        ->relationship('proposal', 'number')
                        ->searchable()
                        ->preload()
                        ->placeholder('None'),
                ]),

            Section::make('০১  চুক্তিবদ্ধ পক্ষসমূহ')
                ->columns(2)
                ->schema([
                    Fieldset::make('গ্রাহক / কার্যাদেশ প্রদানকারী')
                        ->columns(1)
                        ->schema([
                            Select::make('client_id')
                                ->label('Client on file (optional)')
                                ->relationship('client', 'name')
                                ->getOptionLabelFromRecordUsing(fn (Client $record) => $record->display_name)
                                ->searchable(['name', 'organization', 'email', 'phone'])
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if ($client = Client::find($state)) {
                                        $set('details.client.name', $client->organization ?: $client->name);
                                        $set('details.client.contact', $client->name);
                                        $set('details.client.phone_email', collect([$client->phone, $client->email])->filter()->implode(' / '));
                                        $set('details.client.address', $client->address);
                                    }
                                })
                                ->helperText('Picking one fills the fields below.'),
                            TextInput::make('details.client.name')->label('নাম / প্রতিষ্ঠান')->required()->maxLength(200),
                            TextInput::make('details.client.contact')->label('যোগাযোগের ব্যক্তি')->maxLength(150),
                            TextInput::make('details.client.phone_email')->label('ফোন / ইমেইল')->maxLength(200),
                            Textarea::make('details.client.address')->label('ঠিকানা')->rows(2)->maxLength(500),
                        ]),

                    Fieldset::make('সেবা প্রদানকারী / বাস্তবায়নকারী পক্ষ')
                        ->columns(1)
                        ->schema([
                            TextEntry::make('provider_text')
                                ->hiddenLabel()
                                ->html()
                                ->state(fn (Get $get) => e(config('work_orders.provider.name')).'<br>'
                                    .e(config('work_orders.provider.university')).'<br>'
                                    .'দায়িত্বপ্রাপ্ত বিভাগ: <b>'.e(WorkOrder::sector($get('sector'))['department'] ?? '—').'</b>'),
                            TextInput::make('details.coordinator')->label('যোগাযোগ / সমন্বয়কারী')->maxLength(150),
                        ]),
                ]),

            Section::make('০২  নির্বাচিত পরামর্শসেবা')
                ->schema([
                    CheckboxList::make('details.services')
                        ->hiddenLabel()
                        ->options(fn (Get $get) => WorkOrder::sector($get('sector'))['services'] ?? [])
                        ->columns(fn (Get $get) => WorkOrder::sector($get('sector'))['service_columns'] ?? 2)
                        ->visible(fn (Get $get) => filled($get('sector'))),
                    self::chooseSectorFirst(),
                ]),
        ];
    }

    /* ------------------------------------------------------------ ০৩–০৪ */

    protected static function scopeAndInfo(): array
    {
        return [
            Section::make('০৩  অর্পিত কাজের পরিধি')
                ->schema([
                    Textarea::make('details.summary')
                        ->label('কাজ / সমস্যার সারসংক্ষেপ')
                        ->helperText(fn (Get $get) => WorkOrder::sector($get('sector'))['summary_hint'] ?? null)
                        ->rows(3)
                        ->maxLength(1500),
                    Textarea::make('details.included')
                        ->label('অন্তর্ভুক্ত কাজ ও সীমানা')
                        ->helperText('সাইট, কাজ, যন্ত্রপাতি, ব্যক্তি এবং সম্মত কার্যক্রম উল্লেখ করুন।')
                        ->rows(3)
                        ->maxLength(1500),
                    Textarea::make('details.excluded')
                        ->label('বহির্ভূত কাজ / নির্ভরশীলতা')
                        ->helperText('অন্তর্ভুক্ত নয় এমন কাজ ও গ্রাহক-সরবরাহকৃত উপকরণ/অনুমোদন লিখুন।')
                        ->rows(3)
                        ->maxLength(1500),
                ]),

            Section::make('০৪  সেবাভিত্তিক প্রয়োজনীয় তথ্য')
                ->schema([
                    Group::make(fn (Get $get) => self::infoFields($get('sector'))),
                ]),
        ];
    }

    /** Section 04 for the chosen sector: one block per row, a field per blank, a tick per box. */
    protected static function infoFields(?string $sector): array
    {
        $rows = WorkOrder::sector($sector)['info'] ?? null;

        if (! $rows) {
            return [self::chooseSectorFirst()];
        }

        return collect($rows)->map(function (array $row) {
            [$label, $parts] = $row;
            $components = [];
            $caption = null;

            foreach ($parts as $part) {
                match ($part[0]) {
                    'text' => $caption = rtrim($part[1], ':'),
                    'field' => $components[] = TextInput::make('details.info.'.$part[1])
                        ->label($caption ?? $label)
                        ->maxLength(300)
                        ->columnSpan(in_array($part[2] ?? 'md', ['lg', 'xl'], true) ? 2 : 1),
                    'check' => $components[] = Checkbox::make('details.info.'.$part[1])->label($part[2])->inline(),
                };

                if ($part[0] === 'field') {
                    $caption = null;
                }
            }

            return Fieldset::make($label)->columns(4)->schema($components);
        })->all();
    }

    /* ------------------------------------------------------------ ০৫–০৬ */

    protected static function deliveryAndSchedule(): array
    {
        return [
            Section::make('০৫  হস্তান্তরযোগ্য কাজ ও গ্রহণযোগ্যতার শর্ত')
                ->description('Printed exactly as on the approved form for this sector.')
                ->collapsible()
                ->schema([
                    TextEntry::make('deliverables_preview')
                        ->hiddenLabel()
                        ->html()
                        ->state(fn (Get $get) => self::table(
                            ['হস্তান্তরযোগ্য কাজ', 'ন্যূনতম চুক্তিভুক্ত আউটপুট', 'গ্রাহকের গ্রহণের ভিত্তি'],
                            WorkOrder::sector($get('sector'))['deliverables'] ?? [],
                        )),
                ]),

            Section::make('০৬  কাজের ধাপ ও সময়সূচি')
                ->schema([
                    Group::make(fn (Get $get) => collect(WorkOrder::sector($get('sector'))['stages'] ?? [])
                        ->map(fn (array $stage, int $i) => DatePicker::make("details.stage_dates.{$i}")
                            ->label($stage[0])
                            ->helperText('যাচাই: '.$stage[1])
                            ->native(false))
                        ->all())
                        ->columns(4),
                    Grid::make(3)->schema([
                        DatePicker::make('details.start_date')->label('কাজ শুরুর তারিখ')->native(false),
                        DatePicker::make('details.end_date')->label('সম্ভাব্য সমাপ্তি')->native(false)->afterOrEqual('details.start_date'),
                        ToggleButtons::make('details.mode')->label('মাধ্যম')->options(config('work_orders.modes'))->inline(),
                    ]),
                ]),
        ];
    }

    /* ------------------------------------------------------------ ০৭–০৮ */

    protected static function priceAndPayment(): array
    {
        $fee = fn (int $i, string $label) => Fieldset::make($label)
            ->columns(3)
            ->schema([
                TextInput::make("details.fees.{$i}.qty")->label('পরিমাণ')->numeric()->minValue(0)->placeholder('1')->live(onBlur: true),
                TextInput::make("details.fees.{$i}.unit")->label('একক')->placeholder('e.g. প্যাকেজ / দিন / পৃষ্ঠা')->maxLength(40),
                TextInput::make("details.fees.{$i}.rate")->label('হার (টাকা)')->numeric()->minValue(0)->prefix('৳')->live(onBlur: true),
            ]);

        return [
            Section::make('০৭  কাজের মূল্য ও পরিশোধ অনুমোদন')
                ->schema([
                    ...collect(config('work_orders.fee_lines'))->map(fn ($label, $i) => $fee($i, $label))->all(),
                    Grid::make(3)->schema([
                        ToggleButtons::make('details.tax_mode')
                            ->label('ভ্যাট / কর / উৎসে কর্তন')
                            ->options(['add' => 'যোগ (VAT/কর)', 'deduct' => 'বিয়োগ (উৎসে কর্তন)'])
                            ->default('add')
                            ->inline()
                            ->live(),
                        TextInput::make('details.tax_amount')->label('পরিমাণ (টাকা)')->numeric()->minValue(0)->prefix('৳')->live(onBlur: true),
                        TextEntry::make('totals_preview')
                            ->label('সর্বমোট কার্যাদেশ মূল্য')
                            ->state(fn (Get $get) => self::grandTotal($get))
                            ->weight('bold')
                            ->size('lg'),
                    ]),
                    TextInput::make('details.amount_words')
                        ->label('টাকার পরিমাণ কথায় (BDT)')
                        ->placeholder('Leave empty to write it automatically, e.g. পঁয়তাল্লিশ হাজার টাকা মাত্র')
                        ->maxLength(300),
                ]),

            Section::make('০৮  কিস্তিতে পরিশোধ ও বাণিজ্যিক শর্ত')
                ->schema([
                    Grid::make(3)->schema(collect(config('work_orders.payment_steps'))
                        ->map(fn (array $step, int $i) => TextInput::make("details.installments.{$i}.percent")
                            ->label($step['label'])
                            ->helperText('প্রমাণ: '.$step['proof'])
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'))
                        ->all()),
                    TextInput::make('details.payment_due')
                        ->label('মূল্যপ্রস্তাবের মেয়াদ / বিল পরিশোধের সময়')
                        ->placeholder('e.g. চালান প্রাপ্তির ৭ দিনের মধ্যে')
                        ->maxLength(200),
                ]),
        ];
    }

    /* ------------------------------------------------------------ ০৯–১১ */

    protected static function conditionsAndAttachments(): array
    {
        return [
            Section::make('০৯  সেবাভিত্তিক বিশেষ শর্ত')
                ->description('Printed exactly as on the approved form for this sector.')
                ->collapsible()
                ->schema([
                    TextEntry::make('conditions_preview')
                        ->hiddenLabel()
                        ->html()
                        ->state(fn (Get $get) => collect(WorkOrder::sector($get('sector'))['conditions'] ?? [])
                            ->map(fn (array $c, int $i) => '<p style="margin:0 0 8px"><b>'.Numerals::localize(sprintf('%02d', $i + 1), 'bn').' '.e($c[0]).'</b> '.e(str_replace('{revisions}', '____', $c[1])).'</p>')
                            ->implode('') ?: 'Choose the sector first.'),
                    TextInput::make('details.revisions')
                        ->label('অন্তর্ভুক্ত সংশোধন পর্ব (টি)')
                        ->numeric()
                        ->minValue(0)
                        ->visible(fn (Get $get) => collect(WorkOrder::sector($get('sector'))['conditions'] ?? [])
                            ->contains(fn ($c) => str_contains($c[1], '{revisions}'))),
                ]),

            Section::make('১০  সাধারণ প্রশাসন ও বিরোধ নিষ্পত্তি')
                ->description('The same on every form.')
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextEntry::make('general_preview')
                        ->hiddenLabel()
                        ->html()
                        ->state(collect(config('work_orders.general_terms'))
                            ->map(fn (array $t) => '<p style="margin:0 0 8px">• <b>'.e($t[0]).'</b> '.e($t[1]).'</p>')
                            ->implode('')),
                ]),

            Section::make('১১  সংযুক্ত ও রেফারেন্স নথি')
                ->schema([
                    CheckboxList::make('details.attachments')
                        ->hiddenLabel()
                        ->options(fn (Get $get) => WorkOrder::sector($get('sector'))['attachments'] ?? [])
                        ->columns(2),
                    TextInput::make('details.other_attachments')->label('অন্যান্য সংযুক্তি / পৃষ্ঠাসংখ্যা')->maxLength(300),
                ]),
        ];
    }

    /* --------------------------------------------------------------- ১২ */

    protected static function approval(): array
    {
        return [
            Section::make('১২  আনুষ্ঠানিক গ্রহণ ও অনুমোদন')
                ->columns(2)
                ->schema([
                    Fieldset::make('গ্রাহকের পক্ষে / অনুমোদিত স্বাক্ষরকারী')
                        ->columns(1)
                        ->schema([
                            TextInput::make('details.client_signatory')->label('নাম / পদবি')->maxLength(150),
                            DatePicker::make('details.client_sign_date')->label('তারিখ')->native(false),
                        ]),
                    Fieldset::make('UGV RICH-এর পক্ষে / অনুমোদিত স্বাক্ষরকারী')
                        ->columns(1)
                        ->schema([
                            TextInput::make('details.rich_signatory')->label('নাম / পদবি')->maxLength(150),
                            DatePicker::make('details.rich_sign_date')->label('তারিখ')->native(false),
                        ]),
                    TextInput::make('details.tech_approval')
                        ->label('অতিরিক্ত প্রযুক্তিগত যাচাই / প্রকল্প ব্যবস্থাপকের অনুমোদন (প্রযোজ্য হলে)')
                        ->maxLength(150),
                    DatePicker::make('details.tech_approval_date')->label('তারিখ')->native(false),
                    CheckboxList::make('details.checklist')
                        ->label('ইস্যুর আগে যাচাই')
                        ->options(config('work_orders.checklist'))
                        ->columns(4)
                        ->columnSpanFull(),
                    Callout::make('Signatures and seals are signed by hand on the printed copy.')
                        ->info()
                        ->columnSpanFull(),
                ]),
        ];
    }

    /* ----------------------------------------------------------- helpers */

    protected static function chooseSectorFirst(): Callout
    {
        return Callout::make('Choose the sector (সেবা খাত) at the top first.')
            ->warning()
            ->visible(fn (Get $get) => blank($get('sector')));
    }

    protected static function grandTotal(Get $get): string
    {
        try {
            $subtotal = 0;

            foreach (range(0, 2) as $i) {
                $rate = Money::toMinor($get("details.fees.{$i}.rate"));
                $qty = (string) $get("details.fees.{$i}.qty");
                $subtotal += $rate === 0 ? 0 : Money::multiply($rate, is_numeric($qty) ? $qty : '1');
            }

            $tax = Money::toMinor($get('details.tax_amount'));
            $grand = $get('details.tax_mode') === 'deduct' ? $subtotal - $tax : $subtotal + $tax;

            return Money::format(max($grand, 0), true).'  (উপমোট '.Money::format($subtotal, true).')';
        } catch (\InvalidArgumentException) {
            return '—';
        }
    }

    /** @param  list<array<int, string>>  $rows */
    protected static function table(array $head, array $rows): string
    {
        if ($rows === []) {
            return 'Choose the sector first.';
        }

        $th = collect($head)->map(fn ($h) => '<th style="text-align:left;padding:6px 10px;border-bottom:1px solid #cbd5e1">'.e($h).'</th>')->implode('');
        $tr = collect($rows)->map(fn ($r) => '<tr>'.collect($r)->map(fn ($c) => '<td style="padding:6px 10px;border-bottom:1px solid #e2e8f0">'.e($c).'</td>')->implode('').'</tr>')->implode('');

        return '<table style="width:100%;border-collapse:collapse;font-size:13px"><thead><tr>'.$th.'</tr></thead><tbody>'.$tr.'</tbody></table>';
    }

    /**
     * Start-up values when opened from a consultancy request (?request=)
     * or a proposal (?proposal=).
     *
     * @return array<string, mixed>
     */
    public static function prefill(?int $requestId, ?int $proposalId): array
    {
        $data = [
            'issue_date' => now()->toDateString(),
            'status' => 'draft',
            'details' => [
                'tax_mode' => 'add',
                'installments' => [['percent' => 50], ['percent' => 30], ['percent' => 20]],
            ],
        ];

        if ($request = ConsultancyRequest::with('category')->find($requestId)) {
            $isType = in_array($request->organization, ConsultancyRequest::ORGANIZATION_TYPES, true);

            $data['consultancy_request_id'] = $request->id;
            $data['sector'] = WorkOrder::sectorForCategory($request->category);
            data_set($data, 'details.client', [
                'name' => ! $isType && filled($request->organization) ? $request->organization : $request->name,
                'contact' => $request->name,
                'phone_email' => collect([$request->phone, $request->email])->filter()->implode(' / '),
            ]);
            data_set($data, 'details.summary', $request->requirement);
        }

        if ($proposal = Proposal::with(['client', 'items', 'categories'])->find($proposalId)) {
            $client = $proposal->client;

            $data['proposal_id'] = $proposal->id;
            $data['client_id'] = $client?->id;
            $data['consultancy_request_id'] ??= $proposal->consultancy_request_id;
            $data['sector'] ??= WorkOrder::sectorForCategory($proposal->categories->first());
            data_set($data, 'details.quotation_number', $proposal->number);
            data_set($data, 'details.client', [
                'name' => $client?->organization ?: $client?->name,
                'contact' => $client?->name,
                'phone_email' => collect([$client?->phone, $client?->email])->filter()->implode(' / '),
                'address' => $client?->address,
            ]);
            data_set($data, 'details.summary', $proposal->title);
            data_set($data, 'details.included', $proposal->items->map(fn ($item) => '• '.$item->service_name)->implode("\n")
                .collect($proposal->conditions ?? [])->map(fn ($c) => "\n• {$c['label']}: {$c['details']}")->implode(''));
            data_set($data, 'details.fees.0', ['qty' => '1', 'unit' => 'প্যাকেজ', 'rate' => Money::fromMinor(Money::toMinor($proposal->total))]);
            data_set($data, 'details.payment_due', $proposal->payment_terms);
            data_set($data, 'details.start_date', null);
        }

        return $data;
    }
}
