<?php

namespace Uniform\Guards;

use L;
use Uniform\Form;

/**
 * Guard that checks a honeypot form field.
 */
class HoneypotGuard extends Guard
{
    /**
     * Default name for the honeypot form field.
     *
     * @var string
     */
    const FIELD_NAME = 'website';

    /**
     * Name of the honeypot field
     *
     * @var string
     */
    protected $field;

    /**
     * {@inheritdoc}
     */
    function __construct(Form $form, array $data, array $options = [])
    {
        parent::__construct($form, $data, $options);
        $this->field = $this->option('field', self::FIELD_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->field;
    }

    /**
     * {@inheritDoc}
     * Check if the honeypot field contains data.
     * Remove the honeypot field from the form data if it was empty.
     */
    public function check()
    {
        if (!array_key_exists($this->field, $this->data) || $this->data[$this->field]) {
            $this->reject(L::get('uniform-filled-potty'));
        }
        $this->form->forget($this->field);
    }
}
