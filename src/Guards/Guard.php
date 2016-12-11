<?php

namespace Uniform\Guards;

use Uniform\Form;
use Uniform\HasOptions;
use Uniform\Exceptions\GuardRejectedException;

class Guard implements GuardInterface
{
    use HasOptions;

    /**
     * The form instance
     *
     * @var Form
     */
    protected $form;

    /**
     * The form data
     *
     * @var array
     */
    protected $data;

    /**
     * Did the guard reject the request?
     *
     * @var boolean
     */
    protected $rejected;

    /**
     * The reason for rejected access.
     *
     * @var string
     */
    protected $message;

    /**
     * Create a new instance
     *
     * @param Form $form Form instance
     * @param  array $data Form data
     * @param array $options Action options
     */
    function __construct(Form $form, array $data, array $options = [])
    {
        $this->form = $form;
        $this->data = $data;
        $this->options = $options;
        $this->rejected = false;
        $this->message = '';
    }

    /**
     * {@inheritdoc}
     */
    public function check()
    {
        $this->reject();
    }

    /**
     * {@inheritdoc}
     */
    public function hasRejected()
    {
        return $this->rejected;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return static::class;
    }

    /**
     * Make this guard reject the request by throwing a GuardRejectedException
     *
     * @param  string $message Rejection message
     * @throws GuardRejectedException
     */
    protected function reject($message = null)
    {
        $this->rejected = true;
        $this->message = $message ?: static::class.' rejected the request.';

        throw new GuardRejectedException($this->message);
    }
}
