<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobRequest;
use App\Http\Resources\JobCollection;
use App\Http\Resources\JobResource;
use App\Models\Job;
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
            report($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Job $job)
    {
        return new JobResource($job);
    }

    public function store(StoreJobRequest $request)
    {
        $job = Job::createFromRequest($request);
        return new JobResource($job);
    }

    public function update(StoreJobRequest $request, Job $job)
    {
        $job->updateFromRequest($request);
        return new JobResource($job);
    }

    public function destroy(Job $job)
    {
        $job->delete();
        return response()->json(['message' => 'Job deleted successfully']);
    }
    
    
}
