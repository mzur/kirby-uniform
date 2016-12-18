# Kirby Uniform

A versatile [Kirby 2](http://getkirby.com) plugin to handle web form actions.

[![Documentation Status](https://readthedocs.org/projects/kirby-uniform/badge/?version=latest)](http://kirby-uniform.readthedocs.io/en/latest/?badge=latest) [![Build Status](https://travis-ci.org/mzur/kirby-uniform.svg?branch=v3)](https://travis-ci.org/mzur/kirby-uniform)

Builtin actions:

- [email](http://kirby-uniform.readthedocs.io/en/latest/actions/email/): Send the form data by email.
- [email-select](http://kirby-uniform.readthedocs.io/en/latest/actions/email-select/): Choose from multiple recipients to send the form data by email.
- [log](http://kirby-uniform.readthedocs.io/en/latest/actions/log/): Log the form data to a file.
- [login](http://kirby-uniform.readthedocs.io/en/latest/actions/login/): Log in to the Kirby frontend.
- [webhook](http://kirby-uniform.readthedocs.io/en/latest/actions/webhook/): Send the form data as an HTTP request to a webhook.

## Quick example

Controller:

```php
<?php

use Uniform\Form;

return function ($site, $pages, $page) {
   $form = new Form([
      'email' => [
         'rules' => ['required', 'email'],
         'message' => 'Email is required',
      ],
      'message' => [],
   ]);

   if (r::is('POST')) {
      $form->emailAction([
         'to' => $page->email(),
         'sender' => $site->email(),
      ]);
   }

   return compact('form');
};
```

Template:

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

## Installation

### Composer

Run `composer require mzur/kirby-uniform`. Then add the second `require` to the `index.php` like this:

```php
// load kirby
require(__DIR__ . DS . 'kirby' . DS . 'bootstrap.php');
require 'vendor'.DS.'autoload.php';
```

Be sure to include the new `vendor` directory in your deployment.

### Kirby CLI

Get the [Kirby CLI](https://github.com/getkirby/cli) and run `kirby plugin:install mzur/kirby-uniform`.

### Traditional

[Download](https://github.com/mzur/kirby-uniform/archive/master.zip) the repository and extract it to `site/plugins/uniform`.

## Setup

Add this to your CSS:

```css
.uniform__potty {
    position: absolute;
    top: -9999px;
    left: -9999px;
}
```

If you have a single language site you can choose the language Uniform should use in `site/config/config.php` (default is `en`):

```php
c::set('uniform.language', 'de');
```

See [here](https://github.com/mzur/kirby-uniform/tree/master/languages) for all supported languages.

## Documentation

For the full documentation head over to [Read the Docs](http://kirby-uniform.readthedocs.io).

## Questions

See the [answers](http://kirby-uniform.readthedocs.io/en/latest/answers/) in the docs, [post an issue](https://github.com/mzur/kirby-uniform/issues) if you think it is a bug or create a topic in [the forum](https://forum.getkirby.com/) if you need help (be sure to use the `uniform` tag or mention `@mzur`).

## Contributing

Contributions are always welcome!
