<?php

namespace App\Services;

use App\Filament\Resources\ConsultancyApplications\ConsultancyApplicationResource;
use App\Models\ConsultancyApplication as Application;
use App\Models\ConsultancyReviewStage;
use App\Models\User;
use App\Notifications\ConsultancyUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConsultancyReview
{
    public static function reviewers(string $stage)
    {
        return ConsultancyReviewStage::where('key', $stage)->sole()->users()->orderBy('name')->get();
    }

    public function submitted(Application $application): void
    {
        $this->notifyStaff($application, Application::WING, 'New consultancy expert application', 'The application is ready for Consultancy Wing review.');
        $this->notifyStaff($application, Application::DIRECTOR, 'New consultancy expert application', 'The application is with the Consultancy Wing. You will receive a separate notification when it is ready for your decision.');
        $this->notifyApplicant($application, 'Consultancy application received', 'Thank you for applying. We will begin reviewing your application as soon as possible and email you when a decision is made.');
    }

    public function decide(Application $record, User $reviewer, bool $approve, ?string $note): void
    {
        DB::transaction(function () use ($record, $reviewer, $approve, $note) {
            $application = Application::query()->lockForUpdate()->findOrFail($record->id);

            if ($application->status !== $record->status || ! $application->canReview($reviewer)) {
                throw ValidationException::withMessages(['note' => 'This application has already moved on or is assigned to another approval stage.']);
            }

            $stage = $application->status;
            $application->decisions = [...($application->decisions ?? []), [
                'stage' => $stage,
                'user_id' => $reviewer->id,
                'reviewer' => $reviewer->name,
                'decision' => $approve ? 'Approved' : 'Declined',
                'note' => $note,
                'at' => now()->toDateTimeString(),
            ]];

            if (! $approve) {
                $application->update(['status' => Application::DECLINED]);
                $this->notifyApplicant($application, 'Consultancy application declined', 'Your application was declined.'.($note ? ' '.$note : ''));

                return;
            }

            if ($stage === Application::WING) {
                $application->update(['status' => Application::DIRECTOR]);
                $this->notifyStaff($application, Application::DIRECTOR, 'Consultancy application ready for Director review', 'The Consultancy Wing approved this application. Please review it and record your decision.');

                return;
            }

            $user = User::firstOrCreate(['email' => $application->email], [
                'name' => $application->name,
                'password' => Str::random(64),
                'role' => User::EXPERT,
            ]);

            $application->update([
                'status' => Application::APPROVED,
                'user_id' => $user->id,
            ]);

            $newAccount = $user->wasRecentlyCreated;
            $url = $newAccount
                ? route('password.reset', ['token' => Password::broker()->createToken($user), 'email' => $user->email])
                : route('expert.dashboard');

            $this->notifyApplicant(
                $application,
                'Consultancy application approved',
                'Your application was approved. We will email you when your engagement is ready. '.($newAccount
                    ? 'Use your application email to sign in after setting a password. If this link expires, request a new one from the sign-in page.'
                    : 'Sign in with your existing account and open the expert dashboard.'),
                $url,
                $newAccount ? 'Set your password' : 'Sign in',
            );
        });
    }

    private function notifyStaff(Application $application, string $stage, string $title, string $message): void
    {
        Notification::send(self::reviewers($stage), new ConsultancyUpdate(
            $title,
            'Application #'.$application->id.'. '.$message,
            ConsultancyApplicationResource::getUrl('view', ['record' => $application], panel: 'admin'),
            'Open application',
            staff: true,
        ));
    }

    private function notifyApplicant(Application $application, string $title, string $message, ?string $url = null, string $action = 'Visit UGV RICH'): void
    {
        Notification::route('mail', $application->email)->notify(new ConsultancyUpdate(
            $title, $message, $url ?? route('login'), $action,
        ));
    }
}
