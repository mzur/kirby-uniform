# Usage

You first have to initialize the form in your form page controller like this:

```php
$form = uniform('contact-form',[
    'required' => [
        '_from' => 'email'
    ],
    'actions' => [
        [
            '_action' => 'email',
            'to'      => (string) $page->email(),
            'sender'  => 'info@my-domain.tld',
            'subject' => $site->title()->html() . ' - message from the contact form'
        ]
    ]
]);
```

The **first** argument is a unique ID of the form on your entire website. The **second** argument is the array of [options](options).

In this case the `_from` form field is required and validated as an email address. If the form data is correct, the `email` [action](actions) is performed, sending the data to an email address specified in `$page->email()`.

You then create a form element with the own url of the page as `action` target like this:

```php+html
<form action="<?php echo $page->url() ?>" method="post"></form>
```

Uniform by default requires the presence of a `website` field acting as a honey pot and a `_submit` button. Note the `_` at the beginning of the field `name` attribute, marking it as special field that shouldn't be altered.

Here is an example with an additional `_from` field required by the `email` action:

```php+html
<label for="email" class="required">Email</label>
<input<?php e($form->hasError('_from'), ' class="erroneous"')?> type="email" name="_from" id="email" value="<?php $form->echoValue('_from') ?>" required/>

<label class="uniform__potty" for="website">Please leave this field blank</label>
<input type="text" name="website" id="website" class="uniform__potty" />

<button type="submit" name="_submit" value="<?php echo $form->token() ?>"<?php e($form->successful(), " disabled")?>>Submit</button>
```

There are some important things happening here.

First, the `echoValue()` method is used to set the `value` of the email field. If the submission of the form has failed, this restores already set fields. So in this case you don't have to enter the email address again when the page is reloaded after submitting the form. Also, the `hasError()` method is used to mark the email field with a special class if the server-side validation failed. For more on the available methods, see [the methods section](methods).

Second, the honey pot field uses the `uniform__potty` class. If you check the `uniform.css` you'll see that it makes the field disappear visually but not in the souce code of the page, the spam-bots are accessing.

Last, the submit button uses the `token()` method to set its value. The token is submitted along with all the other data of the form and ensures that the form can only be submitted directly from the website and not e.g. with an automated script that doesn't know the token. If the honeypot check fails, a new token is generated to make it harder to guess.

The presence of the last two elements with the exact `name` attributes and the token as a value of the submit button is critical for the plugin to work correctly! Actions may require their own fields like `_from`, too.

Now you can add as many additional form fields as you like but you shouldn't use the `_` prefix for your own field names.

__Note:__ [Disable the kirby cache](https://getkirby.com/docs/developer-guide/advanced/caching#ignoring-pages) for pages where you use Uniform to make sure the form is generated dynamically.
