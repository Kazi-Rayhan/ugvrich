<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bangla is the site's own language and lives at the root; English is the
 * same site one segment deeper, under /en. The first path segment decides
 * which, and LocalizedUrlGenerator keeps every route() call in that language.
 */
class SetLocale
{
    public const LOCALES = ['bn' => 'বাংলা', 'en' => 'English'];

    public const DEFAULT = 'bn';

    /** Where the Researcher Portal keeps the language a person chose. */
    public const SESSION_KEY = 'portal_locale';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->segment(1) === 'en' ? 'en' : self::DEFAULT;

        /* The portal is a signed-in application and is registered once, not
           mirrored under /en, so it has no locale segment to read. It carries
           the choice in the session instead. The public site is untouched:
           there the URL decides, and it still does. */
        if ($request->is('researcher', 'researcher/*')) {
            $chosen = $request->session()->get(self::SESSION_KEY);

            if (isset(self::LOCALES[$chosen])) {
                $locale = $chosen;
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
