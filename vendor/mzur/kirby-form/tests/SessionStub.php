<?php

namespace Jevets\Kirby\Form\Tests;

class SessionStub
{
    protected $store = [];

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->store) ? $this->store[$key] : $default;
    }

    public function set($key, $value)
    {
        $this->store[$key] = $value;
    }

    public function pull($key, $default = null)
    {
        if (array_key_exists($key, $this->store)) {
            $value = $this->store[$key];
            unset($this->store[$key]);
            return $value;
        }

        return $default;
    }
}
