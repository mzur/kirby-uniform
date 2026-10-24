<?php

namespace Uniform\Actions;

use Kirby\Cms\App;

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
        $value = $this->form;

        // Kirby 6 does not unserialize objects from the session
        if (intval(explode('.', App::version())[0]) >= 6) {
            $value = $this->form->data();
        }

        // put form into session
        App::instance()->session()->set($name, $value);
    }
}
