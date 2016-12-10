<?php

namespace Uniform\Actions;

class DumpAction extends Action
{
    public function execute()
    {
        var_dump($this->data);
    }
}
