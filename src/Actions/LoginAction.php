<?php

namespace Uniform\Actions;

use L;

/**
 * Action to log in a user.
 */
class LoginAction extends Action
{
    /**
     * Log in a user.
     */
    public function perform()
    {
        $userField = $this->option('userField', 'username');
        $passwordField = $this->option('passwordField', 'password');

        $user = site()->user($this->form->data($userField));

        if (!$user || !$user->login($this->form->data($passwordField))) {
            $this->fail(L::get('uniform-login-error'));
        }

        $redirect = $this->option('redirect');
        if ($redirect !== null) {
            go($redirect);
        }
    }
}
