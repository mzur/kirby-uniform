<?php

namespace Uniform\Guards;

use l;
use s;
use Uniform\Form;

class CalcGuard extends Guard
{
    /**
     * Session key for the captcha result
     *
     * @var string
     */
    const FLASH_KEY = 'uniform-captcha-result';

    /**
     * Captcha field name
     *
     * @var string
     */
    const FIELD_NAME = '_captcha';

    /**
     * Name of the calc field
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
     * Check if the captcha field was filled in correctly
     * Remove the field from the form data if it was empty.
     */
    public function check()
    {
        $result = s::get(self::FLASH_KEY, null);
        if ($result === null || !array_key_exists($this->field, $this->data) || $this->data[$this->field] != $result) {
            $this->reject(l::get('uniform-fields-not-valid'));
        }
        $this->form->forget($this->field);
    }
}
