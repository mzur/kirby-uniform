<?php

namespace Uniform;

trait HasOptions
{
    /**
     * Options
     *
     * @var array
     */
    protected $options;

    /**
     * Get an option from the ptions array
     *
     * @param string $key Option key
     * @param mixed $default Default value if the option was not set
     * @return mixed
     */
    protected function option($key, $default = null)
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
    }
}
