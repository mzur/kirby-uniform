<?php

/**
 * A class to handle performing actions with form data.
 */
class UniForm
{
    /**
     * Length of the token string unique for a session until the form is sent.
     *
     * @var int
     */
    const TOKEN_LENGTH = 20;

    /**
     * The array of all guard callback functions.
     *
     * @var array
     */
    public static $guards = [];

    /**
     * The array of all action callback functions.
     *
     * @var array
     */
    public static $actions = [];

    /**
     * Unique ID/Key of this form.
     *
     * @var string
     */
    private $id;

    /**
     * Array of uniform options, including the actions to be performed.
     *
     * @var array
     */
    private $options;

    /**
     * POST data of the form.
     *
     * @var array
     */
    private $data;

    /**
     * Token string unique for a session until the form is sent. It is used to
     * prevent arbitrary (scripted) post requests to be able to use the form.
     * It shuld only be possible to submit the form from the actual website
     * containing it.
     *
     * @var string
     */
    private $token;

    /**
     * Contains the returned values of the performed action callbacks as well as
     * 'success' and 'message' of the form plugin itself.
     *
     * @var array
     */
    private $actionOutput;

    /**
     * Array of keys of form fields that were required and not given or failed
     * their validation.
     *
     * @var array
     */
    private $erroneousFields;

    /**
     * Creates a new Uniform instance.
     *
     * @param string $id      The unique ID of this form.
     * @param array  $options Array of uniform options, including the actions.
     */
    public function __construct($id, $options)
    {
        if (empty($id)) {
            throw new Error('No Uniform ID was given.');
        }

        $this->id = $id;

        $this->erroneousFields = [];

        $this->options = [
            // spam protection mechanism to use, default is 'honeypot'
            'guard' => a::get($options, 'guard', 'honeypot'),
            // required field names
            'required' => a::get($options, 'required', []),
            // field names to be validated
            'validate' => a::get($options, 'validate', []),
            // action arrays
            'actions' => a::get($options, 'actions', []),
        ];

        // required fields will also be validated by default
        $this->options['validate'] = a::merge(
            $this->options['validate'],
            $this->options['required']
        );

        // initialize output array with the output of the plugin itself
        $this->actionOutput = [
            '_uniform' => [
                'success' => false,
                'message' => '',
            ],
        ];

        // the token is stored as session variable until the form is sent
        // successfully
        $this->token = s::get($this->id);

        if (!$this->token) {
            $this->generateToken();
        }

        // get the data to be sent (if there is any)
        $this->data = get();

        if ($this->requestValid()) {
            if (empty($this->options['actions'])) {
                throw new Error('No Uniform actions were given.');
            }

            if ($this->dataValid()) {
                // uniform is done, now it's the actions turn
                $this->actionOutput['_uniform']['success'] = true;
            }
        }
    }

    /**
     * Custom implementation of a::missing(). Only works with associative arrays
     * and string values.
     *
     * see: https://github.com/getkirby/toolkit/issues/47
     *
     * If a required value is an array, checks if at least one array item is not empty.
     *
     * see: https://github.com/mzur/kirby-uniform/issues/51
     *
     * @param array $array    The source array
     * @param array $required An array of required keys
     *
     * @return array An array of missing fields. If this is empty, nothing is
     *               missing.
     */
    private static function missing($array, $required = [])
    {
        $missing = [];
        foreach ($required as $r) {
            if (!array_key_exists($r, $array) || ($array[$r] === '')) {
                $missing[] = $r;
            } elseif (is_array($array[$r])) {
                $hasValues = false;
                foreach ($array[$r] as $value) {
                    $hasValues |= $value !== '';
                }
                if (!$hasValues) {
                    $missing[] = $r;
                }
            }
        }

        return $missing;
    }

    /**
     * Generates a new token for this form and session.
     */
    private function generateToken()
    {
        $this->token = str::random(static::TOKEN_LENGTH);
        s::set($this->id, $this->token);
    }

