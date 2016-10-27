<?php

namespace App;

use Exception;

/**
 * Represents the sidebar page in the Memory Alpha database dump. Mainly used
 * to parse the sidebar contents into k/v pairs and strip wiki markup.
 */ 
final class MemoryAlphaSidebar
{
    /**
     * Key used to identify the type of content found in the sidebar. Value must
     * be unique as to avoid being overwritten by a field in the sidebar
     * metadata with the same name as this value.
     *
     * @var string
     */
    const TYPE_KEY = '__type__';

    /**
     * Parse an XML page node, specifically the sidebar section as it
     * contains an easy to parse summary of the page, which is *much*
     * easier than parsing the actual contents of the page.
     *
     * @param string $xml XML node contents
     * @return array Map of data
     * @throws \Exception If the sidebar section could not be found
     */
    public static function parse(&$xml)
    {
        $categories = sprintf('%s', implode('|', MemoryAlphaDatabaseFile::IMPORT_CATEGORIES));
        $regex = sprintf("/\{\{[sS]idebar (?P<type>%s)(?P<fields>.*)\|?\n\}\}/s", $categories);

        // determine type of content based on sidebar type
        if (!preg_match($regex, $xml, $matches)) {
            throw new Exception('Could not parse XML: Could not find sidebar section.');
        }

        $data = [self::TYPE_KEY => $matches['type']];

        // Filter out empty k/v pairs
        $items = array_filter(preg_split("/[\n]/", $matches['fields']), function ($item) {
            return strlen(trim($item)) > 0;
        });

        foreach ($items as $item) {
            // Only grab key value pairs ($field = $value)
            if (strlen($item) == 0 || strpos($item, '=') === false) {
                continue;
            }

            list($field, $value) = explode('=', $item);
            $field = trim($field);
            $value = trim($value);

            if (substr($field, 0, 1) == '|') {
                $field = trim(substr($field, 1, strlen($field)-1));
            }

            // rearrange date fields to YYYY-mm-dd format
            if (stripos($field, 'date') > 0 && strlen($value) == 8) {
                $value = preg_replace('/([0-9]{4})([0-9]{2})([0-9]{2})/', '$1-$2-$3', $value);
            }
            // fields beginning with 'ws' can contain multiple values
            if (substr($field, 0, 2) == 'ws' && strlen($value) > 0) {
                $value = array_filter(array_map('trim', preg_split('/( & | and | &amp; )/', $value)));
            }
            // values can contain HTML comments so strip them
            //$value = preg_replace('/(.*)\s*<!--(.*?)/', '$1', $value);

            $value = self::stripWikiLinks($value);

            // Strip disambiguation links
            // BUG: For some reason this empties `$value` when processing episodes
            if ($matches['type'] == 'species') {
                $value = self::stripDisambiguationLink($value);
            }

            $data[$field] = is_array($value)
                ? array_map('trim', $value)
                : $value;
        }

        return $data;
    }

    /**
     * Strip disambiguation links (ex: `{{dis|value1|value2}})`).
     *
     * @param string $value Value
     * @return string Value stripped of disambiguation link
     */
    private static function stripDisambiguationLink($value)
    {
        return preg_match('/{{dis\|(.*)\|[a-zA-Z0-9\s]+}}+/', $value, $matches)
            ? trim($matches[1])
            : $value;
    }

    /**
     * Strip wiki links (ex: `[[foo]]`).
     *
     * @param string|array $value Value
     * @return string|array Value stripped of wiki links
     */
    private static function stripWikiLinks($value)
    {
        $delimiters = ['[[', ']]'];

        if (is_array($value)) {
            return array_map(function ($item) use ($delimiters) {
                return trim(str_replace($delimiters, '', $item));
            }, $value);
        }

        return trim(str_replace($delimiters, '', $value));
    }
}
