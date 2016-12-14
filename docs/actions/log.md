# Log Action

This action simply puts the form data of every successful form submission to a log file.

Example action array:

```php
[
   '_action' => 'log',
   'file'    => './reservation-form.log'
]
```

Example log entry:

```
[2015-04-11T09:16:18+00:00] 127.0.0.1 Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:37.0) Gecko/20100101 Firefox/37.0
name: Joe User
_from: joe@user.com
message: This is a test submission.
```

## Options

### file (required)

The path to the file the log should be written to. Every new entry is appended to this file. If you use a relative path (`./`) it will be relative to the directory of Kirby's `index.php`. If the file doesn't exist, it will be created **but it will fail if the directory doesn't exist** so the directory has to be created manually.

### snippet

Name of a snippet to use for each log entry. In the snippet, the `$form` and `$actionOptions` arrays are available. The `$form` array contains all form fields and the `$actionOptions` array is the action array of this log action. If no snippet is specified, the default entry format is used (see above).
