# Kirby Uniform Documentation

A versatile [Kirby](http://getkirby.com) plugin to handle web form actions.

Builtin actions:

- [Email](actions/email): Send the form data by email.
- [EmailSelect](actions/email-select): Choose from multiple recipients to send the form data by email.
- [Log](actions/log): Log the form data to a file.
- [Login](actions/login): Log in to the Kirby frontend.
- [SessionStore](actions/session-store): Store the form in the user's session.
- [Upload](actions/upload): Handle file uploads.
- [Webhook](actions/webhook): Send the form data as an HTTP request to a webhook.

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

!!! warning "Note"
    [Disable the Kirby cache](https://getkirby.com/docs/guide/cache) for pages where you use Uniform to make sure the form is generated dynamically.

!!! warning "Note"
    Uniform makes use of Kirby sessions. This requires a session cookie to be set.

## Questions

See the [answers](answers), [post an issue](https://github.com/mzur/kirby-uniform/issues) if you think it is a bug or create a topic in [the forum](https://forum.getkirby.com/) if you need help (be sure to mention `@mzur`).
