<?php

namespace Uniform\Actions;

use A;
use L;

/**
 * Action to set a recipient email address and send the form data via email.
 */
class EmailSelectAction extends EmailAction
{
    /**
     * Name of the form field for the recipient email address.
     *
     * @var string
     */
    const RECIPIENT_KEY = '_recipient';

    /**
     * Set the chosen recipient email address and send the form data via email.
     */
    public function execute()
    {
        $this->options['to'] = $this->getRecipient();
        unset($this->data[self::RECIPIENT_KEY]);
        unset($this->options['allowed-recipients']);

        return parent::execute();
    }

    /**
     * Get the chosen recipient or fail if it is invalid
     *
     * @return string
     */
    protected function getRecipient()
    {
        $recipient = A::get($this->data, self::RECIPIENT_KEY);
        $allowed = $this->requireOption('allowed-recipients');

        if (!array_key_exists($recipient, $allowed)) {
            $this->fail(L::get('uniform-email-error').' '.L::get('uniform-email-select-error'));
        }

        return $allowed[$recipient];
    }
}
