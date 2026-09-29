<?php

namespace App\Support;

/** The Innovation Wing proposal, in the language of the page. */
class InnovationFramework
{
    public static function all(): array
    {
        return LocalizedDocument::load('innovation_framework');
    }
}
