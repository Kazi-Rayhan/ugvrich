<?php

namespace App\Support;

use Illuminate\Support\Facades\Request;

/**
 * Which section of the site the visitor is in.
 *
 * English pages answer under routes named `en.…`, so a pattern written once
 * as `projects.*` has to be matched against both spellings — otherwise the
 * menu highlights nothing on half the site.
 */
class Navigation
{
    public static function isCurrent(string ...$patterns): bool
    {
        $both = [];

        foreach ($patterns as $pattern) {
            $both[] = $pattern;
            $both[] = 'en.'.$pattern;
        }

        return Request::routeIs(...$both);
    }
}
