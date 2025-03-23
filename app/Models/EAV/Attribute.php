<?php

namespace App\Models\EAV;

use App\Models\Job;
use Database\Factories\AttributeFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute extends Model
{
    use HasFactory;
    protected $table = 'attributes';
    protected $fillable = ['name', 'type', 'options'];
    public $timestamps = false;

    const TYPE_TEXT = 'text';
    const TYPE_NUMBER = 'number';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATE = 'date';
    const TYPE_SELECT = 'select';

    const TYPES = [
        self::TYPE_TEXT,
        self::TYPE_NUMBER,
        self::TYPE_BOOLEAN,
        self::TYPE_DATE,
        self::TYPE_SELECT,
    ];

    //Attributes
    public function getIsNumericAttribute()
    {
        return $this->type === self::TYPE_NUMBER;
    }
    public function getIsDateAttribute()
    {
        return $this->type === self::TYPE_DATE;
    }

    //Relations
    public function jobs() : BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'job_attribute_values')
        ->withPivot('value')
        ->as('value_object');
    }
    
    protected static function newFactory()
    {
        return AttributeFactory::new();
    }
    
}
