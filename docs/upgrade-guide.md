# Upgrade Guide

There are several breaking changes between Uniform v2 and v3, mainly because of the more object oriented approach of v3 to make it more modular and testable. Nevertheless the upgrade should be straight forward since the concept of Uniform actions is still the same.

This guide describes an upgrade of the basic example of v2 to v3 first. After that a more detailed list of changes options and methods as well as some caveats is given.

## Basic Example

In v2 Uniform was initialized with the `uniform` helper like this:

```php
$form = uniform(/* id */, /* options */);
```

In v3 the form is an instance of the `Uniform\Form` class:

```php
$form = new \Uniform\Form(/* validation rules */);
```

The form does not require an ID any more. Also it is not instantiated with an options array containing `required`, `actions` etc. but with an array of validation rules. The array of validation rules basically is the combined `required` and `validate` array of the options array of v2 including validation error messages:

v2:
```php
[
    'required' => ['_from' => 'email', 'name' => ''],
    'validate' => ['amount' => 'num'],
    'actions' => /* ... */
]
```

v3:
```php
[
    '_from' => [
        'rules' => ['required', 'email'],
        'message' => 'Please enter a valid email address',
    ],
    'name' => [
        'rules' => ['required'],
        'message' => 'Please enter a name',
    ],
    'amount' => [
        'rules' => ['num'],
        'message' => 'Please enter a number',
    ],
]
```

The `guard` and `actions` of the options array are now defined through a fluid interface of method calls when the form is executed. Usually the form is only executed in `POST` requests:

v2:
```php
[
    /* ... */
    'actions' => [
        [
            '_action' => 'email',
            'to'      => 'me@example.com',
            'sender'  => 'info@my-domain.tld',
            'subject' => 'New message from the contact form'
        ]
    ]
]
```

v3:
```php
if (r::is('POST')) {
    $form->emailAction([
        'to' => 'me@example.com',
        'from' => 'info@example.com',
        'subject' => 'New message from the contact form',
    ]);
}
```

In the template the `$form->hasError` method is now `$form->error` and `$form->echoValue` is now `echo $form->old`:

v2:
```html+php
<input<?php if ($form->hasError('name')): ?> class="error"<?php endif; ?> name="name" type="text" value="<?php $form->echoValue('name') ?>">
```

v3:
```html+php
<input<?php if ($form->error('name')): ?> class="error"<?php endif; ?> name="name" type="text" value="<?php echo $form->old('name') ?>">
```

There is a helper function for the honeypot field of the [HoneypotGuard](guards/honeypot):

v2:
```html
<label class="uniform__potty" for="website">Please leave this field blank</label>
<input type="text" name="website" id="website" class="uniform__potty" />
```

v3:
```html+php
<?php echo honeypot_field() ?>
```

The CSRF token is no longer the value of the submit button but of a hidden form field that can be added with a helper function:

v2:
```html+php
<button type="submit" name="_submit" value="<?php echo $form->token() ?>">Submit</button>
```

v3:
```html+php
<?php echo csrf_field() ?>
<input type="submit" value="Submit">
```

The validation and error messages can be displayed with a snippet:

v2:
```html+php
<?php if ($form->hasMessage()): ?>
    <div class="message <?php e($form->successful(), 'success' , 'error')?>">
        <?php $form->echoMessage() ?>
    </div>
<?php endif; ?>
```

v3:
```html+php
<?php if ($form->success()): ?>
    Thank you for your message. We will get back to you soon!
<?php else: ?>
    <?php snippet('uniform/errors', ['form' => $form]) ?>
<?php endif; ?>
```

## Options

### guard

The guard is now defined through the [fluid interface of method calls](guards/guards) when the form is executed. The default guard is still the [HoneypotGuard](guards/honeypot). Guards can have options like actions now, too.

### required and validate

The required fields and validation rules are now defined during [instantiation of the form](usage#validation-rules). Required fields use the `required` validation rule.

### actions

Actions is now defined through the [fluid interface of method calls](actions/actions) when the form is executed.

## Methods

The API of the form instance has completely changed. Here is a list of the old methods of v2 and what to do in v3 instead:

### value($key)

Use [`$form->old($key)`](methods#oldkey).

### echoValue($key)

Use [`echo $form->old($key)`](methods#oldkey).

### isValue($key, $value)

Use [`$form->old($key) === $value`](methods#oldkey).

### hasError($key)

Use [`$form->error($key)`](methods#errorkey).

### isRequired($key)

There is no equivalent method to check this. The required validation is now handled like any other validation, too, and you can specify an error message for it.

### token()

Use the `csrf()` [helper function](https://getkirby.com/docs/cheatsheet/helpers/csrf) to get the token or the `csrf_field()` helper function to get a hidden form field with the token.

### id()

The form no longer needs an ID.

### options($key = null)

The form no longer as an options array.

### removeField($key)

Use [`$form->forget($key)`](methods#forgetkey).

### successful($action = false)

Use [`$form->success()`](methods#success) to check if everything was successful. There are no individual success states of the actions any more.

### message($action = false)

Use [`$form->error($key)`](methods#errorkey) to check if there is an error message for a field, guard or action. The guards and actions usually have their class name as key. Some guards (like the [CalcGuard](guards/calc)) have a specific form field as key.

### hasMessage($action = false)

Use [`echo $form->error($key)`](methods#errorkey).

## Actions

### Email

The `sender` option is now `from`. The `_from` form field is now `email` and `_receive_copy` is `receive_copy`. There is no explicit `params` option any more. Everything that you pass into the options array will be available in the snippet, too.

### EmailSelect

Analogously to the EmailAction, the `sender` option is now `from`.

### Login

There is no `redirect` option any more. Instead, redirect manually if `$form->success()` is `true`.

### Webhook

You can now specify the `only` **and** `except` option together.

## Caveats

You have to specify all form fields in the validation array, even if they are not required and should not be validated. Only form fields present in the validation array are processed in the actions.

Uniform now works with the [PRG pattern](https://en.wiki2.org/wiki/Post/Redirect/Get+Milds) by default.

There are no longer success messages for actions. Instead you can [specify a single message in the template](examples/basic#template).
