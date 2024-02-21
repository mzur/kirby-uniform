<?php

namespace Uniform\Actions;

use Exception;
use Kirby\Cms\App;
use Kirby\Exception\NotFoundException;
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
        $this->options = $this->preset($this->option('preset'));

        $params = array_merge($this->options, [
            'to' => $this->requireOption('to'),
            'from' => $this->requireOption('from'),
            'replyTo' => $this->option('replyTo', $this->form->data(self::EMAIL_KEY)),
            'subject' => $this->getSubject(),
        ]);

        $escape = $this->option('escapeHtml', true);

        if (empty($params['replyTo'])) {
            unset($params['replyTo']);
        }

        if (array_key_exists('data', $params)) {
            $params['data'] = array_merge($params['data'], $this->form->data('', '', $escape));
        } else {
            $params['data'] = $this->form->data('', '', $escape);
        }

        if (isset($params['template'])) {
            $params['data'] = array_merge($params['data'], [
                '_data' => $params['data'],
                '_options' => $this->options,
            ]);
        } else if (isset($params['body']) && is_string($params['body'])) {
            $params['body'] = $this->resolveTemplate($params['body']);
        } else {
            $params['body'] = $this->getBody($this->form->data('', '', $escape));
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
        App::instance()->email([], $params);
    }

    /**
     * Resolve template strings
     * 
     * @param string $string
     * 
     * @return string
     */
    protected function resolveTemplate($string) {
        // the form could contain arrays which are incompatible with the template function
        $templatableItems = array_filter($this->form->data(), function ($item) {
            return is_scalar($item);
        });

        $version = explode('.', App::version());
        $majorVersion = intval($version[0]);
        $minorVersion = intval($version[1]);
        $fallback = ['fallback' => ''];

        // The arguments to Str::template changed in Kirby 3.6.
        if ($majorVersion <= 3 && $minorVersion <= 5) {
            $fallback = '';
        }

        return  Str::template($string, $templatableItems, $fallback);
    }

    /**
     * Get the email subject and resolve possible template strings
     *
     * @return string
     */
    protected function getSubject()
    {
        $subject = $this->resolveTemplate($this->option('subject', I18n::translate('uniform-email-subject')));
        
        // Remove newlines to prevent malicious modifications of the email header.
        return str_replace("\n", '', $subject);
    }

    /**
     * Get the email body
     *
     * @param array $data
     *
     * @return string
     */
    protected function getBody($data)
    {
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

    /**
     * Loads more options from Kirby email presets, if `preset` was set
     *
     * @return array
     */
    private function preset(string|null $preset): array
    {
        if (!$preset) {
            return $this->options;
        }

        if (($presetOptions = App::instance()->option('email.presets.' . $preset)) === null) {
            throw new NotFoundException([
                'key' => 'email.preset.notFound',
                'data' => ['name' => $preset],
            ]);
        }

        // Options passed to the action always superseed preset options
        $options = array_merge($presetOptions, $this->options);
        unset($options['preset']);

        return $options;
    }
}
