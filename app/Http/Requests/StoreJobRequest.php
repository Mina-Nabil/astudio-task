<?php

namespace App\Http\Requests;

use App\Models\Job;
use App\Services\AttributeValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreJobRequest extends FormRequest
{
    /**
     * The validated attributes
     */
    protected array $validatedAttributes = [];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Basic job information
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company_name' => 'required|string|max:255',
            'salary_min' => 'required|numeric|min:0',
            'salary_max' => 'required|numeric|min:0|gte:salary_min',
            'is_remote' => 'boolean',
            'job_type' => ['required', Rule::in(Job::JOB_TYPE_LIST)],
            'status' => ['required', Rule::in(Job::STATUS_LIST)],
            'published_at' => 'nullable|date',
            
            // Relationship data
            'categories' => 'sometimes|array',
            'categories.*' => 'exists:categories,id',
            
            'locations' => 'sometimes|array',
            'locations.*' => 'exists:locations,id',
            
            'languages' => 'sometimes|array',
            'languages.*' => 'exists:languages,id',
            
            // Basic validation for attributes - detailed validation is done in after()
            'attributes' => 'sometimes|array',
            'attributes.*.id' => 'required|exists:attributes,id',
            'attributes.*.value' => 'required'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'A job title is required',
            'description.required' => 'A job description is required',
            'company_name.required' => 'A company name is required',
            'salary_min.required' => 'Minimum salary is required',
            'salary_max.required' => 'Maximum salary is required',
            'salary_max.gte' => 'Maximum salary must be greater than or equal to minimum salary',
            'job_type.required' => 'Job type is required',
            'job_type.in' => 'Job type must be one of: ' . implode(', ', Job::JOB_TYPE_LIST),
            'status.required' => 'Job status is required',
            'status.in' => 'Job status must be one of: ' . implode(', ', Job::STATUS_LIST),
            'categories.*.exists' => 'Selected category does not exist',
            'locations.*.exists' => 'Selected location does not exist',
            'languages.*.exists' => 'Selected language does not exist',
            'attributes.*.id.exists' => 'Selected attribute does not exist',
            'attributes.*.value.required' => 'Each attribute must have a value'
        ];
    }

    /**
     * Handle a passed validation attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function passedValidation()
    {
        // Only validate attributes if they exist in the request
        if ($this->has('attributes')) {
            try {
                $attributeValidator = app(AttributeValidationService::class);
                $this->validatedAttributes = $attributeValidator->validateAttributes($this->input('attributes'));
                
                // Replace the attributes in the request with the validated ones
                $this->merge(['attributes' => $this->validatedAttributes]);
            } catch (ValidationException $e) {
                throw $e;
            }
        }
    }
}
