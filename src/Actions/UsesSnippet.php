<?php

namespace Uniform\Actions;

use Kirby\Cms\App;
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

    /**
     * Returns the a rendered template as string.
     *
     * @param  string $name
     * @param  array  $data
     * @return string
     */
    protected function getTemplate($name, array $data)
    {
        $template = App::instance()->template($name);

        if (!$template->exists()) {
            throw new Exception("The template '{$name}' does not exist.");
        }

        return $template->render($data);
    }
}
