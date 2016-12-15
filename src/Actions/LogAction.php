<?php

namespace Uniform\Actions;

use L;
use Visitor;

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

        if (file_put_contents($file, $content, FILE_APPEND | LOCK_EX) === false) {
            $this->fail(L::get('uniform-log-error'));
        }
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
            $content = snippet($snippet, [
                'data' => $data,
                'options' => $this->options
            ], true);
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
