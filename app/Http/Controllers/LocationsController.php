<?php

namespace App\Http\Controllers;

use App\Http\Resources\JobResource;
use App\Http\Resources\LocationResource;
use App\Models\Location;

class LocationsController extends Controller
{
    public function index()
    {
        $locations = Location::all();
        return LocationResource::collection($locations);
    }

    public function show(Location $location)
    {
        return new LocationResource($location);
    }

    public function jobs(Location $location)
    {
        $jobs = $location->jobs()->withRelations()->withJobAttributes()->paginate(10);
        return JobResource::collection($jobs);
    }
}
