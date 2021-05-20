<?php

namespace Jevets\Kirby;

use Kirby\Toolkit\A;
use Kirby\Toolkit\V;

class Validator
{
    /**
     * Data to validate.
     *
     * @var array
     */
    protected $data;

    /**
     * Validation rules for each data field.
     *
     * @var array
     */
    protected $rules;

    /**
     * Error messages for each validation rule.
     *
     * @var array
     */
    protected $messages;

    /**
     * Get a new instance.
     *
     * @param array $data Data to validate.
     * @param array $rules Validation rules for each data field.
     * @param array $messages Error messages for each validation rule.
     */
    public function __construct($data, $rules, $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    /**
     * Checks for invalid data
     *
     * @return array
     */
    public function validate()
    {
        $errors = [];
        foreach ($this->rules as $field => $validations) {
            $validationIndex = -1;
            $filled = $this->isFilled($field);
            $message = A::get($this->messages, $field, $field);
            // True if there is an error message for each validation method.
            $messageArray = is_array($message);
            foreach ($validations as $method => $options) {
                if (is_numeric($method)) {
                    $method = $options;
                    $options = [];
                }
                $validationIndex++;
                if ($method === 'required') {
                    if ($filled) {
                        // Field is required and filled.
                        continue;
                    }
                } else if ($filled) {
                    if (!is_array($options)) {
                        $options = [$options];
                    }
                    array_unshift($options, A::get($this->data, $field));
                    if (call_user_func_array([V::class, $method], $options)) {
                        // Field is filled and passes validation method.
                        continue;
                    }
                } else {
                    // If a field is not required and not filled, no validation should be done.
                    continue;
                }

                // If no continue was called we have a failed validation.
                if ($messageArray) {
                    $errors[$field][] = A::get($message, $validationIndex, $field);
                } else {
                    $errors[$field] = $message;
                }
            }
        }

        return $errors;
    }

    /**
     * Checks if a data field is filled.
     * See: http://php.net/manual/en/types.comparisons.php
     * Not filled are: null, undefined variable, '', []
     *
     * @param string $key
     *
     * @return boolean
     */
    protected function isFilled($key)
    {
        return isset($this->data[$key])
            && $this->data[$key] !== ''
            && $this->data[$key] !== [];
    }
}
