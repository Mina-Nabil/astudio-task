<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    protected $fillable = ['city', 'state', 'country'];
    public $timestamps = false;

    public function jobs()
    {
        return $this->belongsToMany(Job::class);
    }
}