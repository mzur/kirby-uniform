<?php

namespace Uniform\Actions;

use A;
use L;
use Str;

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
        if (!array_key_exists($field, $_FILES)) {
            // If no file was uploaded for this field, continue. If the file is required
            // this should be checked during validation.
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
                $this->fail(L::get('uniform-upload-mkdir-fail'), $field);
            }
        }

        $name = $_FILES[$field]['name'];
        $prefix = A::get($options, 'prefix');

        if (is_null($prefix)) {
            $name = Str::random(10).'_'.$name;
        } elseif ($prefix !== false) {
            $name = $prefix.$name;
        }

        $path = $target.DIRECTORY_SEPARATOR.$name;
        if (is_file($path)) {
            $this->fail(L::get('uniform-upload-exists'), $field);
        }

        $success = $this->moveFile($_FILES[$field]['tmp_name'], $path);

        if ($success) {
            $this->createdFiles[] = $path;
        } else {
            $this->fail(L::get('uniform-upload-failed'), $field);
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
}
