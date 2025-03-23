<?php

namespace App\Rules;

use App\Models\EAV\Attribute;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AttributeValueRule implements ValidationRule
{
    /**
     * The attribute to validate against
     */
    protected Attribute $attribute;

    /**
     * Create a new rule instance.
     */
    public function __construct(Attribute $attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        switch ($this->attribute->type) {
            case Attribute::TYPE_TEXT:
                $this->validateText($value, $fail);
                break;
                
            case Attribute::TYPE_NUMBER:
                $this->validateNumber($value, $fail);
                break;
                
            case Attribute::TYPE_BOOLEAN:
                $this->validateBoolean($value, $fail);
                break;
                
            case Attribute::TYPE_DATE:
                $this->validateDate($value, $fail);
                break;
                
            case Attribute::TYPE_SELECT:
                $this->validateSelect($value, $fail);
                break;
                
            default:
                $fail('The attribute type is invalid.');
                break;
        }
    }

    /**
     * Validate a text attribute value
     */
    protected function validateText($value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail("The {$this->attribute->name} must be a string.");
        }
    }

    /**
     * Validate a number attribute value
     */
    protected function validateNumber($value, Closure $fail): void
    {
        if (!is_numeric($value)) {
            $fail("The {$this->attribute->name} must be a number.");
        }
    }

    /**
     * Validate a boolean attribute value
     */
    protected function validateBoolean($value, Closure $fail): void
    {
        // Convert string representations to boolean for validation
        if (is_string($value)) {
            $value = strtolower($value);
            
            if (!in_array($value, ['true', 'false', '1', '0', 'yes', 'no'])) {
                $fail("The {$this->attribute->name} must be a boolean value (true/false, 1/0, yes/no).");
            }
        } else if (!is_bool($value) && $value !== 1 && $value !== 0) {
            $fail("The {$this->attribute->name} must be a boolean value.");
        }
    }

    /**
     * Validate a date attribute value
     */
    protected function validateDate($value, Closure $fail): void
    {
        try {
            Carbon::parse($value);
        } catch (\Exception $e) {
            $fail("The {$this->attribute->name} must be a valid date.");
        }
    }

    /**
     * Validate a select attribute value against available options
     */
    protected function validateSelect($value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail("The {$this->attribute->name} must be a string.");
            return;
        }
        
        // Get options from the attribute
        $options = $this->getSelectOptions();
        
        if (empty($options)) {
            $fail("No options are defined for the {$this->attribute->name} attribute.");
            return;
        }
        
        if (!in_array($value, $options)) {
            $fail("The {$this->attribute->name} must be one of: " . implode(', ', $options));
        }
    }

    /**
     * Parse the options string into an array of options
     */
    protected function getSelectOptions(): array
    {
        if (empty($this->attribute->options)) {
            return [];
        } else return json_decode($this->attribute->options, true);
    }
} 