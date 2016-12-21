<?php

namespace Uniform\Actions;

use Uniform\Exceptions\Exception;

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
        $snippet = snippet($name, $data, true);

        if ($snippet === false) {
            throw new Exception("The snippet '{$name}' does not exist.");
        }

        return $snippet;
    }
}
