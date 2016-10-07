<?php

namespace App\Models\Traits;

use DB;
use Schema;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait HasAppModelTrait
{
    /**
     * Constructor
     *
     * @param array $attributes Attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        parent::boot();

        // Hide fields from responses
        array_push($this->hidden, 'pivot');
        array_push($this->hidden, 'created_at');
        array_push($this->hidden, 'updated_at');

        // By default table names are snake case and plural of the model's
        // name (ex: Person -> people, AccountStatus -> account_statuses)
        $this->table = str_plural(
            strtolower(snake_case(class_basename(get_called_class())))
        );
    }

    /**
     * Retrieve the table name used by the model.
     *
     * @return string Table name
     */
    public static function table()
    {
        return with(new static)->table;
    }

    /**
     * Find a record by a field. If record annot be found then throw an
     * exception.
     *
     * @param string $field   Field
     * @param mixed  $value   Value
     * @param array  $columns Columns to return in the result
     * @return array|mixed Search result
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If record cannot be found
     */
    public static function findByFieldOrFail($field, $value, $columns = ['*'])
    {
        $instance = new self;
        $result = $instance->where($field, $value)
            ->select($columns)
            ->get();

        if (is_null($result) || $result->count() == 0) {
            self::throwNotFoundException();
        }

        return $result;
    }

    /**
     * Creates a new record from a data array. Doesn't insert record into the
     * database (must manually be done via `create()`).
     *
     * @param array $data Data
     * @return mixed Instance of model
     * @throws \InvalidArgumentException If an invalid field is specified in the data array
     */
    public static function createFromArray(array $data)
    {
        $instance = new self;
        $table = $instance::table();

        foreach ($data as $field => $value) {
            if (!Schema::hasColumn($table, $field)) {
                throw new InvalidArgumentException('Invalid field: '.$field);
            }

            $instance->$field = $value;
        }

        return $instance;
    }

    /**
     * Inserts a new record given a data array.
     *
     * @param array $data Data
     * @return mixed Instance of newly created model
     */
    public static function insertFromArray(array $data)
    {
        return self::createFromArray($data)->create();
    }

    /**
     * Inserts many records into the database.
     *
     * @param array $items Items
     * @return
     */
    public static function insertMany(array $items)
    {
        $now = Carbon::now();
        $instance = new self;
        $table = $instance::table();

        $timestamps = [];
        if (Schema::hasColumn($table, 'created_at')) {
            $timestamps['created_at'] = $now;
        }
        if (Schema::hasColumn($table, 'updated_at')) {
            $timestamps['updated_at'] = $now;
        }

        // Inject timestamps if their columns are present in the table schema
        if (count($timestamps) > 0) {
            $items = collect($items)->map(function ($data) use ($timestamps, $table) {
                return array_merge($timestamps, $data);
            })->all();
        }

        return DB::table($table)->insert($items);
    }

    /**
     * Helper method to throw a model not found exception.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function throwNotFoundException()
    {
        throw (new ModelNotFoundException)->setModel(get_called_class());
    }
}
