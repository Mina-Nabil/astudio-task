<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{

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
    ];



}
