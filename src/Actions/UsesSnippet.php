<?php

namespace Uniform\Actions;

trait UsesSnippet
{
    /**
     * Returns the a rendered snippet as string.
     *
     * @param  string $name
     * @param  array  $data
     * @return string
     */
    protected function getSnippet($name, array $data)
    {
        return snippet($name, $data, true);
    }
}
