<?php

namespace Uniform\Actions;

use L;
use A;
use Str;
use Error;

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
     * Receiver email address
     *
     * @var string
     */
    protected $to;

    /**
     * Sender email address
     *
     * @var string
     */
    protected $sender;

     /**
     * {@inheritDoc}
     */
    function __construct(array $data, array $options = [])
    {
        parent::__construct($data, $options);
        $this->to = $this->requireOption('to');
        $this->sender = $this->requireOption('sender');
    }

    /**
     * Append the form data to the log file.
     */
    public function execute()
    {
        $params = [
            'service' => $this->option('service', 'mail'),
            'options' => $this->option('service-options', []),
            'to' => $this->to,
            'from' => $this->sender,
            'replyTo' => $this->option('replyTo', A::get($this->data, self::FROM_KEY)),
            'subject' => $this->getSubject(),
            'body' => $this->getBody(),
        ];

        try {
            if (!email($params)->send()) {
                $this->fail('The email could not be sent.');
            }

            if ($this->shouldReceiveCopy()) {
                $params['subject'] = L::get('uniform-email-copy').' '.$params['subject'];
                $params['to'] = $params['replyTo'];

                if (!email($params)->send()) {
                    $this->fail('The email copy could not be sent but the form has been submitted.');
                }
            }
        } catch (Error $e) {
            $this->fail(L::get('uniform-email-error').' '.$e->getMessage());
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
        $templatableItems = array_filter($this->data, function ($item) {
            return is_scalar($item);
        });

        $subject = Str::template($this->option('subject', l::get('uniform-email-subject')), $templatableItems);

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
            $body = snippet($snippet, [
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
     * Should a copy of the email be sent to the user?
     *
     * @return boolean
     */
    protected function shouldReceiveCopy()
    {
        return $this->option('receive-copy') && array_key_exists(self::RECEIVE_COPY_KEY, $this->data);
    }
}
