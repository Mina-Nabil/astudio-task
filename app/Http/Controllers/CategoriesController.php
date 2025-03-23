<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\JobResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return CategoryResource::collection($categories);
    }

    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    public function jobs(Category $category)
    {
        $jobs = $category->jobs()->withRelations()->withJobAttributes()->paginate(10);
        return JobResource::collection($jobs);
    }
}
