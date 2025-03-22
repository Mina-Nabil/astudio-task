<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = 'languages';
    protected $fillable = ['name'];
    public $timestamps = false;

    public function jobs()
    {
        return $this->belongsToMany(Job::class);
    }
}
