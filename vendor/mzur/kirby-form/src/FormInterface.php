<?php

namespace Jevets\Kirby;

interface FormInterface
{
    /**
     * Create a new instance
     *
     * @param  array  $rules
     * @param string $sessionKey´
     * @return void
     */
    public function __construct($rules = [], $sessionKey = null);

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
    public function data($key = '', $value = '');

    /**
     * Get the data that was flashed to the session
     *
     * @param  string  $key
     * @return  mixed
     */
    public function old($key);

    /**
     * Validate the form
     *
     * @return  boolean  whether the form validates
     */
    public function validates();

    /**
     * Forget a form field
     *
     * @param  string $key Form field name
     */
    public function forget($key);

    /**
     * Get all errors
     *
     * @return  array
     */
    public function errors();

    /**
     * Get a single error by key
     *
     * If no key is provided, the first error will be returned.
     *
     * @param  string  optional  $key
     */
    public function error($key = '');
}