    /**
     * Quickly decides if the request is valid so the server is minimally
     * stressed by scripted attacks.
     *
     * @return bool
     */
    private function requestValid()
    {
        if (a::get($this->data, '_submit') !== $this->token) {
            // clear the data array, too
            // see https://github.com/mzur/kirby-uniform/issues/48
            $this->reset();

            return false;
        }

        // remove uniform specific fields from form data
        $this->removeField('_submit');

        $guards = $this->options['guard'];

        if (empty($guards)) {
            // disabled spam protection
            return true;
        }

        // multiple guards can be defines as array
        if (!is_array($guards)) {
            $guards = [$guards];
        }

        foreach ($guards as $guard) {
            if (!array_key_exists($guard, static::$guards)) {
                throw new Error("Uniform guard '{$guard}' is not defined!");
            }

            $check = call_user_func(static::$guards[$guard], $this);

            if (!$check['success']) {
                // display validation error message
                $this->actionOutput['_uniform']['message'] = a::get($check, 'message', '');

                if (array_key_exists('fields', $check) && is_array($check['fields'])) {
                    // mark field(s) as erroneous
                    $this->erroneousFields = array_merge($this->erroneousFields, $check['fields']);
                }

                // reset the form but let the guard choose whether to clear the data
                // see https://github.com/mzur/kirby-uniform/issues/54
                $this->reset(a::get($check, 'clear', true));

                return false;
            }
        }

        return true;
    }

    /**
     * Checks if all required data is present to send the form.
     *
     * @return bool
     */
    private function dataValid()
    {
        // check if all required fields are there
        $this->erroneousFields = static::missing(
            $this->data,
            array_keys($this->options['required'])
        );

        if (!empty($this->erroneousFields)) {
            $this->actionOutput['_uniform']['message'] = l::get('uniform-fields-required');

            return false;
        }

        // perform validation for all fields with a given validation method
        foreach ($this->options['validate'] as $field => $method) {
            $value = a::get($this->data, $field);
            // validate only if a method is given and the field contains data
            if (!empty($method) && !empty($value) && !call('v::'.$method, $value)) {
                array_push($this->erroneousFields, $field);
            }
        }

        if (!empty($this->erroneousFields)) {
            $this->actionOutput['_uniform']['message'] = l::get('uniform-fields-not-valid');

            return false;
        }

        return true;
    }

    /**
     * Reset the form token and clear the form data.
     *
     * @param bool $clear Clear the form data. Default is true.
     */
    private function reset($clear = true)
    {
        // generate new token to spite the bots }:-)
        $this->generateToken();
        if ($clear) {
            $this->data = [];
        }
    }

    /**
     * Executes the form actions.
     *
     * Returns `true` if all actions were performed successfully, `false`
     * otherwise.
     *
     * @return bool
     */
    public function execute()
    {
        // don't execute if there were validation errors
        if (!$this->actionOutput['_uniform']['success']) {
            return false;
        }

        foreach ($this->options['actions'] as $index => $action) {
            // skip this array if it doesn't contain an action name
            if (!($key = a::get($action, '_action'))) {
                continue;
            }

            if (!isset(static::$actions[$key])) {
                throw new Error('The uniform action "'.$key.'" does not exist.');
            }

            $this->actionOutput[$index] = call_user_func(static::$actions[$key], $this->data, $action);
        }

        // if all actions performed successfully, the session is over
        if ($this->successful()) {
            $this->reset();
        }

        return $this->successful();
    }

    /**
     * Returns the value of a form field. The value is empty if the form was
     * sent successful.
     *
     * @param string $key The "name" attribute of the form field.
     *
     * @return mixed
     */
    public function value($key)
    {
        return a::get($this->data, $key, '');
    }

    /**
     * Echos the value of a form field directly as a HTML-safe string.
     *
     * @param string $key The "name" attribute of the form field.
     */
    public function echoValue($key)
    {
        echo str::html($this->value($key));
    }

