<?php

namespace Uniform\Actions;

use Kirby\Cms\App;
use Kirby\Toolkit\I18n;

/**
 * Action to log the form data to a file
 */
class LogAction extends Action
{
    /**
     * Append the form data to the log file.
     */
    public function perform()
    {
        $file = $this->requireOption('file');
        $content = $this->getContent();

        if ($this->write($file, $content) === false) {
            $this->fail(I18n::translate('uniform-log-error'));
        }
    }

    /**
     * Append the content to the file or create it if it doesn't exist
     *
     * @param  string $filename
     * @param  string $content
     * @return boolean
     */
    protected function write($filename, $content)
    {
        return file_put_contents($filename, $content, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get the content of the log entry
     *
     * @return string
     */
    protected function getContent()
    {
        $template = $this->option('template');
        $escape = $this->option('escapeHtml', true);
        $data = $this->form->data('', '', $escape);

        if ($template) {
            $content = $this->getTemplate($template, [
                'data' => $data,
                'options' => $this->options
            ]);
        } else {
            $visitor = App::instance()->visitor();
            $content = '['.date('c').'] '.$visitor->ip().' '.$visitor->userAgent();

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', array_filter($value, function ($i) {
                        return $i !== '';
                    }));
                }
                $content .= "\n{$key}: {$value}";
            }
            $content .= "\n\n";
        }

        return $content;
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
            throw new \Exception("The template '{$name}' does not exist.");
        }

        return $template->render($data);
    }
}
