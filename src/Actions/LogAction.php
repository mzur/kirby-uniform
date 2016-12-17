<?php

namespace Uniform\Actions;

use L;
use Visitor;

/**
 * Action to log the form data to a file
 */
class LogAction extends Action
{
    use UsesSnippet;

    /**
     * Append the form data to the log file.
     */
    public function perform()
    {
        $file = $this->requireOption('file');
        $content = $this->getContent();

        if ($this->write($file, $content) === false) {
            $this->fail(L::get('uniform-log-error'));
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
        $snippet = $this->option('snippet');
        $data = $this->form->data();

        if ($snippet) {
            $content = $this->getSnippet($snippet, [
                'data' => $data,
                'options' => $this->options
            ]);
        } else {
            $content = '['.date('c').'] '.Visitor::ip().' '.Visitor::userAgent();

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
}
