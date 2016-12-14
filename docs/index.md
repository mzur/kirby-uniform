# Kirby Uniform Documentation

A versatile and powerful [Kirby 2](http://getkirby.com) plugin to handle web form actions.

See the [Kirby with Uniform](http://blog.the-inspired-ones.de/kirby-with-uniform) blog post for a step by step tutorial on using Uniform.

**Questions?** See the [answers](answers) in the docs, [post an issue](https://github.com/mzur/kirby-uniform/issues) if you think it is a bug or create a topic in [the forum](https://forum.getkirby.com/) if you need help (be sure to use the `uniform` tag or mention `@mzur`).

Builtin actions:

- [email](actions/email): Send the form data by email.
- [email-select](actions/email-select): Choose from multiple recipients to send the form data by email.
- [log](actions/log): Log the form data to a file.
- [login](actions/login): Log in to the Kirby frontend.
- [webhook](actions/webhook): Send the form data as a HTTP request to a webhook.

## Installation

1. Copy or link the `uniform` directory to `site/plugins/` **or** use the [Kirby CLI](https://github.com/getkirby/cli) `kirby plugin:install mzur/kirby-uniform`.

2. Add the content of `uniform.css` to your CSS.

If you have a single language site you can choose the language Uniform should use in `site/config/config.php` (default is `en`):

```php
c::set('uniform.language', 'de');
```

For a **quick-start** jump directly to the [basic example](examples/basic).
