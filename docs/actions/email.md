# Email Action

This actions sends the form data by email. In its simplest form it just appends all form fields in `name: value` pairs as plain text. But it can use a custom [body](#body) or [template](#template) to build the email, too. You can use templates to send HTML instead of plain text emails, too.

If there is an `email` field in the form data, the action will use it as `replyTo` of the sent email and remove it from the email body. If there is a `receive_copy` field present (e.g. a checkbox) and the [receive-copy](#receive-copy) option is set, the action will send a copy of the email to the address specified in the `email` field. The subject of this copy email will get the `uniform-email-copy` prefix.

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

The email action accepts the same options than the [email function of Kirby](https://getkirby.com/docs/guide/emails). You can pass on options like `cc`, `bcc` or even `attachments`. The `body` is ignored, however, as it is dynamically generated based on the form data. Here are some special options:


### to (required)

The email address that should be the receiver of the emails. It can be dynamically chosen based on the form content with the [EmailSelectAction](email-select).

### from (required)

The email address that will be the sender of the emails. This should be some address that is associated with the website. If you host it at `example.com` the address may be `info@example.com`.

### preset
The [Kirby email preset](https://getkirby.com/docs/guide/emails#email-presets) to use as a template. It works exactly like you pass in an preset to Kirbys own email function. Uniform uses the preset values as base and merges the action parameters with them. If you have `to` and `from` defined in your preset you do not have to pass them in as parameters again.

### subject

The subject of the email. By default the `uniform-email-subject` language variable is taken. The subject supports templates, too, so you can dynamically add form data to it. A template is a name of a form field surrounded by `{{}}`. Example:

```php
'subject' => 'New request from {{email}}',
```

!!! warning "Note"
    Subject templates do not work with [array form fields](http://stackoverflow.com/a/1978788/1796523).

### template

Name of a email template to use as email body. If this option is set, the action will use the template for the email body instead of printing the `name: value` pairs as plain text. Read more on email templates in the [Kirby documentation](https://getkirby.com/docs/guide/emails#plain-text). In addition to the variables of the form data, you have access to the `$_options` array which is the options array that you passed on to the email action.

Check out the email templates of the [Uniform repo](https://github.com/mzur/kirby-uniform/tree/master/templates/emails) for examples.

!!! warning "Note"
    You cannot access form fields with the name `_data` or `_options` directly in the template as these are reserved for the additional variables provided by Uniform. Use `$_data['_data']` and `$_data['_options']` in this case.

### body

The body of the email. If not specified, the form data will be used as the body (`name: value` pairs as plain text). The body supports templates, too, so you can dynamically add form data to it. A template is a name of a form field surrounded by `{{}}`. Example:

```php
'body' => 'Dear {{name}}, we will get back to you soon!',
```
The body will only be used, if no [template](#template) is specified.

!!! warning "Note"
    Body templates do not work with [array form fields](http://stackoverflow.com/a/1978788/1796523).

### replyTo

Set a static email address as `replyTo` of the email instead of the value of the `email` form field.

### receive-copy

Set this option to `true` if you want to enable the receive copy functionality of the email action. This is useful if you use multiple email actions in a row and don't want the user to receive an email copy once _for each_ email action that is executed. If this option is not `true` no copy will be sent even if the `receive_copy` form field is present.

### escapeHtml

The form data is HTML-escaped by default. Set this option to `false` to disable escaping.
