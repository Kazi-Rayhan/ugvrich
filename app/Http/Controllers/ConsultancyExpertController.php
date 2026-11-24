<?php

namespace App\Http\Controllers;

use App\Filament\Resources\ConsultancyApplications\ConsultancyApplicationResource;
use App\Models\ConsultancyApplication as Application;
use App\Models\Expert;
use App\Models\ExpertPortfolio;
use App\Models\ExpertWalletEntry;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ConsultancyReview;
use App\Services\ExpertWallet;
use App\Services\OrderDelivery;
use App\Support\ExpertBiography;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ConsultancyExpertController extends Controller
{
    public const GROUPS = [
        'education' => ['qualification' => 'Degree or qualification', 'institution' => 'Institution', 'year' => 'Completion year'],
        'occupations' => ['title' => 'Occupation or job title', 'institution' => 'Institution or organization', 'start_date' => 'Start date', 'end_date' => 'End date (leave blank if current)'],
        'experience' => ['position' => 'Role or job title', 'organization' => 'Organization', 'period' => 'Period (e.g. 2021 to 2024)'],
        'skills' => ['name' => 'Skill'],
    ];

    public function create()
    {
        return view('consultancy.register', [
            'categories' => ServiceCategory::active()
                ->with(['services' => fn ($query) => $query->active()->orderBy('name')])
                ->orderBy('sort_order')->orderBy('name')->get(),
            'groups' => self::GROUPS,
        ]);
    }

    public function store(Request $request, ConsultancyReview $review)
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($request->input('email')))]);
        }

        $categoryId = $request->integer('service_category_id');

        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:180', 'unique:consultancy_applications,email'],
            'phone' => ['required', 'string', 'max:40'],
            'location' => ['required', 'string', 'max:150'],
            'affiliation_type' => ['required', Rule::in(Application::AFFILIATIONS)],
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072', 'dimensions:max_width=4000,max_height=4000'],
            'service_category_id' => ['required', 'integer', Rule::exists('service_categories', 'id')->where('is_active', true)],
            'expertise_service_ids' => [
                Rule::requiredIf(fn () => Service::active()->where('service_category_id', $categoryId)->exists()),
                'array',
                'min:1',
            ],
            'expertise_service_ids.*' => [
                'bail',
                'required',
                'integer',
                'distinct',
                Rule::exists('services', 'id')->where('service_category_id', $categoryId)->where('is_active', true),
            ],
            'bio' => ['nullable', 'string', 'max:3000'],
            'website' => ['nullable', 'size:0'],
        ];

        foreach (self::GROUPS as $group => $fields) {
            $rules[$group] = $group === 'experience' ? ['sometimes', 'array', 'max:15'] : ['required', 'array', 'min:1', 'max:15'];
            $rules[$group.'.*'] = ['required', 'array:'.implode(',', array_keys($fields))];
            foreach ($fields as $field => $label) {
                $rules[$group.'.*.'.$field] = match ($field) {
                    'year' => ['required', 'integer', 'min:1900', 'max:'.now()->year],
                    'start_date' => ['required', 'date_format:Y-m-d'],
                    'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:occupations.*.start_date'],
                    default => ['required', 'string', 'max:150'],
                };
            }
        }

        $data = $request->validate($rules, [], [
            'expertise_service_ids' => 'areas of expertise',
            'expertise_service_ids.*' => 'area of expertise',
        ]);
        $expertiseIds = $data['expertise_service_ids'] ?? [];
        $photo = $data['profile_photo'];
        unset($data['website'], $data['profile_photo'], $data['expertise_service_ids']);
        $data['occupations'] = array_values($data['occupations']);
        $data['occupation'] = $data['occupations'][0]['title'];

        $photoPath = $photo->store('consultancy-profile-photos', 'local');
        if (! $photoPath) {
            throw new RuntimeException('Could not store the profile photo.');
        }

        try {
            $application = DB::transaction(function () use ($data, $photoPath, $expertiseIds): Application {
                $application = Application::firstOrCreate(['email' => $data['email']], [
                    ...$data,
                    'profile_photo_path' => $photoPath,
                ]);
                if (! $application->wasRecentlyCreated) {
                    throw ValidationException::withMessages(['email' => 'An application already exists for this email address.']);
                }

                $application->expertiseServices()->attach($expertiseIds);

                return $application;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($photoPath);

            throw $exception;
        }

        $review->submitted($application);

        return redirect()->route('consultancy.registration.thanks')->with('application_reference', $application->id);
    }

    public function dashboard(Request $request)
    {
        $application = $this->application($request)->load('category', 'expert');
        $assignments = collect();
        $assignmentCount = 0;
        $taskCounts = array_fill_keys(array_keys(OrderDelivery::TASK_STATUSES), 0);
        $milestones = ['completed' => 0, 'total' => 0];

        if ($application->signed_at) {
            $orders = Order::query()
                ->whereHas('expertAssignments', fn ($query) => $query->where('consultancy_application_id', $application->id))
                ->where('status', '!=', 'cancelled');

            foreach ((clone $orders)->select('id', 'delivery_milestones')->lazyById() as $order) {
                foreach ($order->delivery_milestones ?? [] as $milestone) {
                    $milestones['total']++;
                    $milestones['completed'] += (int) filled($milestone['completed_at'] ?? null);

                    foreach ($milestone['tasks'] ?? [] as $task) {
                        $status = $task['status'] ?? 'todo';
                        if (isset($taskCounts[$status])) {
                            $taskCounts[$status]++;
                        }
                    }
                }
            }

            $assignmentCount = $application->orderAssignments()->count();
            $assignments = $application->orderAssignments()
                ->with(['order:id,number,proposal_id,status,delivery_milestones', 'order.proposal:id,title'])
                ->latest('id')->limit(4)->get()
                ->map(fn ($assignment) => $assignment->order);
        }

        return view('consultancy.dashboard', compact('application', 'assignments', 'assignmentCount', 'taskCounts', 'milestones'));
    }

    public function assignments(Request $request)
    {
        $application = $this->application($request, signed: true)->load('expert');

        return view('consultancy.order-assignments', [
            'application' => $application,
            'assignments' => $application->orderAssignments()->with('order.proposal', 'order.workOrder')->latest('id')->paginate(12),
        ]);
    }

    public function showAssignment(Request $request, Order $order)
    {
        $application = $this->assignedExpert($request, $order);

        return view('consultancy.order-assignment', [
            'application' => $application,
            'order' => $order->load('workOrder', 'proposal', 'items'),
        ]);
    }

    public function assignmentTasks(Request $request, Order $order)
    {
        return view('consultancy.order-tasks', [
            'application' => $this->assignedExpert($request, $order),
            'order' => $order->load('proposal'),
        ]);
    }

    private function assignedExpert(Request $request, Order $order): Application
    {
        $application = $this->application($request, signed: true);
        abort_unless($application->orderAssignments()->where('order_id', $order->id)->exists(), 404);

        return $application;
    }

    public function moveOrderTask(Request $request, Order $order, string $milestone, string $task, OrderDelivery $delivery)
    {
        $expert = $this->application($request, signed: true);
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(OrderDelivery::TASK_STATUSES))]]);
        $progress = $delivery->moveTask($order, $expert, $milestone, $task, $data['status']);

        if ($request->expectsJson()) {
            return response()->json($progress);
        }

        return redirect()->to(route('expert.orders.tasks', $order).'#milestone-'.$milestone)->with('status', 'Task updated.');
    }

    public function onboarding(Request $request)
    {
        $application = $this->application($request);
        if ($application->signed_at) {
            return new RedirectResponse(route('expert.dashboard'));
        }

        return view('consultancy.expert-onboarding', ['application' => $application]);
    }

    public function showProfile(Request $request)
    {
        return view('consultancy.expert-profile', ['application' => $this->application($request, signed: true)->load('category', 'expert')]);
    }

    public function showPortfolio(Request $request)
    {
        return view('consultancy.expert-portfolio', ['application' => $this->application($request, signed: true)->load('expert', 'portfolios')]);
    }

    public function showContract(Request $request)
    {
        return view('consultancy.expert-contract', ['application' => $this->application($request, signed: true)->load('expert')]);
    }

    public function showWallet(Request $request, ExpertWallet $wallet)
    {
        $application = $this->application($request, signed: true);

        return view('consultancy.expert-wallet', [
            'application' => $application,
            'balances' => $wallet->balances($application),
            'entries' => $application->walletEntries()->latest('id')->paginate(15),
        ]);
    }

    public function savePayoutMethod(Request $request, ExpertWallet $wallet)
    {
        $application = $this->application($request, signed: true);
        $selection = $request->validate([
            'method' => ['required', Rule::in(ExpertWallet::METHODS)],
            'destination_id' => ['nullable', 'string', 'max:36'],
        ]);
        $method = $selection['method'];
        foreach ($method === 'bank' ? ['account_number', 'routing_number'] : ['bkash_number'] as $field) {
            if (is_string($number = $request->input($field))) {
                $request->merge([$field => preg_replace('/[\s-]+/u', '', str_replace(
                    ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'], range(0, 9), $number,
                ))]);
            }
        }
        $destination = $request->validate($method === 'bank' ? [
            'bank_name' => ['required', 'string', 'max:120'],
            'branch_name' => ['required', 'string', 'max:120'],
            'district' => ['required', 'string', 'max:120'],
            'account_name' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'string', 'regex:/^[0-9]{6,30}$/'],
            'routing_number' => ['required', 'string', 'regex:/^[0-9]{9}$/'],
        ] : [
            'bkash_number' => ['required', 'string', 'regex:/^(?:\+880|0)1[0-9]{9}$/'],
        ]);
        $wallet->saveDestination($application, $method, $destination, $selection['destination_id'] ?? null);

        $request->session()->flash('status', __('expert_wallet.saved'));

        return new RedirectResponse(route('expert.wallet.methods'));
    }

    public function showPayoutMethods(Request $request, ExpertWallet $wallet)
    {
        $application = $this->application($request, signed: true);

        return view('consultancy.wallet-methods', ['application' => $application, 'destinations' => $wallet->destinations($application)]);
    }

    public function showWithdrawals(Request $request, ExpertWallet $wallet)
    {
        $application = $this->application($request, signed: true);

        return view('consultancy.wallet-withdrawals', [
            'application' => $application,
            'balances' => $wallet->balances($application),
            'destinations' => $wallet->destinations($application),
            'entries' => $application->walletEntries()->where('kind', ExpertWalletEntry::WITHDRAWAL)->latest('id')->paginate(15),
        ]);
    }

    public function requestWithdrawal(Request $request, ExpertWallet $wallet)
    {
        $application = $this->application($request, signed: true);
        $data = $request->validate([
            'method' => ['required', 'string', 'max:36'],
            'amount' => ExpertWallet::AMOUNT_RULES,
            'request_key' => ['required', 'uuid'],
        ]);
        $wallet->withdraw($application, $data['method'], $data['amount'], $data['request_key']);

        $request->session()->flash('status', __('expert_wallet.requested'));

        return new RedirectResponse(route('expert.wallet.withdrawals'));
    }

    public function sign(Request $request)
    {
        $application = $this->application($request);
        abort_if($application->signed_at, 409);
        abort_unless($application->contract_type && $application->engagement_policy, 403, __('consultancy_engagement.waiting'));
        $policyVersion = $application->engagement_policy['version'];
        $reviewItems = $application->engagementReviewItems();

        $data = $request->validate([
            'policy_version' => ['required', Rule::in([$policyVersion])],
            ...array_fill_keys(array_map(fn (string $key) => 'reviewed.'.$key, array_keys($reviewItems)), ['accepted']),
            'terms' => ['accepted'],
            'signature' => ['required', 'string', 'max:180000'],
        ], ['reviewed.*.accepted' => __('consultancy_engagement.review_required')]);

        $png = $this->signaturePng($data['signature']);
        $path = 'expert-signatures/'.Str::uuid().'.png';
        $disk = Storage::disk('local');
        if (! $disk->put($path, $png)) {
            throw new RuntimeException('Could not store the expert signature.');
        }

        $publicPhoto = null;
        try {
            DB::transaction(function () use ($application, $path, $disk, $policyVersion, &$publicPhoto) {
                $application = Application::query()->lockForUpdate()->findOrFail($application->id);
                abort_if($application->signed_at, 409);

                if ($application->profile_photo_path) {
                    $publicPhoto = 'experts/'.basename($application->profile_photo_path);
                    $contents = $disk->get($application->profile_photo_path);
                    if ($contents === null || ! Storage::disk('public')->put($publicPhoto, $contents)) {
                        throw new RuntimeException('Could not publish the expert photo.');
                    }
                }

                $expert = Expert::create([
                    'name' => $application->name,
                    'slug' => (Str::slug($application->name) ?: 'expert').'-consultancy-'.Str::lower(Str::random(10)),
                    'designation' => $application->current_designation ?? $application->occupation,
                    'affiliation_type' => $application->affiliation_type,
                    'current_job' => $application->current_job,
                    'photo' => $publicPhoto,
                    'service_category_id' => $application->service_category_id,
                    'expertise' => array_column($application->skills, 'name'),
                    'professional_skills' => array_column($application->skills, 'name'),
                    'bio' => $application->bio,
                    'is_active' => true,
                ]);

                $application->update([
                    'expert_id' => $expert->id,
                    'signature_path' => $path,
                    'signed_at' => now(),
                    'policy_version' => $policyVersion,
                ]);
            });
        } catch (Throwable $exception) {
            $disk->delete($path);
            if ($publicPhoto) {
                Storage::disk('public')->delete($publicPhoto);
            }

            throw $exception;
        }

        $request->session()->flash('status', __('consultancy_engagement.activated'));

        return new RedirectResponse(route('expert.dashboard'));
    }

    public function update(Request $request)
    {
        $application = $this->application($request, signed: true);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'occupation' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:40'],
            'location' => ['required', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'bio_html' => ['sometimes', 'nullable', 'string', 'max:16000'],
            'bio_html_bn' => ['sometimes', 'nullable', 'string', 'max:16000'],
            'expertise' => ['sometimes', 'nullable', 'string', 'max:4000'],
            'professional_skills' => ['sometimes', 'nullable', 'string', 'max:4000'],
            'department' => ['sometimes', 'nullable', 'string', 'max:150'],
            'affiliation_type' => ['sometimes', 'nullable', Rule::in(Application::AFFILIATIONS)],
            'current_job' => ['sometimes', 'nullable', 'string', 'max:150'],
            'linkedin' => ['sometimes', 'nullable', 'url:http,https', 'max:255'],
        ]);

        $profile = [
            'name' => $data['name'],
            'designation' => $data['occupation'],
            ...array_intersect_key($data, array_flip(['bio', 'department', 'affiliation_type', 'current_job', 'linkedin'])),
        ];
        foreach (['expertise', 'professional_skills'] as $field) {
            if (array_key_exists($field, $data)) {
                $lines = preg_split('/[,，،\r\n]+/u', $data[$field] ?? '');
                $profile[$field] = array_values(array_unique(array_filter(array_map('trim', $lines), 'filled')));
                validator([$field => $profile[$field]], [
                    $field => ['array', 'max:25'],
                    $field.'.*' => ['string', 'max:150'],
                ])->validate();
            }
        }

        foreach (['bio_html' => 'bio', 'bio_html_bn' => 'bio_bn'] as $field => $plainField) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];
                if (filled($value) && $value === strip_tags($value)) {
                    $value = nl2br(e($value));
                }
                $profile[$field] = ExpertBiography::sanitize($value);
                $profile[$plainField] = ExpertBiography::plainText($profile[$field]) ?: null;
            }
        }

        DB::transaction(function () use ($application, $data, $profile) {
            $application->update([
                'phone' => $data['phone'],
                'location' => $data['location'],
            ]);
            $application->expert()->firstOrFail()->update($profile);
        });

        return back()->with('status', __('consultancy_engagement.saved'));
    }

    public function upload(Request $request)
    {
        $application = $this->application($request, signed: true);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'portfolio' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'is_public' => ['sometimes', 'boolean'],
        ]);

        $path = $data['portfolio']->store('expert-portfolios', 'local');
        if (! $path) {
            throw new RuntimeException('Could not store the portfolio file.');
        }

        try {
            $application->portfolios()->create([
                'title' => $data['title'], 'path' => $path, 'is_public' => $data['is_public'] ?? false,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        return back()->with('status', __('consultancy_engagement.uploaded'));
    }

    public function download(Request $request, ExpertPortfolio $portfolio)
    {
        $application = $this->application($request, signed: true);
        abort_unless($portfolio->consultancy_application_id === $application->id, 404);

        return Storage::disk('local')->download($portfolio->path);
    }

    public function updatePortfolioVisibility(Request $request, ExpertPortfolio $portfolio)
    {
        $application = $this->application($request, signed: true);
        abort_unless($portfolio->consultancy_application_id === $application->id, 404);

        $portfolio->update($request->validate(['is_public' => ['required', 'boolean']]));

        return back()->with('status', __('consultancy_engagement.visibility_saved'));
    }

    public function signature(Request $request, Application $application)
    {
        abort_unless($application->signature_path && (
            $application->user_id === $request->user()->id || ConsultancyApplicationResource::canViewAny()
        ), 404);

        return Storage::disk('local')->download($application->signature_path, 'consultancy-signature.png');
    }

    public function reviewPhoto(Application $application)
    {
        abort_unless(ConsultancyApplicationResource::canViewAny() && $application->profile_photo_path
            && Storage::disk('local')->exists($application->profile_photo_path), 404);

        return Storage::disk('local')->response($application->profile_photo_path, headers: [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function application(Request $request, bool $signed = false): Application
    {
        $application = Application::where('user_id', $request->user()->id)->where('status', Application::APPROVED)->firstOrFail();
        abort_if($signed && ! $application->signed_at, 403, 'Complete your onboarding first.');

        return $application;
    }

    private function signaturePng(string $value): string
    {
        $prefix = 'data:image/png;base64,';
        $decoded = str_starts_with($value, $prefix) ? base64_decode(substr($value, strlen($prefix)), true) : false;
        $details = $decoded ? @getimagesizefromstring($decoded) : false;

        if (! $details || $details[2] !== IMAGETYPE_PNG || $details[0] !== 720 || $details[1] !== 240) {
            $this->invalidSignature();
        }

        $image = @imagecreatefromstring($decoded);
        if (! $image) {
            $this->invalidSignature();
        }

        $sample = imagescale($image, 180, 60);
        $colors = imagecolorstotal($sample);
        $ink = 0;
        for ($y = 0; $y < 60; $y++) {
            for ($x = 0; $x < 180; $x++) {
                $pixel = imagecolorat($sample, $x, $y);
                $color = $colors ? imagecolorsforindex($sample, $pixel) : [
                    'alpha' => ($pixel >> 24) & 127,
                    'red' => ($pixel >> 16) & 255,
                    'green' => ($pixel >> 8) & 255,
                    'blue' => $pixel & 255,
                ];
                if ($color['alpha'] < 64 && max($color['red'], $color['green'], $color['blue']) < 180) {
                    $ink++;
                }
            }
        }

        if ($ink < 8 || $ink > 5400) {
            $this->invalidSignature();
        }

        imagesavealpha($image, true);
        ob_start();
        imagepng($image);
        return ob_get_clean();
    }

    private function invalidSignature(): never
    {
        throw ValidationException::withMessages(['signature' => __('consultancy_engagement.signature_error')]);
    }
}
