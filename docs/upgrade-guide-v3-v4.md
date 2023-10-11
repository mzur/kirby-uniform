# Upgrade Guide v3->v4

There are a few breaking changes between Uniform v3 and v4. Mainly these were introduced to better fit new features and principles of Kirby 3. This guide lists all changes you have to make to your existing Uniform v3 code to make it work with Uniform v4 and Kirby 3.

## Controllers

The `R` class of the Kirby toolkit is no longer available. Request information can be fetched from the main Kirby object. The object can be injected as argument of a controller function or you can use the `kirby()` helper.

Uniform v3 / Kirby 2:

```php
<?php

use Uniform\Form;

return function ()
{
    $form = new Form([
        // ...
    ]);

    if (r::is('POST')) {
        // run actions
    }

    return compact('form');
};
```

Uniform v4 / Kirby 3:

```php
<?php

use Uniform\Form;

return function ($kirby)
{
    $form = new Form([
        // ...
    ]);

    if ($kirby->request()->is('POST')) {
        // run actions
    }

    return compact('form');
};
```

## Email subject templates

String templating in Kirby 3 changed slightly. Variables in string templates are now identified by `{{ }}` instead of `{ }`.

Uniform v3 / Kirby 2:

```php
$form->emailAction([
    // ...
    'subject' => 'New request from {firstname} {lastname}',
]);
```

Uniform v4 / Kirby 3:

```php
$form->emailAction([
    // ...
    'subject' => 'New request from {{firstname}} {{lastname}}',
]);
```

Also, variables in string templates that have no replacement will now be removed instead of left untouched.

Uniform v3 / Kirby 2:

`'New request from {missing}'` becomes `'New request from {missing}'`.

Uniform v4 / Kirby 3:

`'New request from {{missing}}'` becomes `'New request from '`.

## Email services

Kirby 3 no longer uses email services. All emails are sent using PHPMailer. You can [configure the transport settings](https://getkirby.com/docs/guide/emails#transport-configuration) in Kirby's config file. Remove `service` and `service-options` from the options array of the [EmailAction](actions/email) wherever you used them.

## Email snippets/templates

The email snippets of the [EmailAction](actions/email) are now replaced by Kirby's native [email templates](https://getkirby.com/docs/guide/emails#plain-text). Just move your old email snippets to the email template directory and rename the key in the email action options array from `snippet` to `template`.

Form fields are now directly accessible in email templates, as shown in the Kirby documentation. However, the old `$data` and `$options` arrays are still there as `$_data` and `$_options` to provide an easier upgrade path.

You can update your email templates for better readability, though.

Uniform v3 / Kirby 2:

```html+php
This is a message from <?php echo $data['firstname'] ?> <?php echo $data['lastname'] ?>.
```

Uniform v4 / Kirby 3:

```html+php
This is a message from <?= $firstname ?> <?= $lastname ?>.
```

Of course, defining and using both a `text` and `html` email template as shown in the Kirby documentation just works.

The default email snippets provided by Uniform are now available as the `uniform-default` and `uniform-table` email templates.

## Log snippets/templates

To be consistent with the use of email templates instead of snippets, the [LogAction](actions/log) now uses templates instead of snippets, too. Just move your log snippets to the templates directory and rename the key in the log action options array from `snippet` to `template`.
