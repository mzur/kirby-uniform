<?php

namespace Uniform\Guards;

use Uniform\Form;
use Uniform\Performer;
use Uniform\Exceptions\PerformerException;

class Guard extends Performer
{
    /**
     * The form instance
     *
     * @var Form
     */
    protected $form;

    /**
     * Create a new instance
     *
     * @param Form $form Form instance
     * @param  array $data Form data
     * @param array $options Action options
     */
    public function __construct(Form $form, array $data, array $options = [])
    {
        parent::__construct($data, $options);
        $this->form = $form;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->reject();
    }

    /**
     * Make this guard reject the request by throwing a PerformerException
     *
     * @param  string $message Rejection message
     * @param string $key Key of the rejection (e.g. form field name)
     * @throws PerformerException
     */
    protected function reject($message = null, $key = null)
    {
        $message = $message ?: static::class.' rejected the request.';
        $key = $key ?: static::class;

        throw new PerformerException($message, $key);
    }
}
