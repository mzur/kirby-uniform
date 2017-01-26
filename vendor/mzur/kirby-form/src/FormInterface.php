<?php 

namespace Jevets\Kirby;

interface FormInterface
{
    /**
     * Create a new instance
     *
     * @param  array  $data
     * @return void
     */
    public function __construct($data = []);

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
     * Register a field
     *
     * @param  string  $key
     * @param  array  options
     * @return void
     */
    public function addField($key, $options = []);

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
     * Save the form data to the session
     *
     * @return void
     */
    public function saveData();

    /**
     * Add a single error
     *
     * @param  string  $key
     * @param  mixed  optional  $value
     * @return void
     */
    public function addError($key, $value = '');

    /**
     * Add errors
     *
     * @param  array  $errors
     * @return void
     */
    public function addErrors($errors);

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