<?php

namespace Uniform\Actions;

interface ActionInterface
{
    /**
     * Execute the action.
     */
    public function execute();

    /**
     * Check if the action failed.
     *
     * @return boolean
     */
    public function hasFailed();

    /**
     * Get an error message if the action failed.
     *
     * @return string
     */
    public function getMessage();
}
