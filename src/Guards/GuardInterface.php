<?php

namespace Uniform\Guards;

interface GuardInterface
{
    /**
     * Perform the check.
     */
    public function check();

    /**
     * Check if the guard rejected the request.
     *
     * @return boolean
     */
    public function hasRejected();

    /**
     * Get the reason for rejected access.
     *
     * @return string
     */
    public function getMessage();
}
