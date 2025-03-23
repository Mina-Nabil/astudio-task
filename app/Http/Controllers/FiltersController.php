<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttributeResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\LanguageResource;
use App\Http\Resources\LocationResource;
use App\Models\Category;
use App\Models\EAV\Attribute;
use App\Models\Language;
use App\Models\Location;
use Illuminate\Http\Request;

class FiltersController extends Controller
{
    public function categories()
    {
        $categories = Category::all();
        return CategoryResource::collection($categories);
    }

    public function locations()
    {
        $locations = Location::all();
        return LocationResource::collection($locations);
    }

    public function languages()
    {
        $languages = Language::all();
        return LanguageResource::collection($languages);
    }

    public function attributes()
    {
        $attributes = Attribute::all();
        return AttributeResource::collection($attributes);
    }
    
}
