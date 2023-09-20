# Kirby Uniform

A versatile [Kirby](http://getkirby.com) plugin to handle web form actions.

[![Documentation Status](https://readthedocs.org/projects/kirby-uniform/badge/?version=latest)](https://kirby-uniform.readthedocs.io/en/latest/?badge=latest) [![Tests](https://github.com/mzur/kirby-uniform/actions/workflows/php.yml/badge.svg)](https://github.com/mzur/kirby-uniform/actions/workflows/php.yml) ![Kirby >=3](https://img.shields.io/badge/Kirby-%3E=3-green.svg)

This is Uniform for Kirby >=3. You can find Uniform for Kirby 2 in the [kirby-2 branch](https://github.com/mzur/kirby-uniform/tree/kirby-2).

Builtin actions:

- [Email](https://kirby-uniform.readthedocs.io/en/latest/actions/email/): Send the form data by email.
- [EmailSelect](https://kirby-uniform.readthedocs.io/en/latest/actions/email-select/): Choose from multiple recipients to send the form data by email.
- [Log](https://kirby-uniform.readthedocs.io/en/latest/actions/log/): Log the form data to a file.
- [Login](https://kirby-uniform.readthedocs.io/en/latest/actions/login/): Log in to the Kirby frontend.
- [SessionStore](https://kirby-uniform.readthedocs.io/en/latest/actions/session-store): Store the form in the user's session.
- [Upload](https://kirby-uniform.readthedocs.io/en/latest/actions/upload): Handle file uploads.
- [Webhook](https://kirby-uniform.readthedocs.io/en/latest/actions/webhook/): Send the form data as an HTTP request to a webhook.

## Quick example

Controller:

```php
<?php

use Uniform\Form;

return function ($kirby) {
   $form = new Form([
      'email' => [
         'rules' => ['required', 'email'],
         'message' => 'Email is required',
      ],
      'message' => [],
   ]);

   if ($kirby->request()->is('POST')) {
      $form->emailAction([
         'to' => 'me@example.com',
         'from' => 'info@example.com',
      ])->done();
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

Install Uniform via Composer: `composer require mzur/kirby-uniform`

Or [download](https://github.com/mzur/kirby-uniform/archive/master.zip) the repository and extract it to `site/plugins/uniform`.

## Setup

Add this to your CSS:

```css
.uniform__potty {
    position: absolute;
    left: -9999px;
}
```

**Note:** [Disable the Kirby cache](https://getkirby.com/docs/guide/cache) for pages where you use Uniform to make sure the form is generated dynamically.

## Documentation

For the full documentation head over to [Read the Docs](https://kirby-uniform.readthedocs.io).

## Questions

See the [answers](https://kirby-uniform.readthedocs.io/en/latest/answers/) in the docs, [post an issue](https://github.com/mzur/kirby-uniform/issues) if you think it is a bug or create a topic in [the forum](https://forum.getkirby.com/) if you need help.

## Contributing

Contributions are always welcome!

## Donations

Since some people insist on sending me money for this (free) plugin you can do this [here](https://www.paypal.me/mzur/10eur).
