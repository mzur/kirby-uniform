<?php

namespace Jevets\Kirby;

use C as Config;
use R as Request;
use Jevets\Kirby\Flash;
use Jevets\Kirby\FormInterface;
use Jevets\Kirby\Exceptions\TokenMismatchException;

class Form implements FormInterface
{
    /**
     * Name of the form field containing the CSRF token.
     *
     * @var string
     */
    const CSRF_FIELD = 'csrf_token';

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
     * @param  array  $rules
     * @return void
     */
    public function __construct($rules = [], $sessionKey = null)
    {
        // Instantiate the Flash instance
        $this->flash = $sessionKey ? new Flash($sessionKey) : Flash::getInstance();

        // Register the fields
        foreach ($rules as $field => $options) {
            $this->addField($field, $options);
        }

        // Prepopulate the fields with old input data, if it exists
        foreach ($this->fields as $field => $attributes) {
            // Decode HTML entities that might have been encoded by $this->old()
            $this->data[$field] = $this->decodeField(Request::postData($field));
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
        if (!$key) {
            return $this->data;
        } elseif (!$value) {
            return isset($this->data[$key]) ? $this->data[$key] : '';
        }

        $this->data[$key] = $value;
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

        // Encode HTML entities for output
        return isset($data[$key]) ? $this->encodeField($data[$key]) : '';
    }

    /**
     * Validate the form
     *
     * @throws TokenMismatchException If not in debug mode and the CSRF token is invalid
     * @return  boolean  whether the form validates
     */
    public function validates()
    {
        if (csrf(Request::postData(self::CSRF_FIELD)) !== true) {
            if (Config::get('debug') === true) {
                throw new TokenMismatchException('The CSRF token was invalid.');
            }

            return false;
        }

        $invalid = invalid($this->data, $this->rules, $this->messages);

        if ($invalid) {
            $this->addErrors($invalid);
            $this->saveData();
            return false;
        }

        return true;
    }

    /**
     * Forget a form field
     *
     * @param  string $key Form field name
     */
    public function forget($key)
    {
        unset($this->data[$key]);
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
     * Add a single error
     *
     * @param  string  $key
     * @param  mixed  optional  $value
     * @return void
     */
    protected function addError($key, $value = '')
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
    protected function addErrors($data)
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
     * Save the form data to the session
     *
     * @return void
     */
     protected function saveData()
     {
        $data = [];

        foreach ($this->fields as $field => $options) {
            if ($options['flash']) {
                $data[$field] = $this->data($field);
            }
        }

        $this->flash->set(self::FLASH_KEY_DATA, $data);
     }

    /**
     * Register a field
     *
     * @param  string  $key
     * @param  array  options
     * @return void
     */
    protected function addField($key, $options = [])
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
     * Save the errors to the session
     */
    protected function saveErrors()
    {
        $this->flash->set(self::FLASH_KEY_ERRORS, $this->errors);
    }

    /**
     * Encode HTML characters of form field data
     *
     * @param string|array $data
     *
     * @return string|array
     */
    protected function encodeField($data)
    {
        return is_array($data)
            ? array_map([$this, 'encodeField'], $data)
            : htmlspecialchars($data);
    }

    /**
     * Decode HTML characters of form field data
     *
     * @param string|array $data
     *
     * @return string|array
     */
    protected function decodeField($data)
    {
        return is_array($data)
            ? array_map([$this, 'decodeField'], $data)
            : htmlspecialchars_decode($data);
    }
}
