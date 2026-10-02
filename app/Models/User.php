<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'email_notifications'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'email_notifications' => 'boolean',
        ];
    }

    /** Staff, who run the site from the admin panel. */
    public const ADMIN = 'admin';

    /** Researchers, who register themselves and use the Researcher Portal. */
    public const RESEARCHER = 'researcher';

    /**
     * Researchers register themselves, so this can no longer let everybody in.
     *
     * Until the portal existed every row in `users` was a staff account and
     * returning true was safe. It is not any more: a researcher who signed up
     * on the public site would have walked straight into /admin.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ADMIN;
    }

    public function isResearcher(): bool
    {
        return $this->role === self::RESEARCHER;
    }

    /** One row per researcher, holding what `users` does not. */
    public function researcherProfile(): HasOne
    {
        return $this->hasOne(ResearcherProfile::class);
    }

    /** The profile row, made on demand so the portal never has to check. */
    public function profile(): ResearcherProfile
    {
        return $this->researcherProfile()->firstOrCreate([]);
    }

    public function researchIdeas(): HasMany
    {
        return $this->hasMany(ResearchIdea::class)->latest();
    }

    public function researchProposals(): HasMany
    {
        return $this->hasMany(ResearchProposal::class)->latest();
    }

    /** Requests this user has sent the support desk from the portal. */
    public function researchSupports(): HasMany
    {
        return $this->hasMany(ResearchSupport::class)->latest();
    }

    /** Research projects this user is the researcher on. */
    public function researchProjects(): HasMany
    {
        return $this->hasMany(Project::class)->latest();
    }
}
