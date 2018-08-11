# Upload Action

This action handles one or more uploaded files and moves them to some directory. The action does not perform any validation (e.g. file size or type). Use Uniform validation rules for this.

!!! danger "Note"
    Always validate uploaded files with a whitelist of allowed MIME types and maximum file size!

## Example

This example stores an uploaded file in Kirby's content directory.

### Controller

Note that multiple validation messages only work since Kirby 2.5. The `file`, `mime` and `filesize` validators are provided by Uniform. You can also use the `image` validator for image uploads.

```php
<?php

use Uniform\Form;

return function ($kirby)
{
    $form = new Form([
        'filefield' => [
            'rules' => [
                'required',
                'file',
                'mime' => ['application/pdf'],
                'filesize' => 5000,
            ],
            'message' => [
                'Please choose a file.',
                'Please choose a file.',
                'Please choose a PDF.',
                'Please choose a file that is smaller than 5 MB.',
            ],
        ],
    ]);

    if ($kirby->request()->is('POST')) {
        $form->uploadAction(['fields' => [
            'filefield' => [
                'target' => kirby()->roots()->content(),
                'prefix' => false,
            ],
        ]]);
    }

    return compact('form');
};
```

### Template

Note the `enctype` attribute. This is required for file uploads to work.

```html+php
<form enctype="multipart/form-data" method="POST">
    <input type="file" name="filefield" required/>
    <?php echo honeypot_field() ?>
    <?php echo csrf_field() ?>
    <input type="submit" value="Upload">
</form>
```

## Options

### fields

Required. An array listing all file fields of the form. The keys are the field names and the values an options array for each field.

Each options array must contain `target` which specifies the directory where the uploaded file should be moved to. It can also contain an optional `prefix` which will be used as a prefix for the filename of the uploaded file. If `prefix` is false, no prefix will be added. If `prefix` is not set at all, a random prefix will be chosen. This is a security measure so the one uploading the file cannot guess the filename.

If the target directory is not set it will be created (but not recursively). If multiple files are uploaded and one upload fails, the previously processed files and created directories will be removed again.

