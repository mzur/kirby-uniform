<?php

namespace Uniform\Actions;

use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;

/*
 * Action to store one or more uploaded files in some directory.
 */
class UploadAction extends Action
{
    /**
     * Paths of directories that were created by this action.
     *
     * @var array
     */
    protected $createdDirectories = [];

    /**
     * Paths of files that were created by this action.
     *
     * @var array
     */
    protected $createdFiles = [];

    /**
     * Move uploaded files to their target directory.
     *
     */
    public function perform()
    {
        $fields = $this->requireOption('fields');

        foreach ($fields as $field => $options) {
            $this->handleFile($field, $options);
        }
    }

    /**
     * Move a single uploaded file.
     *
     * @param string $field Form field name.
     * @param array $options
     */
    protected function handleFile($field, $options)
    {
        $file = $this->form->data($field);

        if (!is_array($file) || !isset($file['error']) || intval($file['error']) !== UPLOAD_ERR_OK) {
            // If this is an array, kirby-form already recognized and validated the
            // uploaded file. If the file is required, this should have been checked
            // during validation.
            return;
        }

        if (!array_key_exists('target', $options)) {
            // No translation because this is a developer error.
            $this->fail("The target directory is missing for field {$field}.");
        }

        $target = $options['target'];

        if (!is_dir($target)) {
            if (@mkdir($target, 0755)) {
                $this->createdDirectories[] = $target;
            } else {
                $this->fail(I18n::translate('uniform-upload-mkdir-fail'), $field);
            }
        }

        $name = $file['name'];
        $prefix = A::get($options, 'prefix');

        if (is_null($prefix)) {
            $name = $this->getRandomPrefix($name);
        } elseif ($prefix !== false) {
            $name = $prefix.$name;
        }

        $path = $target.DIRECTORY_SEPARATOR.$name;
        if (is_file($path)) {
            $this->fail(I18n::translate('uniform-upload-exists'), $field);
        }

        $success = $this->moveFile($file['tmp_name'], $path);

        if ($success) {
            $this->createdFiles[] = $path;
        } else {
            $this->fail(I18n::translate('uniform-upload-failed'), $field);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function fail($message = null, $key = null)
    {
        array_map('unlink', $this->createdFiles);
        array_map('rmdir', $this->createdDirectories);
        parent::fail($message, $key);
    }

    /**
     * Move the uploaded file
     *
     * @param string $source
     * @param string $target
     *
     * @return bool
     */
    protected function moveFile($source, $target)
    {
        return move_uploaded_file($source, $target);
    }

    /**
     * Adds a random prefix to the name
     *
     * @param string $name The name
     * @param integer $length Length of the prefix
     *
     * @return Name with prefix, sepatated by a '_'
     */
    protected function getRandomPrefix($name, $length = 10)
    {
        $prefix = bin2hex(random_bytes(intval($length / 2)));

        return "{$prefix}_{$name}";
    }
}
