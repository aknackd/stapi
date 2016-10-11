<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasAppModelTrait;

class Species extends Model
{
    use HasAppModelTrait;

    protected $fillable = ['name', 'type', 'quadrants', 'planets', 'population'];

    public function getQuadrantsAttribute($value)
    {
        return json_decode($value);
    }

    public function getPlanetsAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * Import a species from a Memory Alpha database dump record. Used in the
     * `stapi:import` artisan command.
     *
     * @param array $data Data
     * @return \App\Models\Species Created species instance
     */
    public static function import(array $data = [])
    {
        // A value can contain multiples separated by a line break (`<br />`) or a pipe
        $splitFn = function ($string) { return preg_split('/(<br \/>|\/)/', $string); };

        // 1) Parse and determine quadrants and planets the species may come from and/or occupy
        $quadrants = isset($data['quadrant']) ? $splitFn($data['quadrant']) : [];
        $planets   = isset($data['planet'])   ? $splitFn($data['planet'])   : [];
        
        // Species may occupy multiple quadrants (ex: Voth)
        if (isset($data['quadrant']) && isset($data['quadrant2'])) {
            $quadrants[] = $splitFn($data['quadrant2']);
        }
        // Species could also belong to more than one planet (ex: Voth)
        if (isset($data['planet']) && isset($data['planet2'])) {
            $planets[] = $splitFn($data['planet2']);
        }
        
        $quadrants = array_flatten($quadrants);
        $planets   = array_flatten($planets);

        // 2) Extract planet name from a wiki link, if present (ex: `[[Vulcan]]`)
        foreach ($planets as $idx => $planet) {
            if (str_contains($planet, '|')) {
                $planet = array_first(explode('|', $planet));
                // Anchor link likely has the proper planet (ex: "Unamed planet#Species 2161 Homeworld")
                if (str_contains($planet, '#')) {
                    $planet = array_last(explode('#', $planet));
                }
            }
            $planets[$idx] = $planet;
        }

        // 3) Filter out non-Milky Way galaxy quadrants
        $quadrants = collect($quadrants)->map(function ($value, $key) {
            return trim(str_ireplace('quadrant', '', $value));
        })->filter(function ($value, $key) {
            return str_contains(strtolower($value), ['alpha', 'beta', 'gamma', 'delta']);
        })->toArray();

        // 4) If `type` is a wiki link, get the proper name
        if (isset($data['type'])) {
            if (str_contains($data['type'], '|')) {
                $data['type'] = array_last(explode('|', $data['type']));
            } 
            $data['type'] = strtolower($data['type']);
        }

        // 5) Insert
        return Species::create([
            'name'       => $data['title'],
            'type'       => isset($data['type']) ? $data['type'] : null,
            'quadrants'  => json_encode($quadrants),
            'planets'    => json_encode($planets),
            'population' => isset($data['pop']) ? $data['pop'] : null,
        ]);
    }
}
