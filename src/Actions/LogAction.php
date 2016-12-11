<?php

namespace Uniform\Actions;

use L;
use Visitor;
use Uniform\Exceptions\Exception;

/**
 * Action to log the form data to a file
 */
class LogAction extends Action
{
    /**
     * Path to the logfile.
     *
     * @var string
     */
    protected $file;

     /**
     * {@inheritDoc}
     */
    function __construct(array $data, array $options = [])
    {
        parent::__construct($data, $options);

        $this->file = $this->option('file');
        if (!$this->file) {
            throw new Exception('No logfile specified!');
        }
    }

    /**
     * Append the form data to the log file.
     */
    public function execute()
    {
        $snippet = $this->option('snippet');

        if (!$snippet) {
            $content = $this->getContent();
        } else {
            $content = snippet($snippet, [
                'data' => $this->data,
                'options' => $this->options
            ], true);
        }

        if (file_put_contents($this->file, $content, FILE_APPEND | LOCK_EX) === false) {
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
        $content = '['.date('c').'] '.Visitor::ip().' '.Visitor::userAgent();

        foreach ($this->data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', array_filter($value, function ($i) {
                    return $i !== '';
                }));
            }
            $content .= "\n{$key}: {$value}";
        }
        $content .= "\n\n";

        return $content;
    }
}
