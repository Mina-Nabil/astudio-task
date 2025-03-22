<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Language extends Model
{
    use HasFactory;

    protected $table = 'languages';
    protected $fillable = ['name'];
    public $timestamps = false;

    public function jobs()
    {
        return $this->belongsToMany(Job::class, 'job_language');
    }
}
