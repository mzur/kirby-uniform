<?php

namespace Uniform\Actions;

use Uniform\Performer;
use Uniform\Exceptions\PerformerException;

class Action extends Performer
{
    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->fail();
    }

    /**
     * Make this action fail by throwing an PerformerException.
     *
     * @param  string $message Error message
     * @param string $key Key of the error (e.g. form field name)
     * @throws PerformerException
     */
    protected function fail($message = null, $key = null)
    {
        $message = $message ?: static::class.' failed.';
        $key = $key ?: static::class;

        throw new PerformerException($message, $key);
    }
}
