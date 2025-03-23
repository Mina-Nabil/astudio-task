<?php

namespace App\Http\Controllers;

use App\Http\Resources\LanguageResource;
use App\Http\Resources\JobResource;
use App\Models\Language;

class LanguagesController extends Controller
{
    public function index()
    {
        $languages = Language::all();
        return LanguageResource::collection($languages);
    }

    public function show(Language $language)
    {
        return new LanguageResource($language);
    }

    public function jobs(Language $language)
    {
        $jobs = $language->jobs()->withRelations()->withJobAttributes()->paginate(10);
        return JobResource::collection($jobs);
    }
}
