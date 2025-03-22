<?php

namespace App\Services;

use App\Models\EAV\Attribute;
use App\Models\Job;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class JobFilterService
{
    //job fields classification
    const TEXT_FIELDS = ['title', 'description', 'company_name'];
    const NUMERIC_FIELDS = ['salary_min', 'salary_max'];
    const ENUM_FIELDS = ['status', 'type'];
    const DATE_FIELDS = ['created_at', 'updated_at', 'published_at'];
    const BOOLEAN_FIELDS = ['is_remote'];

    //operator classification
    const EQUALITY_OPERATORS = [
        '=',
        '!=',
    ];

    const CONTAINS_OPERATORS = [
        'LIKE',
    ];

    const IN_OPERATORS = [
        'IN',
    ];

    const COMPARISON_OPERATORS = [
        '>',
        '<',
        '>=',
        '<=',
    ];

    const MULTIPLE_OPERATORS = [
        'IN',
        'HAS_ANY',
        'IS_ANY',
        'EXISTS',
    ];

    //operator for each data type, used for data type validation
    const TYPE_OPERATORS = [
        'text' => self::CONTAINS_OPERATORS + self::EQUALITY_OPERATORS,
        'number' => self::COMPARISON_OPERATORS + self::EQUALITY_OPERATORS,
        'list' => self::EQUALITY_OPERATORS + self::IN_OPERATORS,
        'boolean' => self::EQUALITY_OPERATORS,
        'date' => self::EQUALITY_OPERATORS + self::COMPARISON_OPERATORS,
    ];

    //available job relations
    const RELATIONS = ['categories', 'locations', 'languages'];

    //relation operators
    const RELATION_OPERATORS = [
        '=',
        'HAS_ANY',
        'IS_ANY',
        'EXISTS',
    ];

    //joins for each relation to be applied before applying filters
    private $joins = [];

    public function filter($filterExpression = null)
    {
        Log::info($filterExpression);
        $query = $this->parseFilter($filterExpression);
        Log::info($query->toSql());
        return $query->withRelations()->withJobAttributes();
    }



    private function parseFilter(string $filter)
    {

        if (strpos($filter, 'filter=') === 0) {
            $filter = substr($filter, 7); // Remove 'filter=' prefix if exists
        }

        // Initialize the query
        $query = Job::query()->select('jobs.*');

        // If no filter is provided, return all jobs
        if (!$filter) {
            return $query;
        }

        // find filters from filter string and populate filter arrays
        $this->applyFilterExpression($query, $filter);

        //apply joins to main query
        $this->applyJoins($query);

        return $query;
    }

    /**
     * Apply filter expression to query
     * 
     * @param Builder $query Builder instance
     * @param string $expression Filter expression
     */
    private function applyFilterExpression(Builder $query, string $expression)
    {
        Log::info($expression);
        // Handle AND conditions by calling the function recursively using where()
        if (preg_match('/(.+) AND (.+)/', $expression, $matches)) {
            $leftExpression = trim($matches[1]);
            $rightExpression = trim($matches[2]);

            // Handle left and rightparentheses
            if ($leftExpression[0] === '(') {
                $leftExpression = substr($leftExpression, 1);
            }

            if ($leftExpression[strlen($leftExpression) - 1] === ')') {
                $leftExpression = substr($leftExpression, 0, -1);
            }

            if ($rightExpression[0] === '(') {
                $rightExpression = substr($rightExpression, 1);
            }

            if ($rightExpression[strlen($rightExpression) - 1] === ')') {
                $rightExpression = substr($rightExpression, 0, -1);
            }

            $query->where(function ($q) use ($leftExpression) {
                $this->applyFilterExpression($q, $leftExpression);
            });

            $query->where(function ($q) use ($rightExpression) {
                $this->applyFilterExpression($q, $rightExpression);
            });

            return;
        }

        // Handle OR conditions by calling the function recursively using orWhere()
        if (preg_match('/(.+) OR (.+)/', $expression, $matches)) {
            $leftExpression = trim($matches[1]);
            $rightExpression = trim($matches[2]);

            // Handle left and right parentheses
            if ($leftExpression[0] === '(') {
                $leftExpression = substr($leftExpression, 1);
            }

            if ($leftExpression[strlen($leftExpression) - 1] === ')') {
                $leftExpression = substr($leftExpression, 0, -1);
            }

            if ($rightExpression[0] === '(') {
                $rightExpression = substr($rightExpression, 1);
            }

            if ($rightExpression[strlen($rightExpression) - 1] === ')') {
                $rightExpression = substr($rightExpression, 0, -1);
            }

            $query->where(function ($q) use ($leftExpression, $rightExpression) {
                $q->where(function ($subQ) use ($leftExpression) {
                    $this->applyFilterExpression($subQ, $leftExpression);
                });

                $q->orWhere(function ($subQ) use ($rightExpression) {
                    $this->applyFilterExpression($subQ, $rightExpression);
                });
            });

            return;
        }

        // Apply relationship filtering
        if (preg_match('/(\w+)\s*(=|HAS_ANY|IS_ANY|EXISTS)\s*?\(?([\w\s,]*)\)?/', $expression, $matches) && in_array($matches[1], Job::RELATIONS)) {
            $relation = $matches[1];
            $operator = $matches[2];
            $values = !empty($matches[3]) ? array_map('trim', explode(',', $matches[3])) : [];

            $cleanValues = $this->cleanValues($values);

            if (in_array($relation, self::RELATIONS)) {

                switch ($relation) {
                    case 'categories':
                        $this->addJoin('categories');
                        $query->filterRelation('categories.name', $operator, $cleanValues);
                        break;
                    case 'locations':
                        $this->addJoin('locations');
                        $query->where(function ($q) use ($operator, $cleanValues) {
                            $q->where(function ($q2) use ($operator, $cleanValues) {
                                $q2->filterRelation('locations.city', $operator, $cleanValues);
                            })->orWhere(function ($q2) use ($operator, $cleanValues) {
                                $q2->filterRelation('locations.country', $operator, $cleanValues);
                            })->orWhere(function ($q2) use ($operator, $cleanValues) {
                                $q2->filterRelation('locations.state', $operator, $cleanValues);
                            });
                            if (in_array('Remote', $cleanValues)) {
                                $q->orWhere('jobs.is_remote', 1);
                            }
                        });
                        break;
                    case 'languages':
                        $this->addJoin('languages');
                        $query->filterRelation('languages.name', $operator, $cleanValues);
                        break;
                }

                return;
            } else {
                throw new \Exception("Invalid relation: $relation");
            }
        }

        // Apply attribute conditions (EAV pattern)
        if (preg_match('/attribute:(\w+)\s*(=|!=|>|<|>=|<=|LIKE|IN)\s*?\(?([\w\s,\'\".-]*)\)?/', $expression, $matches)) {
            $attributeName = $matches[1];
            $operator = strtoupper($matches[2]);
            $valuesStr = trim($matches[3]);
            $values = array_map('trim', explode(',', $valuesStr));

            $cleanValues = $this->cleanValues($values);

            $this->applyAttributeFilter($query, $attributeName, $operator, $cleanValues);

            return;
        }

        // Applying direct field filters
        if (preg_match('/(\w+)\s*(IN|LIKE|>=|<=|>|<|=|!=)\s*(?:\(([\w\s,\'\".-]+)\)|([a-zA-Z0-9_-]+))/', $expression, $matches)) {
            $field = $matches[1];
            $operator = $matches[2];
            $valuesStr = $matches[3];
            $values = array_map('trim', explode(',', $valuesStr));

            // Remove quotes if present in each value
            $cleanValues = $this->cleanValues($values);

            //validate operator for each field type
            if (in_array($field, self::TEXT_FIELDS)) {
                $this->validateTypeOperator('text', $operator);
            }

            if (in_array($field, self::NUMERIC_FIELDS)) {
                $this->validateTypeOperator('number', $operator);
            }

            if (in_array($field, self::ENUM_FIELDS)) {
                $this->validateTypeOperator('list', $operator);
            }

            if (in_array($field, self::DATE_FIELDS)) {
                $this->validateTypeOperator('date', $operator);
            }

            if (in_array($field, self::BOOLEAN_FIELDS)) {
                $this->validateTypeOperator('boolean', $operator);
            }


            $query->filterField($field, $operator, $cleanValues);
            return;
        } else {
            //throw error if filter expression can't be parsed
            throw new Exception("Invalid filter expression: $expression");
        }
    }

    /**
     * Apply joins to main query using joins array
     * 
     * @param Builder $query Builder instance
     */
    private function applyJoins(Builder $query)
    {
        foreach ($this->joins as $join) {
            switch ($join) {
                case 'categories':
                    $query->joinCategories();
                    break;
                case 'locations':
                    $query->joinLocations();
                    break;
                case 'languages':
                    $query->joinLanguages();
                    break;
            }
        }
    }

    /**
     * Apply attribute filter based on operator
     * 
     * @param Builder $query Builder instance
     * @param string $attributeName Attribute name
     * @param string $operator Operator
     * @param array $values Values
     */
    private function applyAttributeFilter(Builder $query, string $attributeName, string $operator, array $values)
    {
        $attribute = Attribute::where('name', $attributeName)->first();

        if (!$attribute) {
            throw new Exception("Attribute not found: $attributeName");
        }

        $this->validateTypeOperator($attribute->type, $operator);

        $query->filterByJobAttributes($attribute->id, $operator, $values);

    }

    /**
     * Validate operator for data field type,
     * can be used for attribute type or job table field type
     * 
     * @param string $type Attribute type or job table field type
     * @param string $operator Operator
     * @throws Exception if invalid operator is used
     */
    private function validateTypeOperator(string $type, string $operator)
    {

        if ($type == 'select') {
            $type = 'list';
        }

        if (!in_array($operator, self::TYPE_OPERATORS[$type])) {
            throw new Exception("Invalid operator for attribute type: $type");
        }
    }

    /**
     * Clean values by removing quotes
     * 
     * @param array $values Values
     * @return array Cleaned values
     */
    private function cleanValues(array $values)
    {
        $cleanValues = [];
        foreach ($values as $value) {
            if ((substr($value, 0, 1) === "'" && substr($value, -1) === "'") ||
                (substr($value, 0, 1) === '"' && substr($value, -1) === '"')
            ) {
                $cleanValues[] = substr($value, 1, -1);
            } else {
                $cleanValues[] = $value;
            }
        }
        return $cleanValues;
    }

    /**
     * Add join to joins array
     * 
     * @param string $relation Relation name
     */
    private function addJoin(string $relation)
    {
        $this->joins[] = $relation;
    }
}
