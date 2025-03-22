<?php

namespace App\Models;

use App\Models\EAV\Attribute;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    ///scopes
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
     * Join job attributes based on attribute ID.
     * 
     * @param Builder $query Builder instance
     * @param int $attribute_id Attribute ID
     * @param string $operator Operator
     * @param array $values Values
     * @return Builder
     */
    public function scopeFilterByJobAttributes(Builder $query, $attribute_id, $operator, $values): Builder
    {
         $query->join('job_attribute_values', 'jobs.id', '=', 'job_attribute_values.job_id')
            ->where('job_attribute_values.attribute_id', $attribute_id);

            switch ($operator) {
                case 'IN':
                    $query->whereIn('job_attribute_values.value', $values);
                    break;
                case 'LIKE':
                    $query->where('job_attribute_values.value', 'LIKE', '%' . $values[0] . '%');
                    break;
                default:
                    $query->where('job_attribute_values.value', $operator, $values[0]);
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
