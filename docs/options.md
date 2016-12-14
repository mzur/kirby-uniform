# Options

These are the options of the options array. You have to specify at least one action. Everything else is, well, optional.

## guard

With this option you can configure which [guards](guards) you wish to use. By default it is set to `honeypot` which will activate the [honeypot guard](guards#honeypot).

You can choose a different guard:

```php
'guard' => 'calc'
```

Combine guards:

```php
'guard' => ['honeypot', 'calc']
```

Or disable the spam protection:

```php
'guard' => ''
```

## required

Associative array of required form fields. The keys of the array are the `name` attributes of the required fields. The values of the entries are optional [validator function](http://getkirby.com/docs/cheatsheet#validators) names. Example:

```php
['_from' => 'email']
```

So the `_from` field is required and validated by the [`v::email`](http://getkirby.com/docs/cheatsheet/validators/email) function. Note, that this works only with validator functions that validate single strings. If a field is required but should not be validated, leave the validator function name empty.

If a required field is missing, the form won't execute any actions.

## validate

Like [`required`](#required) but execution of the actions will *not* fail if one of these fields is missing. Only if one of these fields contains invalid data the actions will not be performed.

## actions

An array of [action](actions) arrays. Each of these action arrays needs to contain at least an `_action` key with the name of the action that should be performed as value. It can contain arbitrary additional data for the action function. Example:

```php
[
    '_action' => 'email',
    'to'      => (string) $page->email(),
    'subject' => $site->title()->html() . ' - message from the contact form'
]
```

This way even the same actions can be performed multiple times when a form is submitted (like sending the form data to multiple email addresses).
