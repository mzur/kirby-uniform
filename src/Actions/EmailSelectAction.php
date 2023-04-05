<?php

namespace Uniform\Actions;

use Kirby\Toolkit\I18n;

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
    const RECIPIENT_FIELD = 'recipient';

    /**
     * Set the chosen recipient email address and send the form data via email.
     */
    public function perform()
    {
        $this->options['to'] = $this->getRecipient();
        $this->form->forget(self::RECIPIENT_FIELD);
        unset($this->options['allowed-recipients']);

        return parent::perform();
    }

    /**
     * Get the chosen recipient or fail if it is invalid
     *
     * @return string
     */
    protected function getRecipient()
    {
        $recipient = $this->form->data(self::RECIPIENT_FIELD);
        $allowed = $this->requireOption('allowed-recipients');

        if (!array_key_exists($recipient, $allowed)) {
            $this->fail(I18n::translate('uniform-email-error').' '.I18n::translate('uniform-email-select-error'), self::RECIPIENT_FIELD);
        }

        return $allowed[$recipient];
    }
}
