<?php

namespace App\Services;

use App\Models\ConsultancyApplication as Application;
use App\Models\ConsultancyEngagementPolicy;
use App\Models\User;
use App\Notifications\ConsultancyUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ConsultancyEngagement
{
    public static function canAssign(User $user): bool
    {
        return $user->can('assignEngagement', Application::class);
    }

    public function assign(Application $record, User $assigner, string $contract, ?string $percentage): void
    {
        abort_unless(self::canAssign($assigner), 403);
        validator(['contract' => $contract, 'percentage' => $percentage], [
            'contract' => ['required', Rule::in(array_keys(Application::CONTRACTS))],
            'percentage' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,2'],
        ])->validate();

        DB::transaction(function () use ($record, $assigner, $contract, $percentage): void {
            $application = Application::query()->lockForUpdate()->findOrFail($record->id);
            if ($application->status !== Application::APPROVED || $application->contract_type !== null || $application->signed_at) {
                throw ValidationException::withMessages(['contract' => __('consultancy_engagement.only_pending')]);
            }

            $policy = ConsultancyEngagementPolicy::where('contract_type', $contract)->sole();
            $application->update([
                'contract_type' => $contract,
                'engagement_policy' => $policy->only(['version', ...ConsultancyEngagementPolicy::CONTENT_FIELDS]),
                'engagement_percentage' => $percentage,
                'engagement_assigned_at' => now(),
                'engagement_assigned_by' => $assigner->id,
            ]);

            Notification::route('mail', $application->email)->notify(new ConsultancyUpdate(
                __('consultancy_engagement.notification_subject', locale: 'en').' / '.__('consultancy_engagement.notification_subject', locale: 'bn'),
                __('consultancy_engagement.notification_body', ['contract' => Application::contractLabel($contract, 'en')], 'en')
                    ."\n\n".__('consultancy_engagement.notification_body', ['contract' => Application::contractLabel($contract, 'bn')], 'bn'),
                route('expert.dashboard'),
                __('consultancy_engagement.notification_link', locale: 'en').' / '.__('consultancy_engagement.notification_link', locale: 'bn'),
            ));
        });
    }
}
