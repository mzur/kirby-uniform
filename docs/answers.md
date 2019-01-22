# Answers

This is a collection of answers to all sorts of questions regarding Uniform that popped up over the time. If you still have questions [post an issue](https://github.com/mzur/kirby-uniform/issues) if you think it is a bug or create a topic in [the forum](https://forum.getkirby.com/) if you need help (be sure to mention `@mzur`).

## How can I send an HTML email?

You can send HTML emails by using [email templates](https://getkirby.com/docs/guide/emails#html-plain-text).

## Can Uniform be used with AJAX?

Yes, see the [AJAX example](examples/ajax).

## When I submit a correctly filled out form, all data vanishes and nothing happens. What is going wrong?

Check if the HTTP request for submitting the form is redirected. Some server configurations will redirect a request e.g. from `example.com/form` to `example.com/form/` during which all form data is lost. Try changing your form's action URL from `<?php echo $page->url() ?>` to `<?php echo $page->url() ?>/`.

## Can I use a redirect when the form is submitted successfully?

Yes, just add this to your controller code after Uniform executed the actions:

```php
if (kirby()->request()->is('POST')) {
    // execute Uniform actions

    if ($form->success()) {
        go('/my-uri');
    }
}
```

## Can I work with the submitted form data outside of Uniform snippets?

Sure, since the form data is submitted with an ordinary `POST` request you can access the value of a field with name `myfield` anywhere in your code using the [`get` Kirby helper](https://getkirby.com/docs/cheatsheet/helpers/get) `get('myfield')`. If you have access to the `$form` object, you can use the [data method](methods#datakey-value), too.

## I have multiple static forms on one page. When one fails the error messages are also displayed for the other forms. Why?

This happens because the forms share the same session storage by default. In this case you have to give each form a unique session storage. You can do that with the second parameter of the `Form` constructor:

```php
<?php

use Uniform\Form;

return function ($kirby)
{
    $contactForm = new Form([/* rules */], 'contact-form');
    $newsletterForm = new Form([/* rules */], 'newsletter-form');

    if ($kirby->request()->is('POST')) {
        if (/* contact form sent */) {
            $contactForm->emailAction([
                'to' => 'me@example.com',
                'from' => 'info@example.com',
            ]);
        } elseif (/* newsletter form sent */) {
            $newsletterForm->emailAction([
                'to' => 'me@example.com',
                'from' => 'info@example.com',
            ]);
        }
    }

    return compact('form');
};
```

## How can I have one or more forms on cached pages, that are submitted via AJAX?

When you use Uniform on cached pages, the CSRF token is also cached and may be outdated when the user retrieves it. To manually update the CSRF token, you can add an uncached route to your Kirby config file and set the token with JavaScript. Don't forget to ignore this route in the cache.

**Route:**
```
[
    'pattern' => 'gettoken',
    'method' => 'GET',
    'action'  => function() {
      return Response::json(['token' => csrf()]);
    },
]
```

**JS:**
```
var token_request = new XMLHttpRequest();
var request_path = 'gettoken';

token_request.onreadystatechange = function() {
  var token_fields = document.querySelectorAll('form.contact-form input[name="csrf_token"]');

  if (this.status === 200 && this.readyState === 4) {
    var token = JSON.parse(this.response).token;
    for(i = 0; i < token_fields.length; i++) {
      token_fields[i].value = token;
    }
  }
};

token_request.open('GET', request_path);
token_request.send();
```
