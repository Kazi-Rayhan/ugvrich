<?php

namespace App\Support;

/** The Research Wing framework document, in the language of the page. */
class ResearchFramework
{
    public static function all(): array
    {
        return LocalizedDocument::load('research_framework');
    }
}
