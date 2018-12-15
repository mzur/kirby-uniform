<?php

namespace Uniform\Actions;

use Exception;
use Uniform\Form;
use Kirby\Cms\App;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\I18n;

/**
 * Action to send the form data via email.
 */
class EmailAction extends Action
{
    /**
     * Name of the form field for the user's email address.
     *
     * @var string
     */
    const EMAIL_KEY = 'email';

    /**
     * Name of the form field for the receie copy checkbox.
     *
     * @var string
     */
    const RECEIVE_COPY_KEY = 'receive_copy';

    /**
     * Send the form data via email.
     */
    public function perform()
    {
        $params = array_merge($this->options, [
            'to' => $this->requireOption('to'),
            'from' => $this->requireOption('from'),
            'replyTo' => $this->option('replyTo', $this->form->data(self::EMAIL_KEY)),
            'subject' => $this->getSubject(),
        ]);

        if (empty($params['replyTo'])) {
            unset($params['replyTo']);
        }

        if (array_key_exists('data', $params)) {
            $params['data'] = array_merge($params['data'], $this->form->data());
        } else {
            $params['data'] = $this->form->data();
        }

        if (isset($params['template'])) {
            $params['data'] = array_merge($params['data'], [
                '_data' => $params['data'],
                '_options' => $this->options,
            ]);
        } else {
            $params['body'] = $this->getBody();
        }

        try {
            $this->sendEmail($params);

            if ($this->shouldReceiveCopy()) {
                $params['subject'] = I18n::translate('uniform-email-copy').' '.$params['subject'];
                $to = $params['to'];
                $params['to'] = $params['replyTo'];
                $params['replyTo'] = $to;
                $this->sendEmail($params);
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Handle an exception when the email should be sent.
     *
     * @param Exception|Error $e
     */
    protected function handleException($e)
    {
        if (App::instance()->option('debug') === true) {
            $this->fail(I18n::translate('uniform-email-error').': '.$e->getMessage());
        }

        $this->fail(I18n::translate('uniform-email-error').'.');
    }

    /**
     * Send an email
     *
     * @param  array  $params
     */
    protected function sendEmail(array $params)
    {
        App::instance()->email($params);
    }

    /**
     * Get the email subject and resolve possible template strings
     *
     * @return string
     */
    protected function getSubject()
    {
        // the form could contain arrays which are incompatible with the template function
        $templatableItems = array_filter($this->form->data(), function ($item) {
            return is_scalar($item);
        });

        $subject = Str::template($this->option('subject', I18n::translate('uniform-email-subject')), $templatableItems);

        // Remove newlines to prevent malicious modifications of the email header.
        return str_replace("\n", '', $subject);
    }

    /**
     * Get the email body
     *
     * @return string
     */
    protected function getBody()
    {
        $data = $this->form->data();

        unset($data[self::EMAIL_KEY]);
        unset($data[self::RECEIVE_COPY_KEY]);
        $body = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', array_filter($value, function ($i) {
                    return $i !== '';
                }));
            }

            $body .= ucfirst($key).': '.$value."\n\n";
        }

        return $body;
    }

    /**
     * Should a copy of the email be sent to the user?
     *
     * @return boolean
     */
    protected function shouldReceiveCopy()
    {
        return $this->option('receive-copy') === true
            && $this->form->data(self::RECEIVE_COPY_KEY);
    }
}
