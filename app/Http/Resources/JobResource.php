<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'company_name' => $this->company_name,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'job_type' => $this->job_type,
            'is_remote' => $this->is_remote,
            'published_at' => Carbon::parse($this->published_at)->format('Y-m-d H:i:s'),
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
            'attributes' => JobAttributeResource::collection($this->attributes),
            'languages' => LanguageResource::collection($this->languages),
            'categories' => CategoryResource::collection($this->categories),
            'locations' => LocationResource::collection($this->locations),
        ];
    }
}
