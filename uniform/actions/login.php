<?php

/*
 * Action to log in to the Kirby frontend
 */
uniform::$actions['login'] = function ($form, $actionOptions) {
    $user = site()->user($form['username']);
    $redirect = a::get($actionOptions, 'redirect', false);

    if ($user && $user->login($form['password'])) {
        if ($redirect !== false) {
            go($redirect);
        }

        return [
            'success' => true,
            'message' => l::get('uniform-login-success'),
        ];
    } else {
        return [
            'success' => false,
            'message' => l::get('uniform-login-error'),
        ];
    }
};
