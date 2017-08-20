# Kirby Uniform Documentation

A versatile [Kirby 2](http://getkirby.com) plugin to handle web form actions.

Builtin actions:

- [Email](actions/email): Send the form data by email.
- [EmailSelect](actions/email-select): Choose from multiple recipients to send the form data by email.
- [Log](actions/log): Log the form data to a file.
- [Login](actions/login): Log in to the Kirby frontend.
- [SessionStore](actions/session-store): Store the form in the user's session.
- [Upload](actions/upload): Handle file uploads.
- [Webhook](actions/webhook): Send the form data as an HTTP request to a webhook.

## Installation

Uniform requires PHP 5.5 or higher. It is highly recommended to use a version under [active support](https://php.net/supported-versions.php).

### Kirby CLI

Get the [Kirby CLI](https://github.com/getkirby/cli) and run `kirby plugin:install mzur/kirby-uniform`.

### Traditional

[Download](https://github.com/mzur/kirby-uniform/archive/master.zip) the repository and extract it to `site/plugins/uniform`.

### Composer

Run `composer require mzur/kirby-uniform`. Then create the file `site/plugins/autoload.php` with the content:

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

!!! warning "Note"
    [Disable the Kirby cache](https://getkirby.com/docs/developer-guide/advanced/caching#ignoring-pages) for pages where you use Uniform to make sure the form is generated dynamically.

## Questions

See the [answers](answers), [post an issue](https://github.com/mzur/kirby-uniform/issues) if you think it is a bug or create a topic in [the forum](https://forum.getkirby.com/) if you need help (be sure to use the `uniform` tag or mention `@mzur`).
