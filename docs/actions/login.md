# Login Action

This action provides a simple way of logging in to the Kirby frontend. For this the `username` and `password` fields need to be present in the form. A spam protection guard should not be necessary in most cases.

## Example

### Controller

```php
<?php

use Uniform\Form;

return function ($kirby)
{
    $form = new Form([
        'username' => [
            'rules' => ['required'],
            'message' => 'Please enter your username',
        ],
        'password' => [
            'rules' => ['required', 'min' => 8],
            'message' => 'Please enter your password',
        ],
    ]);

    if ($kirby->request()->is('POST')) {
        $form->withoutGuards()
            ->loginAction();

        if ($form->success()) {
            // redirect to internal page
        }
    }

    return compact('form');
};
```

### Template

```html+php
<form method="POST">
    <input name="username" type="text" value="<?php echo $form->old('username') ?>">
    <input name="password" type="password">
    <?php echo csrf_field() ?>
    <input type="submit" value="Login">
</form>
```

!!! danger "Note"
    Never return a previously entered password with `$form->old('password')`!

## Options

### user-field

Set the name for the username form field. Default is `username`. The error messages of the login action will be stored for this form field.

### password-field

Set the name for the password form field. Default is `password`.
