<?php

namespace App\Services;

use App\Models\EAV\Attribute;
use App\Rules\AttributeValueRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AttributeValidationService
{
    /**
     * Validate attributes from a request
     * Makes sure the attributes values are relevant to the attribute type
     *
     * @param array $attributes The attributes to validate
     * @return array The validated attributes
     * @throws ValidationException If validation fails
     */
    public function validateAttributes(array $attributes): array
    {
        $validatedAttributes = [];

        foreach ($attributes as $attributeData) {
            // Check if attribute ID exists
            if (!isset($attributeData['id'])) {
                throw ValidationException::withMessages([
                    'attributes' => 'Each attribute must have an ID.'
                ]);
            }

            // Find the attribute
            $attribute = Attribute::find($attributeData['id']);
            if (!$attribute) {
                throw ValidationException::withMessages([
                    'attributes' => "Attribute with ID {$attributeData['id']} not found."
                ]);
            }

            // Validate the attribute value
            $this->validateAttributeValue($attribute, $attributeData);

            // Add to validated attributes
            $validatedAttributes[] = [
                'id' => $attribute->id,
                'value' => $this->formatAttributeValue($attribute, $attributeData['value'])
            ];
        }

        return $validatedAttributes;
    }

    /**
     * Validate a single attribute value
     *
     * @param Attribute $attribute The attribute to validate against
     * @param array $attributeData The attribute data from the request
     * @throws ValidationException If validation fails
     */
    protected function validateAttributeValue(Attribute $attribute, array $attributeData): void
    {
        // Check if value exists
        if (!isset($attributeData['value'])) {
            throw ValidationException::withMessages([
                "attributes.{$attribute->id}" => "Value for attribute {$attribute->name} is required."
            ]);
        }

        // Create a validator using our custom rule
        $validator = Validator::make(
            ['value' => $attributeData['value']],
            ['value' => [new AttributeValueRule($attribute)]]
        );

        // Check if validation fails
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                "attributes.{$attribute->id}" => $validator->errors()->first('value')
            ]);
        }
    }

    /**
     * Format the attribute value based on its type
     *
     * @param Attribute $attribute The attribute
     * @param mixed $value The value to format
     * @return mixed The formatted value
     */
    protected function formatAttributeValue(Attribute $attribute, $value)
    {
        switch ($attribute->type) {
            case Attribute::TYPE_NUMBER:
                return is_numeric($value) ? (float)$value : $value;
                
            case Attribute::TYPE_BOOLEAN:
                if (is_string($value)) {
                    $value = strtolower($value);
                    return in_array($value, ['true', '1', 'yes']) ? true : false;
                }
                return (bool)$value;
                
            case Attribute::TYPE_DATE:
                return is_string($value) ? $value : $value->format('Y-m-d');
                
            default:
                return $value;
        }
    }
} 