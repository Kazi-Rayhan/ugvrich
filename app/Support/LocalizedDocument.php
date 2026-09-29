<?php

namespace App\Support;

/**
 * A long-form document the site renders, read in the language of the page.
 *
 * config/{name}.php holds the English document and defines the shape.
 * lang/{locale}/{name}.php carries the same keys in another language; anything
 * it leaves out keeps its English wording, so a page is never half-empty while
 * a translation is being written.
 */
class LocalizedDocument
{
    public static function load(string $name): array
    {
        $document = config($name, []);

        if (app()->getLocale() === 'en') {
            return $document;
        }

        $translated = trans($name);

        return is_array($translated) ? static::merge($document, $translated) : $document;
    }

    /** Translated values win; untranslated branches keep the original. */
    protected static function merge(array $original, array $translated): array
    {
        foreach ($original as $key => $value) {
            if (! array_key_exists($key, $translated)) {
                continue;
            }

            $original[$key] = is_array($value) && is_array($translated[$key])
                ? static::merge($value, $translated[$key])
                : (blank($translated[$key]) ? $value : $translated[$key]);
        }

        return $original;
    }
}
