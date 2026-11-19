<?php

namespace App\Helpers;

/**
 * HTML allowlist sanitization for rich text (e.g. Summernote).
 */
class HtmlSanitizer
{
    private const ALLOWED_TAGS = '<p><br><b><i><u><strong><em><ul><ol><li><a><img><h1><h2><h3><h4><h5><h6><span><div><blockquote><table><thead><tbody><tr><th><td><hr>';

    public static function richText(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $clean = strip_tags($html, self::ALLOWED_TAGS);

        // Drop event handlers and javascript: URLs.
        $clean = preg_replace('/\s(on[a-z]+)\s*=\s*(["\']).*?\2/iu', '', $clean) ?? $clean;
        $clean = preg_replace('/\s(on[a-z]+)\s*=\s*[^\s>]*/iu', '', $clean) ?? $clean;
        $clean = preg_replace('/\s(javascript|vbscript|data)\s*:/iu', '', $clean) ?? $clean;

        // Strip dangerous attributes from remaining tags.
        $clean = preg_replace_callback(
            '/<(a|img|span|div|p|table|td|th|tr)\b([^>]*)>/iu',
            static function (array $matches): string {
                $tag = strtolower($matches[1]);
                $attrs = $matches[2];
                $allowed = [];

                if ($tag === 'a' && preg_match('/\shref\s*=\s*(["\'])(.*?)\1/iu', $attrs, $m)) {
                    $href = trim($m[2]);
                    if (preg_match('#^(https?:)?//|^/|^mailto:#iu', $href) && ! preg_match('#^(javascript|data|vbscript):#iu', $href)) {
                        $allowed[] = 'href="'.e($href, false).'"';
                        $allowed[] = 'rel="noopener noreferrer"';
                        $allowed[] = 'target="_blank"';
                    }
                }

                if ($tag === 'img' && preg_match('/\ssrc\s*=\s*(["\'])(.*?)\1/iu', $attrs, $m)) {
                    $src = trim($m[2]);
                    if (preg_match('#^(https?:)?//|^/#iu', $src) && ! preg_match('#^(javascript|data|vbscript):#iu', $src)) {
                        $allowed[] = 'src="'.e($src, false).'"';
                        $allowed[] = 'alt=""';
                    }
                }

                if ($allowed === []) {
                    return '<'.$tag.'>';
                }

                return '<'.$tag.' '.implode(' ', $allowed).'>';
            },
            $clean
        ) ?? $clean;

        return $clean;
    }
}
