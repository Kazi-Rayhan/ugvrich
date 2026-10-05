<?php

namespace App\Models;

use App\Helpers\PolicyHelper;
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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'password', 'role', 'role_id', 'email_notifications'])]
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
     * Innovators: an account is made for whoever submits an idea on the public
     * form, and they follow their ideas from the innovator dashboard.
     */
    public const INNOVATOR = 'innovator';

    /**
     * Researchers register themselves, so this can no longer let everybody in.
     *
     * Until the portal existed every row in `users` was a staff account and
     * returning true was safe. It is not any more: a researcher who signed up
     * on the public site would have walked straight into /admin.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        /* Staff by the old column, or anybody a role has been written for that
           includes the permission to come in. The second is how a reviewer who
           is not an administrator gets a way in. */
        return $this->role === self::ADMIN
            || $this->isAdmin()
            || $this->hasPermission(self::ACCESS_PANEL);
    }

    public function isAdmin(): bool
    {
        if ($this->primaryRole?->name === Role::ADMIN) {
            return true;
        }

        return $this->relationLoaded('roles')
            ? $this->roles->contains('name', Role::ADMIN)
            : $this->roles()->where('name', Role::ADMIN)->exists();
    }

    public function isResearcher(): bool
    {
        return $this->role === self::RESEARCHER;
    }

    public function isInnovator(): bool
    {
        return $this->role === self::INNOVATOR;
    }

    /** The ideas submitted from this innovator account, newest first. */
    public function ideaSubmissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(IdeaSubmission::class)->latest();
    }

    /** The permission that opens the admin panel at all. */
    public const ACCESS_PANEL = 'access_admin_panel';

    /* ------------------------------------------------- roles and permissions */

    /**
     * The primary role, as a row.
     *
     * Note the existing `role` string column is untouched and still decides
     * admin from researcher across the site; this is the finer grained layer
     * sitting on top of it.
     */
    public function primaryRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /** Any further roles, beyond the primary one. */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $name): bool
    {
        if ($this->primaryRole?->name === $name) {
            return true;
        }

        return $this->relationLoaded('roles')
            ? $this->roles->contains('name', $name)
            : $this->roles()->where('name', $name)->exists();
    }

    /**
     * Permission names from every role this user carries, worked out once per
     * instance: a page asks this question dozens of times.
     *
     * @var array<string, true>|null
     */
    protected ?array $permissionLookup = null;

    public function hasPermission(string $permission): bool
    {
        $this->permissionLookup ??= array_fill_keys($this->permissionNames()->all(), true);

        return isset($this->permissionLookup[$permission]);
    }

    /** @return Collection<int, string> */
    public function permissionNames(): Collection
    {
        $roles = collect();

        if ($this->primaryRole) {
            $this->primaryRole->loadMissing('permissions');
            $roles->push($this->primaryRole);
        }

        if ($this->relationLoaded('roles')) {
            $this->roles->loadMissing('permissions');
            $roles = $roles->merge($this->roles);
        } else {
            $roles = $roles->merge($this->roles()->with('permissions')->get());
        }

        return $roles
            ->unique('id')
            ->flatMap(fn (Role $role) => $role->permissions->pluck('name'))
            ->unique()
            ->values();
    }

    /**
     * May this user do `$action` to `$model`?
     *
     * The question the policies ask. An administrator may do anything; anybody
     * else needs the permission by name. Nothing here looks at who owns the
     * record — that is the policy's own business.
     */
    public function allowedTo(string $action, string|object $model): bool
    {
        return $this->isAdmin() || $this->hasPermission(PolicyHelper::name($action, $model));
    }

    /** Forget what was worked out, after roles have been changed. */
    public function forgetPermissions(): void
    {
        $this->permissionLookup = null;
        $this->unsetRelation('roles');
        $this->unsetRelation('primaryRole');
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
