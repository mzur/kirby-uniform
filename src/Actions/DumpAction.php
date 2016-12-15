<?php

namespace Uniform\Actions;

/**
 * Action to dump the form data to the page.
 */
class DumpAction extends Action
{
    /**
     * Dump the form data.
     */
    public function perform()
    {
        var_dump($this->form->data());
    }
}
