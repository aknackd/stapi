<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasAppModelTrait;
use App\Helpers\WikiHelper;

class Starship extends Model
{
    use HasAppModelTrait;

    protected $fillable = [
        'name', 'class', 'registry_number', 'owners', 'operators', 'status', 'status_at'
    ];

    public function getOwnersAttribute($value)
    {
        return json_decode($value);
    }

    public function getOperatorsAttribute($value)
    {
        return json_decode($value);
    }

    public function getStatusAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * Import a starship from a Memory Alpha database dump record. Used in the
     * `stapi:import` artisan command.
     *
     * @param array $data Data
     * @return \App\Models\Starship Created starship instance
     */
    public static function import(array $data = [])
    {
        $data['status'] = isset($data['Status']) ? $data['Status'] : '';

        $insertData = [
            'name'            => isset($data['name'])       ? $data['name']       : null,
            'class'           => isset($data['Class'])      ? $data['Class']      : null,
            'registry_number' => isset($data['Registry'])   ? $data['Registry']   : null,
            'status_at'       => isset($data['Datestatus']) ? $data['Datestatus'] : null, 
        ];

        // Handle fields that could have multiple values
        foreach (['owner', 'operator', 'status'] as $key) {
            $insertData[$key] = [];
            if (isset($data[$key])) {
                $parts = preg_split('/<br\s?\/?>/', $data[$key]);
                $insertData[$key] = collect($parts)->map(function ($item, $key) {
                    return trim(strip_tags(WikiHelper::removeWikiLinks($item)));
                })->toArray();
            }
        }

        // Strip registry number from name if present
        if (!isset($data['name'])) {
            $insertData['name'] = $insertData['registry_number'] === null
                ? $data['title']
                : str_replace("({$data['Registry']})", '', $data['title']);
        }

        // Strip out any wiki links and HTML tags
        foreach ($insertData as $key => $value) {
            if (!is_array($value)) {
                $insertData[$key] = trim(strip_tags(WikiHelper::removeWikiLinks($value)));
            }
        }

       return self::create([
            'name'            => $insertData['name'],
            'class'           => $insertData['class'],
            'registry_number' => $insertData['registry_number'],
            'owners'          => json_encode($insertData['owner']),
            'operators'       => json_encode($insertData['operator']),
            'status'          => json_encode($insertData['status']), 
            'status_at'       => $insertData['status_at'],
        ]);
    }
}
