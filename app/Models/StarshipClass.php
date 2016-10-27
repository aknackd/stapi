<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasAppModelTrait;
use App\Helpers\WikiHelper;

class StarshipClass extends Model
{
    use HasAppModelTrait;

    protected $fillable = [
        'name', 'owner', 'operator', 'active_during', 'affiliation',
        'type', 'length', 'mass', 'speed', 'decks', 'armament', 'defenses',
        'crew',
    ];

    /**
     * Import a starship class from a Memory Alpha database dump record. Used
     * in the `stapi:import` artisan command.
     *
     * @param array $data Data
     * @return \App\Models\StarshipClass Created starship class instance
     * @todo `Armament` isn't getting parsed - see App\MemoryAlphaSidebar::parse()
     */
    public static function import(array $data = [])
    {
        $data['Name'] = trim(str_replace(' class', '', $data['title']));

        // Handle fields that could have multiple values
        $keys = ['owner', 'operator', 'Affiliation', 'Defenses', 'Armament', 'Speed', 'Crew'];
        foreach ($keys as $key) {
            $insertData[$key] = [];
            if (isset($data[$key])) {
                $parts = preg_split('/(<br\s?\/?>|,\s)/', $data[$key]);
                $value = collect($parts)->map(function ($item, $key) {
                    return trim(strip_tags(WikiHelper::removeWikiLinks($item)));
                })->toArray();
                $data[$key] = $value;
            }
        }

        if (isset($data['Decks'])) {
            $data['Decks'] = trim(preg_replace('/<!--(.*?)-->/', '', $data['Decks']));
        }

        return self::create([
            'name'          => $data['Name'],
            'owner'         => isset($data['owner'])       ? json_encode($data['owner']) : null,
            'operator'      => isset($data['operator'])    ? json_encode($data['operator']) : null,
            'active_during' => isset($data['Active'])      ? WikiHelper::removeWikiLinks($data['Active']) : null,
            'affiliation'   => isset($data['Affiliation']) ? json_encode($data['Affiliation']) : null,
            'length'        => isset($data['Length'])      ? WikiHelper::removeWikiLinks($data['Length']) : null,
            'mass'          => isset($data['Mass'])        ? WikiHelper::removeWikiLinks($data['Mass']) : null,
            'speed'         => isset($data['Speed'])       ? json_encode($data['Speed']) : null,
            'decks'         => isset($data['Decks'])       ? WikiHelper::removeWikiLinks($data['Decks']) : null,
            'armaments'     => isset($data['Armament'])    ? json_encode($data['Armament']) : null,
            'defenses'      => isset($data['Defenses'])    ? json_encode($data['Defenses']) : null,
            'crew'          => isset($data['Crew'])        ? json_encode($data['Crew']) : null,
        ]);
    }
}
