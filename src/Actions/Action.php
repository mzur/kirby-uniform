<?php

namespace Uniform\Actions;

use Uniform\Exceptions\ActionFailedException;

class Action implements ActionInterface
{

    /**
     * The form data
     *
     * @var array
     */
    protected $data;

    /**
     * Action options
     *
     * @var array
     */
    protected $options;

    /**
     * Failed the action during execution?
     *
     * @var boolean
     */
    protected $failed;

    /**
     * Error message in case of failure to execute
     *
     * @var string
     */
    protected $message;

    /**
     * Create a new instance
     *
     * @param  array  $data  Form data
     * @param array  $options Action options
     */
    function __construct(array $data, array $options = [])
    {
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function hasFailed()
    {
        return $this->failed;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Make this action fail by throwing an ActionFailedException.
     *
     * @param  string $message Error message
     * @throws ActionFailedException
     */
    protected function fail($message = null)
    {
        $this->failed = false;
        $this->message = $message ?: static::class.' failed.';

        throw new ActionFailedException($this->message);
    }
}
