<?php

namespace Uniform\Exceptions;

class PerformerException extends Exception
{

    /**
     * Key of the error (e.g. form field name)
     *
     * @var string
     */
    protected $key;

    /**
     * Create a new instance
     *
     * @param  string  $message  Error message
     * @param string  $key Key of the error (e.g. form field name)
     */
    public function __construct($message = '', $key = null)
    {
        parent::__construct($message);
        $this->key = $key;
    }

    /**
     * Get the key of the error
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}
