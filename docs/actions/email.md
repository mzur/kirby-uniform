# Email Action

This action can send the form data by email. In its simplest form it just appends all form fields in `name: value` pairs as plain text. But it can use a snippet to build the email, too. You can also define custom email services for the Kirby Toolkit [email function](http://getkirby.com/docs/cheatsheet/helpers/email) to send e.g. HTML emails.

If you like to use email templates, put those snippets from the `snippets` directory of this repo that you like to use to `site/snippets/` (or create your own snippets).

The action requires a `_from` form field providing the email address for the `replyTo` field of the email that will be sent. Make sure not to use `_` as a prefix of the form field names containing the data, else they won't appear in the default plain text email being sent.

If there is a `_receive_copy` field present in the form data (e.g. from a checkbox, see the [extended example](examples/extended)), the sender's email address (`_from`) will receive a copy, too. The subject of this copy email will get the `uniform-email-copy` prefix.

## Options

Here is a full list of options of the email action that can be included in the action array.

### to (required)

The email address the form data should be sent to.

### sender (required)

The email address used for the `from` field of the email that will be sent. This should be the address of your website, e.g. `info@your-domain.tld`. Some spam filters will cause problems if they discover an email with a `from` field that doesn't match the server of it's origin.

### subject

The custom subject of the email to be sent by the form. If none is given, `uniform-email-subject` is chosen from the language file.

The subject can contain form data, too. For example if the subject should contain the value of a form field named `number-persons`, create a subject like this:

```php
'subject' => 'New reservation: {number-persons} persons!'
```

This does not work with [array form fields](http://stackoverflow.com/a/1978788/1796523).

### replyTo

Set a fixed email address as `replyTo` of the sent email instead of the default address of the `_from` form field.

### snippet

The name of the email snippet to use from the `site/snippets/` directory of your site. See the `snippets` directory of this repo for example snippets. If you like to write your own snippet, you can use the `$form` array (it's a simple array, not the form object!) containing all the data of the form field including the 'special' properties like `_subject` that all start with a `_`, as well as the `$options` array containing all the valid options of the action array (the parsed dynamic subject for example).

### service

The name of the email service to use; default is `mail`. If you use another email service, make sure to provide the [`service-options`](#service-options) as well. You can implement custom services similar to custom actions in a `site/plugins/email-services/email-services.php` file.

### service-options

An array of options to pass along to the email service. This will be the `$email->options` array you can access in a custom email service. Or if you use the `amazon` service, for example, you need to provide the `key`, `secret` and `host` in this array.

### receive-copy

Set to `false` to disable the receive copy functionality of this email action. This is useful if you use multiple email actions in a row and don't want the user to receive an email copy once _for each_ action. Default is `true`.

### params

Here you can add data that should be passed on to the email snippet. In the snippet, it can be accessed as `$options['params']`.
