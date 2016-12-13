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
     * {@inheritDoc}
     * Check if the honeypot field contains data.
     * Remove the honeypot field from the form data if it was empty.
     */
    public function perform()
    {
        $field = $this->option('field', self::FIELD_NAME);
        if (!array_key_exists($field, $this->data) || $this->data[$field] !== '') {
            $this->reject(L::get('uniform-filled-potty'), $field);
        }
        $this->form->forget($field);
    }
}
