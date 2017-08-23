# Usage

Uniform is initialized in the page controller like this:

```php
<?php

use Uniform\Form;

return function ($site, $pages, $page)
{
    $form = new Form([
        'email' => [
            'rules' => ['required', 'email'],
            'message' => 'Please enter a valid email address',
        ],
        'message' => [
            'rules' => ['required'],
            'message' => 'Please enter a message',
        ],
    ]);

    if (r::is('POST')) {
        $form->emailAction([
            'to' => 'me@example.com',
            'from' => 'info@example.com',
        ]);
    }

    return compact('form');
};
```

First, you create a new instance of `Uniform\Form`. The constructor argument is an array of [validation rules](#validation-rules). In a regular `GET` request the `$form` object can be used to display old form data or errors from a previously attempted submission of the form (see [Template](#template)). When the form is submitted in a `POST` request, Uniform does several things:

1. Validate the [CSRF token](https://en.wikipedia.org/wiki/Cross-site_request_forgery)
2. Validate the form data based on the validation rules
3. Call guards for spam protection
4. Call actions to process the form data

If any of these steps fail, Uniform will immediately redirect back to the form page and skip the subsequent steps ([PRG pattern](https://en.wiki2.org/wiki/Post/Redirect/Get+Milds)). For convenience, steps 1-3 are implicitly called when the first action is performed. So in the controller code above the form will check the CSRF token, validate the form fields and call the default [HoneypotGuard](guards/honeypot) although we only told it to execute the [EmailAction](actions/email).

## Validation rules

The constructor argument of `Uniform\Form` is an array of validation rules and error messages for each form field. To validate the form data Kirby's [invalid helper function](https://getkirby.com/docs/cheatsheet/helpers/invalid) is used, so you can use all [validators](https://getkirby.com/docs/cheatsheet#validators) that are available for the invalid function (or [implement your own](https://getkirby.com/docs/developer-guide/objects/validators)). If the form field validation failed, the individual error messages can be fetched through the [error method](methods#errorkey) of the `$form` object.

Besides validation rules and error messages, the constructor array also defines which form fields Uniform should use in the first place. If only `email` and `message` are defined but the form also has a `name` field, it will be ignored because it was not defined in the constructor array. You can define form fields that should not be included in the validation with an empty validation array like this:

```php
$form = new Form([
    'email' => [
        'rules' => ['required', 'email'],
        'message' => 'Please enter a valid email address',
    ],
    'message' => [
        'rules' => ['required'],
        'message' => 'Please enter a message',
    ],
    'name' => [],
]);
```

Form fields that should not be returned to the user in case of some validation error can be excluded from flashing like this:

```php
$form = new Form([
    'username' => [
        'rules' => ['required'],
        'message' => 'Please enter your username',
    ],
    'password' => [
        'rules' => ['required'],
        'message' => 'Please enter your password',
        'flash' => false,
    ],
]);
```

As of Kirby 2.5 you are able to specify an error message for each individual validation rule like this:

```php
$form = new Form([
    'email' => [
        'rules' => ['required', 'email'],
        'message' => ['The email is required', 'Please enter a valid email address'],
    ],
]);
```

## Guards

Once the form fields were successfully validated the form data is passed on to the [guards](guards/guards) which are mechanisms for spam protection. If you don't explicitly specify a guard, Uniform will use the default [HoneypotGuard](guards/honeypot). Another guard (like the [CalcGuard](guards/calc)) can be specified before the actions like this:

```php
if (r::is('POST')) {
    $form->calcGuard(['field' => 'result'])
        ->emailAction([
            'to' => 'me@example.com',
            'from' => 'info@example.com',
        ]);
}
```

As you can see, guards may receive an options array as argument. Here we use it to specify a custom form field name for the user input. You can also see the fluid interface of calling guards and actions where the individual methods are chained. This can be used to call multiple different guards or actions in a row:

```php
if (r::is('POST')) {
    $form->honeypotGuard()
        ->calcGuard()
        ->emailAction([
            'to' => 'me@example.com',
            'from' => 'info@example.com',
        ]);
}
```

If you don't want to execute any guards you can disable them before the actions are executed:

```php
if (r::is('POST')) {
    $form->withoutGuards()
        ->emailAction([
            'to' => 'me@example.com',
            'from' => 'info@example.com',
        ]);
}
```

## Actions

When Uniform is convinced that the submitted form data is no spam, the [actions](actions/actions) are executed. Similar to the guards you can choose from several built-in actions but you can easily [write your own](actions/actions#custom-actions), too. Most actions require some options in the options array. Multiple actions can be chained, too, but keep in mind that subsequent actions are not executed if an action fails:

```php
if (r::is('POST')) {
    $form->emailAction([
            'to' => 'me@example.com',
            'from' => 'info@example.com',
        ])
        ->emailAction([
            'to' => 'you@example.com',
            'from' => 'info@example.com',
        ])
        ->logAction([
            'file' => kirby()->roots()->site().'/messages.log'
        ]);
}
```

## Template

Uniform provides a few methods to enhance the plain HTML form in the template. First of all you have to make sure that you always include the `csrf_field` helper function which creates a hidden form field containing the CSRF token. If the default [HoneypotGuard](guards/honeypot) is used, you have to include the `honeypot_field` helper, too.

Whenever the form validation failed or anything else went wrong, the user would loose all the form data that was already entered. Of course we don't want that so Uniform flashes all form data to the session in this case. The flashed data can be accessed through the [old method](methods#oldkey) to re-populate the form data as you can see in the example below:

```html+php
<form action="<?php echo $page->url() ?>" method="POST">
   <input name="email" type="email" value="<?php echo $form->old('email'); ?>">
   <textarea name="message"><?php echo $form->old('message'); ?></textarea>
   <?php echo csrf_field(); ?>
   <?php echo honeypot_field(); ?>
   <input type="submit" value="Submit">
</form>
<?php if ($form->success()): ?>
   Success!
<?php else: ?>
   <?php snippet('uniform/errors', ['form' => $form]); ?>
<?php endif; ?>
```

Of course we also want to display error messages in case anything went wrong. Uniform provides a snippet that simply displays all error messages. You can also write your own snippet or display the error messages for each field individually like this:

```html+php
<input name="email" type="email" value="<?php echo $form->old('email'); ?>">
<?php if ($form->error('email')): ?>
    <p class="error-text"><?php echo implode('<br>', $form->error('email')) ?></p>
<?php endif; ?>
```

Keep in mind that the error method returns an array since there may be multiple error messages for a single form field.
