<?php

namespace App\Http\Controllers;

use App\Http\Resources\JobCollection;
use App\Http\Resources\JobResource;
use App\Services\JobFilterService;
use Exception;
use Illuminate\Http\Request;

class JobController extends Controller
{
    protected $jobFilterService;

    public function __construct(JobFilterService $jobFilterService)
    {
        $this->jobFilterService = $jobFilterService;
    }

    public function index()
    {
        $filter = request()->query('filter');
        try {
            $jobs = $this->jobFilterService->filter($filter)->paginate(20);
            return JobResource::collection($jobs);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
