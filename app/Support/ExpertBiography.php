<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class ExpertBiography
{
    public static function sanitize(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $config = new HtmlSanitizerConfig;
        foreach (['p', 'div', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'blockquote', 'h3', 'h4'] as $tag) {
            $config = $config->allowElement($tag);
        }

        $html = (new HtmlSanitizer($config))->sanitize($value);

        return filled(self::plainText($html)) ? $html : null;
    }

    public static function plainText(?string $html): string
    {
        $blocks = preg_replace('~<(?:br\b[^>]*|/(?:p|div|li|blockquote|h3|h4))>~i', "\n", $html ?? '');

        return trim(str_replace("\u{00A0}", ' ', html_entity_decode(strip_tags($blocks), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}
