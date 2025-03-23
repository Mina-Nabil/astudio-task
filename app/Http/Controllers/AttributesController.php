<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttributeResource;
use App\Http\Resources\JobResource;
use App\Models\EAV\Attribute;
use Illuminate\Http\Request;

class AttributesController extends Controller
{
    public function index()
    {
        $attributes = Attribute::all();
        return AttributeResource::collection($attributes);
    }
    
    public function show(Attribute $attribute)
    {
        return new AttributeResource($attribute);
    }

    public function jobs(Attribute $attribute)
    {
        $jobs = $attribute->jobs()->withRelations()->withJobAttributes()->paginate(10);
        return JobResource::collection($jobs);
    }
}
