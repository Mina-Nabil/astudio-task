<?php

namespace App\Models;

use App\Models\EAV\Attribute;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Job extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    const STATUS_LIST = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_ARCHIVED,
    ];

    const JOB_TYPE_FULL_TIME = 'full-time';
    const JOB_TYPE_PART_TIME = 'part-time';
    const JOB_TYPE_CONTRACT = 'contract';
    const JOB_TYPE_FREELANCE = 'freelance';

    const JOB_TYPE_LIST = [
        self::JOB_TYPE_FULL_TIME,
        self::JOB_TYPE_PART_TIME,
        self::JOB_TYPE_CONTRACT,
        self::JOB_TYPE_FREELANCE,
    ];

    protected $table = 'jobs';

    protected $fillable = [
        'title',
        'description',
        'company_name',
        'salary_min',
        'salary_max',
        'job_type',
        'status',
        'published_at',
        'is_remote',
    ];

    const RELATIONS = [
        'categories',
        'locations',
        'languages',
    ];

    /**
     * Create a new job from a StoreJobRequest
     *
     * @param \App\Http\Requests\StoreJobRequest $request
     * @return Job
     */
    public static function createFromRequest($request)
    {
        Log::info(json_encode($request->all()));
        // Start a database transaction to ensure all related data is saved correctly
        return DB::transaction(function () use ($request) {
            // Create new job with basic fields
            $job = self::create([
                'title' => $request->title,
                'description' => $request->description,
                'company_name' => $request->company_name,
                'salary_min' => $request->salary_min,
                'salary_max' => $request->salary_max,
                'is_remote' => $request->is_remote,
                'job_type' => $request->job_type,
                'status' => $request->status,
                'published_at' => $request->published_at ?? Carbon::now(),
            ]);

            // Attach categories if provided
            if ($request->has('categories') && is_array($request->categories)) {
                $job->categories()->attach($request->categories);
            }

            // Attach locations if provided
            if ($request->has('locations') && is_array($request->locations)) {
                $job->locations()->attach($request->locations);
            }

            // Attach languages if provided
            if ($request->has('languages') && is_array($request->languages)) {
                $job->languages()->attach($request->languages);
            }

            // Attach attributes if provided - using array instead of object due to super attributes property
            if ($request->has('attributes') && is_array($request['attributes'])) {
                foreach ($request['attributes'] as $attribute) {
                    $job->attributes()->attach($attribute['id'], [
                        'value' => $attribute['value']
                    ]);
                }
            }

            // Load relationships and return the job
            return $job->load(['categories', 'locations', 'languages', 'attributes']);
        });
    }

    /**
     * Update an existing job from a StoreJobRequest
     *
     * @param \App\Http\Requests\StoreJobRequest $request
     * @return Job
     */
    public function updateFromRequest($request)
    {
        // Start a database transaction to ensure all related data is updated correctly
        return DB::transaction(function () use ($request) {
            // Update job with basic fields
            $this->update([
                'title' => $request->title,
                'description' => $request->description,
                'company_name' => $request->company_name,
                'salary_min' => $request->salary_min,
                'salary_max' => $request->salary_max,
                'is_remote' => $request->is_remote,
                'job_type' => $request->job_type,
                'status' => $request->status,
                'published_at' => $request->published_at ?? Carbon::now(),
            ]);

            // Update categories if provided
            if ($request->has('categories')) {
                $this->categories()->sync($request->categories);
            }

            // Update locations if provided
            if ($request->has('locations')) {
                $this->locations()->sync($request->locations);
            }

            // Update languages if provided
            if ($request->has('languages')) {
                $this->languages()->sync($request->languages);
            }

            // Update attributes if provided
            if ($request->has('attributes') && is_array($request['attributes'])) {
                // Get existing attribute IDs to determine which ones to detach
                $existingAttributeIds = $this->attributes()->pluck('attributes.id')->toArray();
                $newAttributeIds = array_column($request['attributes'], 'id');

                // Attribute IDs to detach (in existing but not in new)
                $attributeIdsToDetach = array_diff($existingAttributeIds, $newAttributeIds);
                
                // Detach attributes that are no longer needed
                if (!empty($attributeIdsToDetach)) {
                    $this->attributes()->detach($attributeIdsToDetach);
                }

                // Update or attach new attributes
                foreach ($request['attributes'] as $attribute) {
                    // Check if the attribute is already attached
                    $existingAttribute = $this->attributes()
                        ->where('attributes.id', $attribute['id'])
                        ->first();

                    if ($existingAttribute) {
                        // Update existing attribute value
                        $this->attributes()->updateExistingPivot($attribute['id'], [
                            'value' => $attribute['value']
                        ]);
                    } else {
                        // Attach new attribute
                        $this->attributes()->attach($attribute['id'], [
                            'value' => $attribute['value']
                        ]);
                    }
                }
            }

            // Refresh the model with relationships
            $this->refresh();
            $this->load(['categories', 'locations', 'languages', 'attributes']);

            return $this;
        });
    }

    ///model scopes
    public function scopeWithRelations(Builder $query): Builder
    {
        return $query->with(self::RELATIONS);
    }

    public function scopeWithJobAttributes(Builder $query): Builder
    {
        return $query->with('attributes');
    }

    public function scopeJoinLanguages(Builder $query): Builder
    {
        return $query->join('job_language', 'jobs.id', '=', 'job_language.job_id')
            ->join('languages', 'job_language.language_id', '=', 'languages.id');
    }

    public function scopeJoinLocations(Builder $query): Builder
    {
        return $query->join('job_location', 'jobs.id', '=', 'job_location.job_id')
            ->join('locations', 'job_location.location_id', '=', 'locations.id');
    }

    public function scopeJoinCategories(Builder $query): Builder
    {
        return $query->join('job_category', 'jobs.id', '=', 'job_category.job_id')
            ->join('categories', 'job_category.category_id', '=', 'categories.id');
    }

    /**
     * Join job attributes based on attribute accessor. To allow multiple joins for different attributes.
     * 
     * @param Builder $query Builder instance
     * @param string $attribute_accessor Attribute accessor
     * @return Builder
     */
    public function scopeJoinAttributes(Builder $query, string $attribute_accessor): Builder
    {
        return $query->join("job_attribute_values as $attribute_accessor", 'jobs.id', '=', "$attribute_accessor.job_id")
            ->join("attributes as att_$attribute_accessor", "$attribute_accessor.attribute_id", '=', "att_$attribute_accessor.id");
    }

    /**
     * Join job attributes based on attribute ID.
     * 
     * @param Builder $query Builder instance
     * @param int $attribute_id Attribute ID
     * @param string $operator Operator
     * @param array $values Values
     * @return Builder
     */
    public function scopeFilterByJobAttributes(Builder $query, Attribute $attribute, $operator, $values): Builder
    {
        //Validation for date and numeric attributes
        if ($attribute->is_date) {
            $values = array_map(function ($value) {
                try {
                    return Carbon::parse($value)->format('Y-m-d');
                } catch (Exception $e) {
                    throw new Exception("Invalid date format: $value");
                }
            }, $values);
        }

        if ($attribute->is_numeric) {
            $values = array_map(function ($value) {
                try {
                    return (float)$value;
                } catch (Exception $e) {
                    throw new Exception("Invalid numeric format: $value");
                }
            }, $values);
        }

        $query->where("job_att_$attribute->id.attribute_id", $attribute->id);
        switch ($operator) {
            case 'IN':
                $query->whereIn("job_att_$attribute->id.value", $values);
                break;
            case 'LIKE':
                $query->where("job_att_$attribute->id.value", 'LIKE', '%' . $values[0] . '%');
                break;
            default:
                if ($attribute->is_numeric) {
                    $query->whereRaw("CAST(job_att_$attribute->id.value AS DECIMAL) $operator ?", $values[0]);
                } else {
                    $query->where("job_att_$attribute->id.value", $operator, $values[0]);
                }
                break;
        }


        return $query;
    }

    /**
     * Apply field filter based on operator.
     * 
     * @param Builder $query Builder instance
     * @param string $field Field name
     * @param string $operator Operator
     * @param array $values Values
     * 
     */
    public function scopeFilterField(Builder $query, $field, $operator, array $values): Builder
    {
        switch ($operator) {
            case 'LIKE':
                //use where column like %value% for LIKE operator
                $query->where($field, 'LIKE', '%' . $values[0] . '%');
                break;
            case 'IN':
                //use whereIn for IN operator
                $query->whereIn($field, $values);
                break;
            default:
                //use operator for equality and comparison operators
                $query->where($field, $operator, $values[0]);
                break;
        }
        return $query;
    }

    /**
     * Apply relation filter based on operator.
     * 
     * @param Builder $query Builder instance
     * @param string $column Column name
     * @param string $operator Operator
     * @param array $values Values
     * @throws Exception if invalid operator is used
     */
    public function scopeFilterRelation(Builder $query, $column, $operator, array $values): Builder
    {
        switch ($operator) {
            case '=':
                foreach ($values as $value) {
                    $query->where($column, $value);
                }
                break;
            case 'HAS_ANY':
            case 'IS_ANY':
                $query->where(function ($q) use ($column, $values) {
                    foreach ($values as $value) {
                        $q->orWhere($column, $value);
                    }
                });
                break;
            case 'EXISTS':
                $query->whereNotNull($column);
                break;

            default:
                throw new Exception("Invalid operator for relation: $operator");
        }
        return $query;
    }


    ///relations
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'job_category');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'job_location');
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'job_language');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'job_attribute_values')
            ->withPivot('value')
            ->as('value_object');
    }
}
