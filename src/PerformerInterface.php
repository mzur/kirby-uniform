<?php

namespace Uniform;

interface PerformerInterface
{
    /**
     * Create a new instance
     *
     * @param Form  $form
     * @param array $options
     */
    public function __construct(Form $form, array $options = []);

    /**
     * Execute the performer.
     */
    public function perform();
}
