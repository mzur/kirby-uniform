<?php

namespace Uniform\Actions;

use L;
use Str;
use Error;
use Email;
use Uniform\Form;

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
    const FROM_KEY = '_from';

    /**
     * Name of the form field for the receie copy checkbox.
     *
     * @var string
     */
    const RECEIVE_COPY_KEY = '_receive_copy';

    /**
     * Create a new instance
     * @param Form  $form
     * @param array $options
     */
    public function __construct(Form $form, array $options = [])
    {
        parent::__construct($form, $options);
        $this->data = $form->data();
    }

    /**
     * Send the form data via email.
     */
    public function perform()
    {
        $params = [
            'service' => $this->option('service', 'mail'),
            'options' => $this->option('service-options', []),
            'to' => $this->requireOption('to'),
            'from' => $this->requireOption('sender'),
            'replyTo' => $this->option('replyTo', $this->form->data(self::FROM_KEY)),
            'subject' => $this->getSubject(),
            'body' => $this->getBody(),
        ];

        try {
            if (!$this->sendEmail($params)) {
                $this->fail('The email could not be sent.');
            }

            if ($this->shouldReceiveCopy()) {
                $params['subject'] = L::get('uniform-email-copy').' '.$params['subject'];
                $params['to'] = $params['replyTo'];

                if (!$this->sendEmail($params)) {
                    $this->fail('The email copy could not be sent but the form has been submitted.');
                }
            }
        } catch (Error $e) {
            $this->fail(L::get('uniform-email-error').' '.$e->getMessage());
        }
    }

    /**
     * Send an email
     *
     * @param  array  $params
     * @return boolean
     */
    protected function sendEmail(array $params)
    {
        $email = new Email($params);

        return $email->send();
    }

    /**
     * Get the email subject and resolve possible template strings
     *
     * @return string
     */
    protected function getSubject()
    {
        // the form could contain arrays which are incompatible with the template function
        $templatableItems = array_filter($this->data, function ($item) {
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

        if ($snippet) {
            $body = $this->getSnippet($snippet, [
                'data' => $this->data,
                'options' => $this->options
            ], true);
        } else {
            $body = '';
            foreach ($this->data as $key => $value) {
                if (Str::startsWith($key, '_')) continue;

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
     * Returns the a rendered snippet as string.
     *
     * @param  string $name
     * @param  array  $data
     * @return string
     */
    protected function getSnippet($name, array $data)
    {
        return snippet($name, $data);
    }

    /**
     * Should a copy of the email be sent to the user?
     *
     * @return boolean
     */
    protected function shouldReceiveCopy()
    {
        return $this->option('receive-copy') && $this->form->data(self::RECEIVE_COPY_KEY);
    }
}
