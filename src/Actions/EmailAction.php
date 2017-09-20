<?php

namespace Uniform\Actions;

use C;
use L;
use Str;
use Email;
use Error;
use Exception;
use Uniform\Form;

/**
 * Action to send the form data via email.
 */
class EmailAction extends Action
{
    use UsesSnippet;

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
        $params = [
            'service' => $this->option('service', 'mail'),
            'options' => $this->option('service-options', []),
            'to' => $this->requireOption('to'),
            'from' => $this->requireOption('from'),
            'replyTo' => $this->option('replyTo', $this->form->data(self::EMAIL_KEY)),
            'subject' => $this->getSubject(),
            'body' => $this->getBody(),
        ];

        try {
            $this->sendEmail($params);

            if ($this->shouldReceiveCopy()) {
                $params['subject'] = L::get('uniform-email-copy').' '.$params['subject'];
                $to = $params['to'];
                $params['to'] = $params['replyTo'];
                $params['replyTo'] = $to;
                $this->sendEmail($params);
            }
        } catch (Exception $e) {
            $this->handleException($e);
        } catch (Error $e) {
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
        if (c::get('debug') === true) {
            $this->fail(L::get('uniform-email-error').': '.$e->getMessage());
        }

        $this->fail(L::get('uniform-email-error').'.');
    }

    /**
     * Send an email
     *
     * @param  array  $params
     */
    protected function sendEmail(array $params)
    {
        $email = new Email($params);

        if (!$email->send()) {
            throw $email->error;
        }
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

        $subject = Str::template($this->option('subject', L::get('uniform-email-subject')), $templatableItems);

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
        $snippet = $this->option('snippet');
        $data = $this->form->data();

        if ($snippet) {
            $body = $this->getSnippet($snippet, [
                'data' => $data,
                'options' => $this->options
            ]);
        } else {
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
