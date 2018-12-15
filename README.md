# Kirby Uniform

A versatile [Kirby 2](http://getkirby.com) plugin to handle web form actions.

[![Documentation Status](https://readthedocs.org/projects/kirby-uniform/badge/?version=kirby-2)](https://kirby-uniform.readthedocs.io/en/kirby-2/?badge=kirby-2) [![Build Status](https://travis-ci.org/mzur/kirby-uniform.svg?branch=kirby-2)](https://travis-ci.org/mzur/kirby-uniform) ![Kirby 2](https://img.shields.io/badge/Kirby-2-green.svg) [![saythanks](https://img.shields.io/badge/say-thanks-blue.svg)](https://saythanks.io/to/mzur)

This is Uniform v3. For Uniform v2.3 head over to the [v2 branch](https://github.com/mzur/kirby-uniform/tree/v2).

Builtin actions:

- [Email](https://kirby-uniform.readthedocs.io/en/kirby-2/actions/email/): Send the form data by email.
- [EmailSelect](https://kirby-uniform.readthedocs.io/en/kirby-2/actions/email-select/): Choose from multiple recipients to send the form data by email.
- [Log](https://kirby-uniform.readthedocs.io/en/kirby-2/actions/log/): Log the form data to a file.
- [Login](https://kirby-uniform.readthedocs.io/en/kirby-2/actions/login/): Log in to the Kirby frontend.
- [SessionStore](https://kirby-uniform.readthedocs.io/en/kirby-2/actions/session-store): Store the form in the user's session.
- [Upload](https://kirby-uniform.readthedocs.io/en/kirby-2/actions/upload): Handle file uploads.
- [Webhook](https://kirby-uniform.readthedocs.io/en/kirby-2/actions/webhook/): Send the form data as an HTTP request to a webhook.

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
         'to' => 'me@example.com',
         'from' => 'info@example.com',
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

Uniform requires PHP 5.6 or higher. It is highly recommended to use a version under [active support](https://php.net/supported-versions.php).

### Kirby CLI

Get the [Kirby CLI](https://github.com/getkirby/cli) and run `kirby plugin:install mzur/kirby-uniform`.

### Traditional

[Download](https://github.com/mzur/kirby-uniform/archive/master.zip) the repository and extract it to `site/plugins/uniform`.

### Composer

Run `composer require mzur/kirby-uniform:^3.0`. Then create the file `site/plugins/autoload.php` with the content:

```php
<?php

require kirby()->roots()->index().DS.'vendor'.DS.'autoload.php';
```

Be sure to include the new `vendor` directory in your deployment.

## Setup

Add this to your CSS:

```css
.uniform__potty {
    position: absolute;
    left: -9999px;
}
```

If you have a single language site you can choose the language Uniform should use in `site/config/config.php` (default is `en`):

```php
c::set('uniform.language', 'de');
```

See [here](https://github.com/mzur/kirby-uniform/tree/master/languages) for all supported languages.

**Note:** [Disable the Kirby cache](https://getkirby.com/docs/developer-guide/advanced/caching#ignoring-pages) for pages where you use Uniform to make sure the form is generated dynamically.

## Documentation

For the full documentation head over to [Read the Docs](https://kirby-uniform.readthedocs.io/en/kirby-2).

## Questions

See the [answers](https://kirby-uniform.readthedocs.io/en/kirby-2/answers/) in the docs, [post an issue](https://github.com/mzur/kirby-uniform/issues) if you think it is a bug or create a topic in [the forum](https://forum.getkirby.com/) if you need help (be sure to use the `uniform` tag or mention `@mzur`).

## Contributing

Contributions are always welcome!

## Donations

Since some people insist on sending me money for this (free) plugin you can do this [here](https://www.paypal.me/mzur/10eur). You can also say [thank you](https://saythanks.io/to/mzur).
