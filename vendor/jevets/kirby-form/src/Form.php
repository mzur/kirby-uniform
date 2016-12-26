<?php

namespace Jevets\Kirby;

use Jevets\Kirby\Flash;
use Jevets\Kirby\FormInterface;

class Form implements FormInterface
{
    /**
     * Session key for errors
     *
     * @var string
     */
    const FLASH_KEY_ERRORS = 'form.errors';

    /**
     * Session key for data
     *
     * @var string
     */
    const FLASH_KEY_DATA = 'form.data';

    /**
     * Container for the Flash object
     *
     * @var Jevets\Kirby\Flash
     */
    protected $flash;

    /**
     * Submitted form data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Array of errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Array of registered fields
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Array of validation rules
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Array of validation messages
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Create a new instance
     *
     * @param  array  $data
     * @return void
     */
    public function __construct($data = [])
    {
        // Instantiate the Flash instance
        $this->flash = Flash::getInstance();

        // Register the fields
        foreach ($data as $field => $options) {
            $this->addField($field, $options);
        }

        // Prepopulate the fields with old input data, if it exists
        foreach ($this->fields as $field => $attributes) {
            $this->data[$field] = htmlspecialchars(get($field));
        }

        // Get any errors from the Flash
        $this->errors = $this->flash->get(self::FLASH_KEY_ERRORS, []);
    }

    /**
     * Get or set form data
     *
     * If a second argument is provided, the data
     * for the key will be returned. Otherwise, all
     * data will be returned.
     *
     * @param  string  optional  $key
     * @return mixed|array
     */
    public function data($key = '', $value = '')
    {
        if (!$key)
            return $this->data;

        if (!!$key && !$value)
            return isset($this->data[$key]) ? $this->data[$key] : '';

        $this->data[$key] = $value;
    }

    /**
     * Register a field
     *
     * @param  string  $key
     * @param  array  options
     * @return void
     */
    public function addField($key, $options = [])
    {
        $this->fields[$key] = [
            'name' => $key,
            'id' => isset($options['id']) ? $options['id'] : $key,
            'flash' => isset($options['flash']) ? $options['flash'] : true,
        ];

        $this->rules[$key] = isset($options['rules']) ? $options['rules'] : [];
        $this->messages[$key] = isset($options['message']) ? $options['message'] : [];
    }

    /**
     * Get the data that was flashed to the session
     *
     * @param  string  $key
     * @return  mixed
     */
    public function old($key)
    {
        $data = $this->flash->get(self::FLASH_KEY_DATA, []);

        return isset($data[$key]) ? htmlspecialchars_decode($data[$key]) : '';
    }

    /**
     * Validate the form
     *
     * @return  boolean  whether the form validates
     */
    public function validates()
    {
        $invalid = invalid($this->data(), $this->rules, $this->messages);

        if ($invalid)
        {
            $this->addErrors($invalid);
            $this->saveData();
            return false;
        }

        return true;
    }

    /**
     * Save the form data to the session
     *
     * @return void
     */
     public function saveData()
     {
        $data = [];

        foreach ($this->fields as $field => $options)
        {
            if (!!$options['flash'])
            {
                $data[$field] = $this->data($field);
            }
        }

        $this->flash->set(self::FLASH_KEY_DATA, $data);
     }

    /**
     * Add a single error
     *
     * @param  string  $key
     * @param  mixed  optional  $value
     * @return void
     */
    public function addError($key, $value = '')
    {
        $this->addErrors([$key => $value]);
    }

    /**
     * Add errors
     *
     * Each error will be an array of error messages.
     *
     * @param  array  $data  Each item can be either a single error message that will be
     *                       appended to the errors array of the key or it can be an
     *                       array of error messages that will be merged with the errors
     *                       array of the key.
     * @return void
     */
    public function addErrors($data)
    {
        foreach ($data as $key => $value) {
            if (!isset($this->errors[$key])) {
                $this->errors[$key] = [];
            }

            if (is_array($value)) {
                $this->errors[$key] = array_merge($this->errors[$key], $value);
            } else {
                $this->errors[$key][] = $value;
            }
        }

        $this->saveErrors();
    }

    /**
     * Get all errors
     *
     * @return  array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Get a single error by key
     *
     * If no key is provided, the first error will be returned.
     *
     * @param  string  $key  optional
     *
     * @return  array
     */
    public function error($key = '')
    {
        $errors = $this->errors();

        if ($key) {
            return isset($errors[$key]) ? $errors[$key] : [];
        }

        // Return first array element or an empty array if there is no first element.
        return reset($errors) ?: [];
    }

    /**
     * Save the errors to the session
     */
    protected function saveErrors()
    {
        $this->flash->set(self::FLASH_KEY_ERRORS, $this->errors);
    }
}