    /**
     * Checks if a form field has a certain value.
     *
     * Returns `true` if the value equals the content of the form field,
     * `false` otherwise.
     *
     * @param string $key   The "name" attribute of the form field.
     * @param string $value The value tested against the actual content of the form field.
     *
     * @return bool
     */
    public function isValue($key, $value)
    {
        return $this->value($key) === $value;
    }

    /**
     * Checks if there were any errors when validating form fields.
     *
     * Returns `true` if there are erroneous fields. If a key is given, returns
     * `true` if this field is erroneous. Returns `false` otherwise.
     *
     * @param string $key (optional) the key of the form field to check.
     *
     * @return bool
     */
    public function hasError($key = false)
    {
        return ($key) ? v::in($key, $this->erroneousFields) : !empty($this->erroneousFields);
    }

    /**
     * Checks if a field is a required field or not.
     *
     * Returns `true` if the field was in the list of required fields.
     * Returns `false` otherwise.
     *
     * @param string $key the key of the form field to check.
     *
     * @return bool
     */
    public function isRequired($key)
    {
        return $key !== null && !empty($this->options['required'][$key]);
    }

    /**
     * Returns the current session token of this form.
     *
     * @return string
     */
    public function token()
    {
        return $this->token;
    }

    /**
     * Returns the ID of the form.
     *
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Returns the options (array) of the form.
     *
     * @param string $key (optional) Key of a specific option to return. If null, all options are returned.
     *
     * @return mixed
     */
    public function options($key = null)
    {
        if ($key) {
            return a::get($this->options, $key, null);
        } else {
            return $this->options;
        }
    }

    /**
     * Remove a form field from the form.
     *
     * @param string $key Form field name
     */
    public function removeField($key)
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }
    }

    /**
     * If an `$action` was given, returns `true` if the action was performed
     * successfully, `false` otherwise.
     * If no `$action` was given, returns `true` if all actions performed
     * successfully, `false` otherwise.
     *
     * @param mixed $action (optional) the index of the action to perform a
     *                      successful check
     *
     * @return bool
     */
    public function successful($action = false)
    {
        if (!is_int($action) && !is_string($action)) {
            foreach ($this->actionOutput as $output) {
                if (!a::get($output, 'success')) {
                    return false;
                }
            }

            return true;
        } elseif (array_key_exists($action, $this->actionOutput)) {
            return a::get($this->actionOutput[$action], 'success');
        } else {
            return false;
        }
    }

    /**
     * If an `$action` was given, returns the success/error feedback message of
     * the action.
     * If no `$action` was given, returns the feedback messages of all actions;
     * one per line.
     *
     * @param mixed $action (optional) the index of the action to get the
     *                      feedback message from
     *
     * @return string
     */
    public function message($action = false)
    {
        $message = '';
        if (!is_int($action) && !is_string($action)) {
            foreach ($this->actionOutput as $output) {
                $message .= a::get($output, 'message', '')."\n";
            }
        } elseif (array_key_exists($action, $this->actionOutput)) {
            $message = a::get($this->actionOutput[$action], 'message', '');
        }

        return trim($message);
    }

    /**
     * Echos the success/error feedback message directly as a HTML-safe string.
     * Either from one specified action or from all actions.
     *
     * @param mixed $action (optional) the index of the action to get the
     *                      feedback message from
     */
    public function echoMessage($action = false)
    {
        echo str::html($this->message($action));
    }

    /**
     * Returns `true` if there is a success/error feedback message for the
     * specified action.
     * If no action was specified, `true` if there is any  message from any
     * action, `false` otherwise.
     *
     * @param mixed $action (optional) the index of the action to check for the
     *                      presence of a feedback message.
     *
     * @return bool
     */
    public function hasMessage($action = false)
    {
        if (!is_int($action) && !is_string($action)) {
            foreach ($this->actionOutput as $output) {
                if (a::get($output, 'message')) {
                    return true;
                }
            }

            return false;
        } elseif (array_key_exists($action, $this->actionOutput)) {
            return (boolean) a::get($this->actionOutput[$action], 'message');
        } else {
            return false;
        }
    }
}
