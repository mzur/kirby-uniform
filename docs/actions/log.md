# Log Action

This action appends the form data and some information on the submitter to a log file.

## Example

### Controller

```php
<?php

use Uniform\Form;

return function ($kirby)
{
    $form = new Form([
        'email' => [
            'rules' => ['required', 'email'],
            'message' => 'Email is required',
        ],
        'message' => [
            'rules' => ['required'],
            'message' => 'Message is required',
        ],
    ]);

    if ($kirby->request()->is('POST')) {
        $form->logAction([
            'file' => kirby()->roots()->site().'/messages.log',
        ]);
    }

    return compact('form');
};
```

### Template

```html+php
<form method="POST">
    <input name="email" type="email" value="<?php echo $form->old('email') ?>">
    <textarea name="message"><?php echo $form->old('message') ?></textarea>
    <?php echo csrf_field() ?>
    <?php echo honeypot_field() ?>
    <input type="submit" value="Submit">
</form>
```

### Log entry

```
[2016-12-20T09:16:18+00:00] 127.0.0.1 Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:37.0) Gecko/20100101 Firefox/51.0
email: joe@user.com
message: This is a test submission.
```

## Options

### file (required)

Path to the log file where the form data should be appended to. The file will be created if it doesn't exist.

!!! warning "Note"
    The action will fail if the parent directory of the log file does not exist.

### template

By default the action lists all form fields with their values along with some information on the submitter of the form in each log entry (see [above](log-entry)). To customize the format you can also supply the name of a template in this option. Inside the template you have access to the `$data` array, which is a plain associative array containing the form data, and the `$options` array which is the options array that you passed on to the log action.

Uniform ships with the `uniform/log-json` template for convenience. Use it to get a JSON logfile like this:

```
{"timestamp":"2016-12-20T09:16:18+00:00","ip":"127.0.0.1","userAgent":"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:37.0) Gecko/20100101 Firefox/51.0","email":"joe@user.com","message":"This is a test submission."}
```

Be sure to use a unique `file` for each request if you use the JSON template, else the log objects will be appended to the same file and the JSON will become malformed.

### escapeHtml

The form data is HTML-escaped by default. Set this option to `false` to disable escaping.
