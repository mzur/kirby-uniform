<?php

/*
 * Action to choose from multiple recipients who should receive the form by
 * email.
 */
uniform::$actions['email-select'] = function ($form, $actionOptions) {
    $allowed = a::get($actionOptions, 'allowed-recipients');

    if (!is_array($allowed)) {
        throw new Exception('Uniform email select action: No allowed recipients!');
    }

    $recipient = a::get($form, '_recipient');

    if (!array_key_exists($recipient, $allowed)) {
        return [
            'success' => false,
            'message' => l::get('uniform-email-error').' '.l::get('uniform-email-select-error'),
        ];
    }

    unset($form['_recipient']);
    unset($actionOptions['allowed-recipients']);
    $actionOptions['to'] = $allowed[$recipient];

    return call_user_func(uniform::$actions['email'], $form, $actionOptions);
};
