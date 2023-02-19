# Kirby Flash

[![Tests](https://github.com/mzur/kirby-flash/actions/workflows/php.yml/badge.svg)](https://github.com/mzur/kirby-flash/actions/workflows/php.yml)

**This is a fork of [jevets\kirby-flash](https://github.com/jevets/kirby-flash).**

Allows you to "flash" data to the session, which will be available via the session on the next page load, after which the data is removed from the session.

Very useful for:

- Saving submitted form data for form validation, specifically for the [Post/Redirect/Get](https://en.wikipedia.org/wiki/Post/Redirect/Get) design pattern
- Displaying Success or Error messages after a page reload

## Quick Example

```php
flash('thanks_message', 'Thank you for contacting us!');
```

Elsewhere...

```php
<?php if (flash('thanks_message')): ?>
    <?php echo flash('thanks_message') ?>
<?php endif ?>
```

## Installation

Install with composer:

```bash
# Kirby v2
composer require mzur/kirby-flash:^1.0
# Kirby v3
composer require mzur/kirby-flash:^2.0
```

## Usage

### Set data
```php
flash('key', 'value');

flash('messages.success', ['Thanks for your feedback!']);
flash('messages.errors', ['Email is a required field']);
flash('username', 'jimihendrix');
```

### Get data
```php
$value = flash('key');

$success_messages = flash('messages.success'); // Array( 0 => 'Thanks for your feedback!' )
$username = flash('username'); // "jimihendrix"
```

## Examples

```php
flash('messages.errors', [
    'Email is required',
    'Password is required',
]);

flash('messages.errors'); // Array( 0 => 'Email is required', 1 => 'Password is required' )
```

```php
<?php if (count(flash('messages.errors')) > 0): ?>
<div class="alert alert-error">
    <?php foreach (flash('messages.errors') as $message): ?>
        <div><?= html($message) ?></div>
    <?php endforeach ?>
</div>
<?php endif ?>
```

## `flash()` Helper Function

This class loads a global helper function: `flash($key, $value = '')`.

The `flash()` function is only defined if it doesn't already exist, so you could define your own `flash()` function if necessary. Most of the time, you'll probably just use `flash()` in your application.

When **called with one parameter**, the value is returned. If the key doesn't exist, then you'll get back an empty string.

```php
flash('my_key');
```

When **called with two parameters**, the `$value` is set for the `$key`.

If `$key` already exists, `$value` will replace the existing `$key`'s value.

```php
flash('my_other_key', 'Some Value');
flash('my_other_key', 'Some Other Value');

flash('my_other_key'); // "Some Other Value"
```

You may store any kind of data you want in the session. As another example, you could store multiple form validation error messages as an array in a single key.

```php
flash('messages.errors', ['Email is required.', 'Phone is required.']);

flash('messages.errors'); // Array( 0 => 'Email is required.', 1 => 'Phone is required.' )
```

## Flash for current page load only

Sometimes it can be useful to flash a message for the current page load, not the next one. This use case
arises when a message should be shown in a response to the same request, rather than a redirect.

The `flash()` helper method can be called with an optional third parameter, a boolean to toggle whether to keep the flashed variable only for the current request. The default value of this parameter is `false` which will keep the flashed variable for the next request.

```php
flash('message', 'Message for redirect'); // Keep for next request
flash('message', 'Message for this response', true); // Keep only for current request

## Session Key

By default Flash stores data under the session key `_flash`.

So you *could* access flash data like `$kirby->session()->get('_flash')` if you wanted to.

### Changing the Session Key

Use the static method to change the flash key. (You should probably do this early on in your app, probably in `index.php` or `site.php`.)

```php
Jevets\Kirby\Flash::setSessionKey('_my_custom_key');
```

### Getting the Session Key

```php
Jevets\Kirby\Flash::sessionKey();
```


## Contributing

Feel free to send a pull request!

## Issues/Bugs

Please use the [GitHub issue tracker](https://github.com/mzur/kirby-flash/issues).
