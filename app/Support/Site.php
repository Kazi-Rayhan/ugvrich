<?php

namespace App\Support;

use App\Models\InnovationArea;
use App\Models\ServiceCategory;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Read-side facade over the settings table plus the handful of lookups
 * every page of the public site needs (nav categories, contact block).
 */
class Site
{
    protected ?Collection $navCategories = null;

    protected ?Collection $innovationAreas = null;

    public function get(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }

    /** Decode a JSON-valued setting into an array. */
    public function list(string $key): array
    {
        $raw = Setting::get($key);

        if (blank($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function name(): string
    {
        return (string) $this->get('site_name', 'UGV RICH');
    }

    public function tagline(): string
    {
        return (string) $this->get('site_tagline', 'Research, Innovation & Consultancy Hub');
    }

    /** Memoised per request — models are never put in the cache store. */
    public function navCategories(): Collection
    {
        return $this->navCategories ??= ServiceCategory::active()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'name_bn', 'slug', 'icon', 'tagline', 'tagline_bn']);
    }

    /** The Innovation Wing's department areas, for the menu. Memoised per request. */
    public function innovationAreas(): Collection
    {
        return $this->innovationAreas ??= InnovationArea::active()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'name_bn', 'slug', 'icon', 'department', 'tagline', 'tagline_bn', 'description', 'description_bn']);
    }

    public function socials(): array
    {
        return collect([
            'facebook' => $this->get('social_facebook'),
            'linkedin' => $this->get('social_linkedin'),
            'x-social' => $this->get('social_x'),
            'youtube' => $this->get('social_youtube'),
        ])->filter()->all();
    }

    /**
     * Clear the cached settings and drop the resolved instance, so anything
     * still holding the container's copy (queue workers, Octane, a request
     * that saves and then renders) picks up the new values.
     */
    public static function flush(): void
    {
        Cache::forget('settings.all');

        app()->forgetInstance(self::class);
    }
}
