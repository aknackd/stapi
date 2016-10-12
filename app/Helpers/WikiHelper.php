<?php

namespace App\Helpers;

final class WikiHelper
{
    /**
     * Remove wiki links from a string.
     *
     * @param string $text Text
     * @return string Text with wiki links extracted
     * @todo Write tests
     */
    public static function removeWikiLinks($text)
    {
        $text = str_replace(['"', "'"], '', $text);
        $text = str_replace(['{{', '}}'], '', $text);
        $text = str_replace("''", '', $text);

        if ((preg_match_all('/\[\[(.+?)\]\]/u', $text, $matches)) !== false) {
            list($originals, $parts) = $matches;
            foreach ($parts as $idx => $item) {
                if (str_contains($item, '|')) {
                    $parts[$idx] = array_last(explode('|', $item));
                }
            }
            $text = str_replace($originals, $parts, $text);
        }

        // Dumb way to extract the link text by assuming it's the last element
        if (str_contains($text, '|')) {
            $text = array_last(explode('|', $text));
        }

        return $text;
    }
}
