<?php

namespace Uniform\Actions;

/**
 * Action to dump the form data to the page.
 */
class DumpAction extends Action
{
    public function execute()
    {
        var_dump($this->data);
    }
}
