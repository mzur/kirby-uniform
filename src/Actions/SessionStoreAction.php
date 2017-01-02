<?php

namespace Uniform\Actions;

use S;

/*
 * Action to store the form data in the user's session under a key given
 * by the action options 'name' value.
 */
class SessionStoreAction extends Action
{
    public function perform()
    {
        // get the name of the session variable
        $name = $this->option('name', 'session-store');

        // put form into session
        S::set($name, $this->form);
    }
}
