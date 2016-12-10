<?php

namespace Uniform\Actions;

use Uniform\HasOptions;
use Uniform\Exceptions\ActionFailedException;

class Action implements ActionInterface
{
    use HasOptions;

    /**
     * The form data
     *
     * @var array
     */
    protected $data;

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
        $this->failed = false;
        $this->message = '';
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
