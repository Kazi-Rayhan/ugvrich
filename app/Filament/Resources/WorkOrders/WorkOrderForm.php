<?php

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Support\SalesConditions;
use App\Models\Client;
use App\Models\ConsultancyRequest;
use App\Models\Proposal;
use App\Models\ServiceCategory;
use App\Models\WorkOrder;
use App\Support\Money;
use Closure;
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
use Illuminate\Support\Str;

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
            Tabs::make('Work order details')
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs(array_map(
                    fn (array $part) => Tab::make($part['title'])->icon($part['icon'])->schema($part['schema']),
                    self::parts(),
                )),
        ]);
    }

    public static function parts(
        bool $linked = false,
        bool|Closure $requireSector = false,
        array $clientFields = [],
        bool|Closure $canEdit = true,
        bool $useCategoryLabels = false,
    ): array {
        [$scope, $requirements] = self::scopeAndInfo();
        [$deliverables, $schedule] = self::deliveryAndSchedule();
        [$conditions, $generalTerms, $attachments] = self::conditionsAndAttachments();
        [$approval] = self::approval();

        $template = Section::make('Work order template')
            ->description($useCategoryLabels
                ? 'The category determines the available subcategories and print template.'
                : 'Choose the sector before entering the scope. It determines the available services and print template.')
            ->icon('heroicon-o-document-check')
            ->columns(2)
            ->schema([
                self::sectorField($requireSector, $useCategoryLabels),
                self::revisionField(),
                ...($linked ? [] : [
                    DatePicker::make('issue_date')->label('Issue date')->native(false),
                    TextInput::make('details.quotation_number')->label('Proposal / quotation number')->maxLength(60),
                    Select::make('proposal_id')->label('Linked proposal')->relationship('proposal', 'number')->searchable()->preload(),
                ]),
                self::revisionRoundsField(),
            ]);

        return [
            [
                'title' => 'Work order',
                'description' => 'Client and work order details',
                'icon' => 'heroicon-o-document-text',
                'schema' => [
                    self::contractingParties($linked, $clientFields, $canEdit, showDepartment: ! $useCategoryLabels)
                        ->heading('Contracting parties')
                        ->icon('heroicon-o-users'),
                    Group::make([
                        $template,
                        self::servicesSection($useCategoryLabels)
                            ->description($useCategoryLabels ? null : 'Select the services covered by this work order.')
                            ->visible(fn (Get $get) => filled($get('sector'))),
                    ])->visible($canEdit)->columnSpanFull(),
                ],
            ],
            [
                'title' => 'Scope & schedule',
                'description' => 'Requirements and milestones',
                'icon' => 'heroicon-o-calendar-days',
                'schema' => [
                    $scope->heading('Scope of work')->collapsible(),
                    $requirements->heading($useCategoryLabels ? 'Category requirements' : 'Sector requirements')
                        ->visible(fn (Get $get) => filled($get('sector')))
                        ->collapsible(),
                    $schedule->heading('Milestones and schedule')->collapsible(),
                ],
            ],
            [
                'title' => 'Payments',
                'description' => 'Installment schedule',
                'icon' => 'heroicon-o-banknotes',
                'schema' => [
                    ...($linked ? [] : [self::feesSection()->collapsible()]),
                    self::installmentsSection()->collapsible(),
                ],
            ],
            [
                'title' => 'Work order review',
                'description' => 'Attachments and approvals',
                'icon' => 'heroicon-o-clipboard-document-check',
                'schema' => [
                    $attachments->heading('Attachments')->collapsible(),
                    $approval->heading('Signatories and pre-issue checks')->collapsible(),
                    Section::make('Bangla print previews')
                        ->description('Review the deliverables, service conditions, and standard terms as they appear on the printed form.')
                        ->schema([$deliverables->collapsed(false), $conditions, $generalTerms->collapsed(false)])
                        ->collapsible()
                        ->collapsed(),
                ],
            ],
        ];
    }

    protected static function sectorField(bool|Closure $required, bool $useCategoryLabels): Select
    {
        return Select::make('sector')
            ->label($useCategoryLabels ? 'Category' : 'Sector')
            ->options(WorkOrder::sectorOptions(includeDepartment: ! $useCategoryLabels))
            ->required($required)
            ->native(false)
            ->live()
            ->afterStateUpdated($useCategoryLabels ? function ($state, Get $get, Set $set): void {
                $set('details.services', []);
                self::syncProposalPricing($state, [], $get, $set);
            } : null)
            ->helperText($useCategoryLabels ? null : 'Chooses the form: WO-ICT-01, WO-EEE-01, …');
    }

    /* ------------------------------------------------------------ ০১–০২ */

    protected static function servicesSection(bool $useCategoryLabels): Section
    {
        return Section::make($useCategoryLabels ? 'Subcategories' : 'Consultancy services')
            ->schema([
                CheckboxList::make('details.services')
                    ->label($useCategoryLabels ? 'Subcategories' : 'Services')
                    ->hiddenLabel()
                    ->options(fn (Get $get) => self::sectorText($get('sector'), 'services', []))
                    ->required($useCategoryLabels)
                    ->live($useCategoryLabels)
                    ->afterStateUpdated($useCategoryLabels ? fn ($state, Get $get, Set $set) => self::syncProposalPricing(
                        $get('sector'), (array) $state, $get, $set,
                    ) : null)
                    ->columns(fn (Get $get) => WorkOrder::sector($get('sector'))['service_columns'] ?? 2),
            ]);
    }

    protected static function syncProposalPricing(?string $sector, array $selected, Get $get, Set $set): void
    {
        $slugs = WorkOrder::sector($sector)['categories'] ?? [];
        $categoryIds = ServiceCategory::whereIn('slug', $slugs)->pluck('id', 'slug');
        $categoryId = collect($slugs)->map(fn (string $slug) => $categoryIds->get($slug))->first(fn ($id) => $id !== null);
        $categories = $categoryId === null ? [] : [$categoryId];
        $services = array_intersect_key(self::sectorText($sector, 'services', []), array_fill_keys($selected, true));
        $existing = collect((array) $get('../items'));
        $items = [];

        foreach ($services as $name) {
            $key = $existing->search(fn (array $item) => ($item['service_name'] ?? null) === $name);
            if ($key !== false) {
                $items[$key] = $existing[$key];
                continue;
            }

            $items[(string) Str::uuid()] = [
                'service_name' => $name,
                'service_category_id' => $categoryId,
                'quantity' => 1,
                'discount' => 0,
            ];
        }

        $set('../items', $items);
        $set('../categories', $categories);
        $set('../conditions', SalesConditions::sync((array) $get('../conditions'), $categories, []));
    }

    protected static function revisionField(): TextInput
    {
        return TextInput::make('details.revision')->label('Revision / version')->placeholder('e.g. 01 / 2026')->maxLength(30);
    }

    protected static function revisionRoundsField(): TextInput
    {
        return TextInput::make('details.revisions')
            ->label('Included revision rounds')
            ->numeric()
            ->minValue(0)
            ->visible(fn (Get $get) => collect(WorkOrder::sector($get('sector'))['conditions'] ?? [])
                ->contains(fn ($condition) => str_contains($condition[1], '{revisions}')));
    }

    /* ------------------------------------------------------------ ০৩–০৪ */

    protected static function contractingParties(
        bool $linked = false,
        array $clientFields = [],
        bool|Closure $canEdit = true,
        bool $showDepartment = true,
    ): Section {
        return Section::make('01 Contracting parties')
            ->columns(2)
            ->schema([
                Fieldset::make('First Party (Client)')
                    ->columns(1)
                    ->schema([
                        ...($linked ? [] : [
                            Select::make('client_id')
                                ->label('Client on file (optional)')
                                ->relationship('client', 'name')
                                ->getOptionLabelFromRecordUsing(fn (Client $record) => $record->display_name)
                                ->searchable(['name', 'organization', 'email', 'phone'])
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if ($client = Client::find($state)) {
                                        $set('details.client', self::clientDetails($client));
                                    }
                                })
                                ->helperText('Picking one fills the fields below.'),
                        ]),
                        ...$clientFields,
                        Group::make([
                            TextInput::make('details.client.name')->label('Name / organization')->maxLength(200)
                                ->helperText($linked ? 'Leave blank to use the client from the proposal.' : null),
                            TextInput::make('details.client.contact')->label('Contact person')->maxLength(150),
                            TextInput::make('details.client.phone_email')->label('Phone / email')->maxLength(200),
                            Textarea::make('details.client.address')->label('Address')->rows(2)->maxLength(500),
                        ])->visible($canEdit)
                            ->hidden(fn (Get $get) => $get('client_mode') === 'new'),
                    ]),

                Fieldset::make('Second Party (Service Provider)')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('provider_text')
                            ->hiddenLabel()
                            ->html()
                            ->state(fn (Get $get) => e(config('work_order_admin.provider.name')).'<br>'
                                .e(config('work_order_admin.provider.university'))
                                .($showDepartment ? '<br>Responsible department: <b>'.e(self::sectorText($get('sector'), 'department', '—')).'</b>' : '')),
                        TextInput::make('details.coordinator')->label('Contact / coordinator')->maxLength(150),
                    ])->visible($canEdit),
            ]);
    }

    public static function clientDetails(?Client $client): array
    {
        return [
            'name' => $client?->organization ?: $client?->name,
            'contact' => $client?->name,
            'phone_email' => collect([$client?->phone, $client?->email])->filter()->implode(' / '),
            'address' => $client?->address,
        ];
    }

    protected static function scopeAndInfo(): array
    {
        return [
            Section::make('03 Scope of work')
                ->schema([
                    Textarea::make('details.summary')
                        ->label('Project / problem summary')
                        ->helperText(fn (Get $get) => self::sectorText($get('sector'), 'summary_hint'))
                        ->rows(3)
                        ->maxLength(1500),
                    Textarea::make('details.included')
                        ->label('Included work and scope boundaries')
                        ->helperText('List the sites, tasks, equipment, people, and agreed activities.')
                        ->rows(3)
                        ->maxLength(1500),
                    Textarea::make('details.excluded')
                        ->label('Excluded work / dependencies')
                        ->helperText('List excluded tasks and any materials or approvals the client must provide.')
                        ->rows(3)
                        ->maxLength(1500),
                ]),

            Section::make('04 Service-specific information')
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

        $groupLabels = config("work_order_admin.sectors.{$sector}.info_groups", []);
        $fieldLabels = config("work_order_admin.sectors.{$sector}.info_labels", []);

        return collect($rows)->map(function (array $row, int $index) use ($groupLabels, $fieldLabels) {
            [$label, $parts] = $row;
            $label = $groupLabels[$index] ?? $label;
            $components = [];
            $caption = null;

            foreach ($parts as $part) {
                match ($part[0]) {
                    'text' => $caption = rtrim($part[1], ':'),
                    'field' => $components[] = TextInput::make('details.info.'.$part[1])
                        ->label($fieldLabels[$part[1]] ?? $caption ?? $label)
                        ->maxLength(300)
                        ->columnSpan(in_array($part[2] ?? 'md', ['lg', 'xl'], true) ? 2 : 1),
                    'check' => $components[] = Checkbox::make('details.info.'.$part[1])->label($fieldLabels[$part[1]] ?? $part[2])->inline(),
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
            Section::make('05 Deliverables and acceptance criteria')
                ->description('Preview of the approved Bangla print wording.')
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextEntry::make('deliverables_preview')
                        ->hiddenLabel()
                        ->html()
                        ->state(fn (Get $get) => self::table(
                            ['Deliverable', 'Minimum contractual output', 'Client acceptance criteria'],
                            WorkOrder::sector($get('sector'))['deliverables'] ?? [],
                        )),
                ]),

            Section::make('06 Milestones and schedule')
                ->schema([
                    Group::make(fn (Get $get) => collect(self::sectorText($get('sector'), 'stages', []))
                        ->map(fn (array $stage, int $i) => DatePicker::make("details.stage_dates.{$i}")
                            ->label($stage[0])
                            ->helperText('Verification: '.$stage[1])
                            ->native(false))
                        ->all())
                        ->columns(4),
                    Grid::make(3)->schema([
                        DatePicker::make('details.start_date')->label('Start date')->native(false),
                        DatePicker::make('details.end_date')->label('Estimated completion date')->native(false)->afterOrEqual('details.start_date'),
                        ToggleButtons::make('details.mode')->label('Delivery mode')->options(config('work_order_admin.modes'))->inline(),
                    ]),
                ]),
        ];
    }

    /* ------------------------------------------------------------ ০৭–০৮ */

    protected static function feesSection(): Section
    {
        $fee = fn (int $i, string $label) => Fieldset::make($label)
            ->columns(3)
            ->schema([
                TextInput::make("details.fees.{$i}.qty")->label('Quantity')->numeric()->minValue(0)->placeholder('1')->live(onBlur: true),
                TextInput::make("details.fees.{$i}.unit")->label('Unit')->placeholder('e.g. package / day / page')->maxLength(40),
                TextInput::make("details.fees.{$i}.rate")->label('Rate (BDT)')->numeric()->minValue(0)->prefix('BDT')->live(onBlur: true),
            ]);

        return Section::make('07 Fees and payment approval')
            ->schema([
                ...collect(config('work_order_admin.fee_lines'))->map(fn ($label, $i) => $fee($i, $label))->all(),
                Grid::make(3)->schema([
                    ToggleButtons::make('details.tax_mode')
                        ->label('VAT / tax / withholding')
                        ->options(['add' => 'Add VAT / tax', 'deduct' => 'Deduct withholding tax'])
                        ->default('add')
                        ->inline()
                        ->live(),
                    TextInput::make('details.tax_amount')->label('Amount (BDT)')->numeric()->minValue(0)->prefix('BDT')->live(onBlur: true),
                    TextEntry::make('totals_preview')
                        ->label('Work order total')
                        ->state(fn (Get $get) => self::grandTotal($get))
                        ->weight('bold')
                        ->size('lg'),
                ]),
                TextInput::make('details.amount_words')
                    ->label('Amount in words (BDT)')
                    ->placeholder('Leave blank to generate the Bangla amount in words for printing.')
                    ->maxLength(300),
            ]);
    }

    protected static function installmentsSection(): Section
    {
        return Section::make('Installments and payment terms')
            ->schema([
                Grid::make(3)->schema(collect(config('work_order_admin.payment_steps'))
                    ->map(fn (array $step, int $i) => TextInput::make("details.installments.{$i}.percent")
                        ->label($step['label'])
                        ->helperText('Supporting document: '.$step['proof'])
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%'))
                    ->all()),
                TextInput::make('details.payment_due')
                    ->label('Quotation validity / payment deadline')
                    ->placeholder('e.g. within 7 days of receiving the invoice')
                    ->maxLength(200),
            ]);
    }

    public static function linkedDraftAttributes(Proposal $proposal, array $input): array
    {
        $defaults = self::prefill(null, $proposal->getKey());
        $details = $input['details'] ?? [];

        foreach (['summary', 'included', 'payment_due'] as $field) {
            if (blank($details[$field] ?? null)) {
                unset($details[$field]);
            }
        }

        $installments = array_filter($details['installments'] ?? [], fn (array $entry) => filled($entry['percent'] ?? null));
        $client = array_filter($details['client'] ?? [], filled(...));

        return [
            ...$defaults,
            'sector' => $input['sector'],
            'details' => array_replace($defaults['details'], $details, [
                'installments' => array_replace($defaults['details']['installments'], $installments),
                'client' => array_replace($defaults['details']['client'], $client),
            ]),
            'issue_date' => $proposal->issue_date,
            'created_by' => auth()->id(),
        ];
    }

    /* ------------------------------------------------------------ ০৯–১১ */

    protected static function conditionsAndAttachments(): array
    {
        return [
            Section::make('09 Service-specific conditions')
                ->description('Preview of the approved Bangla print wording.')
                ->collapsible()
                ->schema([
                    TextEntry::make('conditions_preview')
                        ->hiddenLabel()
                        ->html()
                        ->state(fn (Get $get) => collect(WorkOrder::sector($get('sector'))['conditions'] ?? [])
                            ->map(fn (array $c, int $i) => '<p style="margin:0 0 8px"><b>'.sprintf('%02d', $i + 1).' '.e($c[0]).'</b> '.e(str_replace('{revisions}', '____', $c[1])).'</p>')
                            ->implode('') ?: 'Choose the sector first.'),
                ]),

            Section::make('10 General administration and dispute resolution')
                ->description('Preview of the Bangla terms used on every printed form.')
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

            Section::make('11 Attachments and reference documents')
                ->schema([
                    CheckboxList::make('details.attachments')
                        ->hiddenLabel()
                        ->options(fn (Get $get) => self::sectorText($get('sector'), 'attachments', []))
                        ->columns(2),
                    TextInput::make('details.other_attachments')->label('Other attachments / page count')->maxLength(300),
                ]),
        ];
    }

    /* --------------------------------------------------------------- ১২ */

    protected static function approval(): array
    {
        return [
            Section::make('12 Formal acceptance and approval')
                ->columns(2)
                ->schema([
                    Fieldset::make('Authorized client signatory')
                        ->columns(1)
                        ->schema([
                            TextInput::make('details.client_signatory')->label('Name / designation')->maxLength(150),
                            DatePicker::make('details.client_sign_date')->label('Date')->native(false),
                        ]),
                    Fieldset::make('Authorized UGV RICH signatory')
                        ->columns(1)
                        ->schema([
                            TextInput::make('details.rich_signatory')->label('Name / designation')->maxLength(150),
                            DatePicker::make('details.rich_sign_date')->label('Date')->native(false),
                        ]),
                    TextInput::make('details.tech_approval')
                        ->label('Additional technical review / project manager approval (if applicable)')
                        ->maxLength(150),
                    DatePicker::make('details.tech_approval_date')->label('Date')->native(false),
                    CheckboxList::make('details.checklist')
                        ->label('Pre-issue checklist')
                        ->options(config('work_order_admin.checklist'))
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
        return Callout::make('Choose a sector at the top first.')
            ->warning()
            ->visible(fn (Get $get) => blank($get('sector')));
    }

    protected static function sectorText(?string $sector, string $key, mixed $default = null): mixed
    {
        $original = WorkOrder::sector($sector)[$key] ?? $default;
        $english = config("work_order_admin.sectors.{$sector}.{$key}");

        return is_array($original) && is_array($english)
            ? array_replace($original, $english)
            : ($english ?? $original);
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

            return Money::format(max($grand, 0), true).'  (Subtotal '.Money::format($subtotal, true).')';
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
            data_set($data, 'details.client', self::clientDetails($client));
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
