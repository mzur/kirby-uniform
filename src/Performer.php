<?php

namespace Uniform;

class Performer implements PerformerInterface
{
    /**
     * Form data
     *
     * @var array
     */
    protected $data;

    /**
     * Options
     *
     * @var array
     */
    protected $options;

    public function __construct(array $data, array $options = [])
    {
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function perform()
    {
        //
    }

    /**
     * Get an option from the options array
     *
     * @param string $key Option key
     * @param mixed $default Default value if the option was not set
     * @return mixed
     */
    protected function option($key, $default = null)
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
    }

    /**
     * Get an option from the options array and throw an exception if it isn't set
     * @param string $key Option key
     * @return mixed
     * @throws Exception
     */
    protected function requireOption($key)
    {
        $value = $this->option($key);
        if ($value === null) {
            throw new Exception("The '{$key}' option is required.");
        }

        return $value;
    }
}
