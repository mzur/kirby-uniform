<?php

namespace Uniform\Guards;

use Uniform\Form;
use Uniform\Performer;
use Uniform\Exceptions\PerformerException;

class Guard extends Performer
{
    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->reject();
    }

    /**
     * Make this guard reject the request by throwing a PerformerException
     *
     * @param  string $message Rejection message
     * @param string $key Key of the rejection (e.g. form field name)
     * @throws PerformerException
     */
    protected function reject($message = null, $key = null)
    {
        $message = $message ?: static::class.' rejected the request.';
        $key = $key ?: static::class;

        throw new PerformerException($message, $key);
    }
}
