<?php

namespace Jevets\Kirby\Form\Tests;

use Kirby\Cms\App;

class SessionTestApp extends App
{
    public function session(array $options = [])
    {
        $this->session = $this->session ?? new SessionStub();

        return $this->session;
    }
}
