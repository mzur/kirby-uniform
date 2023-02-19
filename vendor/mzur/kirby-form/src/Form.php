<?php

namespace Jevets\Kirby;

use Kirby\Cms\App;
use Kirby\Toolkit\I18n;
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
     * @param string $sessionKey
     * @return void
     */
    public function __construct($rules = [], $sessionKey = null)
    {
        $request = App::instance()->request();
        // Instantiate the Flash instance
        $this->flash = $sessionKey ? new Flash($sessionKey) : Flash::getInstance();

        // Register the fields
        foreach ($rules as $field => $options) {
            $this->addField($field, $options);
        }

        // Prepopulate the fields with old input data, if it exists
        foreach ($this->fields as $field => $attributes) {
            if (in_array('file', $this->rules[$field], true)) {
                if (in_array('required', $this->rules[$field])) {
                    $this->rules[$field][array_search('file', $this->rules[$field])] = 'requiredFile';
                }
                $this->data[$field] = $request->files()->get($field);
            } else {
                // Decode HTML entities that might have been encoded by $this->old()
                $data = $this->decodeField($request->body()->get($field));
                $this->data[$field] = $this->trimWhitespace($data);
            }
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
     * @param string $key
     * @param string $value
     * @param bool $escape
     * @return mixed|array
     */
    public function data($key = '', $value = '', $escape = true)
    {
        if ($key === '') {
            return $escape ? array_map([$this, 'encodeField'], $this->data) : $this->data;
        } elseif ($value === '') {
            if (! isset($this->data[$key])) {
                return '';
            }

            return $escape ? $this->encodeField($this->data[$key]) : $this->data[$key];
        }

        $this->data[$key] = $this->trimWhitespace($value);
    }

    /**
     * Get the data that was flashed to the session
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return  mixed
     */
    public function old($key, $default = '')
    {
        $data = $this->flash->get(self::FLASH_KEY_DATA, []);

        return isset($data[$key]) ? $this->encodeField($data[$key]) : $default;
    }

    /**
     * Validate the form
     *
     * @throws TokenMismatchException If not in debug mode and the CSRF token is invalid
     * @return  boolean  whether the form validates
     */
    public function validates()
    {
        $app = App::instance();

        $token = $app->request()->csrf() ?? $app->request()->body()->get(self::CSRF_FIELD);
        if (empty($token) || csrf($token) !== true) {
            if ($app->option('debug', false) === true) {
                throw new TokenMismatchException('The CSRF token was invalid.');
            }
            $this->addError(self::CSRF_FIELD, I18n::translate('form-csrf-expired', 'Your session timed out. Please submit the form again.'));
            $this->saveData();

            return false;
        }

        $validator = new Validator($this->data, $this->rules, $this->messages);
        $invalid = $validator->validate();

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
     * Get fields by key
     *
     * If no key is provided, all fields will be returned.
     *
     * @param  string  $key  optional
     *
     * @return  array
     */
    public function fields($key = '')
    {
        $fields = $this->fields;

        if ($key) {
            return isset($fields[$key]) ? $fields[$key] : [];
        }

        return $fields;
    }

    /**
     * Get rules by key
     *
     * If no key is provided, all rules will be returned.
     *
     * @param  string  $key  optional
     *
     * @return  array
     */
    public function rules($key = '')
    {
        $rules = $this->rules;

        if ($key) {
            return isset($rules[$key]) ? $rules[$key] : [];
        }

        return $rules;
    }

    /**
     * Get messages by key
     *
     * If no key is provided, all messages will be returned.
     *
     * @param  string  $key  optional
     *
     * @return  array
     */
    public function messages($key = '')
    {
        $messages = $this->messages;

        if ($key) {
            return isset($messages[$key]) ? $messages[$key] : [];
        }

        return $messages;
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
                $data[$field] = $this->data($field, '', false);
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
            : htmlspecialchars_decode($data ?? '');
    }

    /**
     * Trim whitespace from input data
     *
     * @param string|array $data
     *
     * @return string|array
     */
    protected function trimWhitespace($data)
    {
        return is_array($data)
            ? array_map([$this, 'trimWhitespace'], $data)
            : trim($data ?? '');
    }
}
