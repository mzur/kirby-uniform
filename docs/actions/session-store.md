# SessionStore Action

This action stores the form in the user's session object so that it can be accessed in subsequent requests - e.g. after a redirect.

## Example

This example stores the the form data in the session and redirects the user to a confirmation page where the user's email address is displayed.

### Controller

```php
<?php

use Uniform\Form;

return function ($kirby)
{
    $form = new Form([
        'email' => [
            'rules' => ['required', 'email'],
            'message' => 'Please enter a valid email address',
        ],
    ]);

    if ($kirby->request()->is('POST')) {
        $form->sessionStoreAction(['name' => 'user-form']);

        if ($form->success()) {
            go('confirmation');
        }
    }

    return compact('form');
};
```

### Template

```html+php
<form method="POST">
    <input name="email" type="email" value="<?php echo $form->old('email') ?>">
    <?php echo honeypot_field() ?>
    <?php echo csrf_field() ?>
    <input type="submit" value="Subscribe">
</form>
```

### Confirmation Page Template

```html+php
<?php if (kirby()->session()->get('user-form')): ?>
    Thank you <?php echo kirby()->session()->get('user-form')->data('email'); ?> for subscribing.
<?php endif; ?>
```

## Options

### name

The name of the session key the form will be stored in. Defaults to `session-store`.

