# Email Action

This actions sends the form data by email. In its simplest form it just appends all form fields in `name: value` pairs as plain text. But it can use a [snippet](#snippet) to build the email, too. You can also define custom [email services](#service) to send e.g. HTML emails.

If there is an `email` field in the form data, the action will use it as `replyTo` of the sent email and remove it from the email body. If there is a `receive_copy` field present (e.g. a checkbox) and the [receive-copy](#receive-copy) option is set, the action will send a copy of the email to the address specified in the `email` field. The subject of this copy email will get the `uniform-email-copy` prefix.

## Example

### Controller

```php
<?php

use Uniform\Form;

return function ($site, $pages, $page)
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

    if (r::is('POST')) {
        $form->emailAction([
            'to' => 'me@example.com',
            'from' => 'info@example.com',
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

## Options

### to (required)

The email address that should be the receiver of the emails. It can be dynamically chosen based on the form content with the [EmailSelectAction](email-select).

### from (required)

The email address that will be the sender of the emails. This should be some address that is associated with the website. If you host it at `example.com` the address may be `info@example.com`.

### subject

The subject of the email. By default the `uniform-email-subject` language variable is taken. The subject supports templates, too, so you can dynamically add form data to it. A template is a name of a form field surrounded by `{}`. Example:
```php
'subject' => 'New request from {email}',
```

!!! warning "Note"
    Subject templates do not work with [array form fields](http://stackoverflow.com/a/1978788/1796523).

### snippet

Name of a snippet to use as email body. If this option is set the action will use the snippet for the email body instead of printing the `name: value` pairs as plain text. Inside the snippet you have access to the `$data` array, which is a plain associative array containing the form data, and the `$options` array which is the options array that you passed on to the email action.

Check out the `email-*` snippets of the [Uniform repo](https://github.com/mzur/kirby-uniform/tree/master/snippets) for examples.

### replyTo

Set a static email address as `replyTo` of the email instead of the value of the `email` form field.

### service

Name of an [email service](https://getkirby.com/docs/developer-guide/advanced/emails) to use. The default service is `mail`. For other services, make sure to provide the [service-options](#service-options) option as well.

### service-options

An array of options to pass along to the email service. If you use the [SES service](https://getkirby.com/docs/developer-guide/advanced/emails#amazon-ses), for example, you need to provide the `key`, `secret` and `host` in this array. This will be the `$email->options` array you can access in a custom email service.

### receive-copy

Set this option to `true` if you want to enable the receive copy functionality of the email action. This is useful if you use multiple email actions in a row and don't want the user to receive an email copy once _for each_ email action that is executed. If this option is not `true` no copy will be sent even if the `receive_copy` form field is present.
