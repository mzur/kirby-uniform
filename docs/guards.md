# Guards

Uniform offers several mechanisms for spam protection. Similar to actions, guards can be extended and combined. To add custom guards, create a `site/plugins/uniform-guards/uniform-guards.php` file and implement all your custom guards there. Take a look at the [`calc` guard](https://github.com/mzur/kirby-uniform/blob/v2.2.1/uniform/guards/calc.php) to see how to implement one.

## Honeypot

The honeypot guard will reject all requests where a special (visually hidden) form field was filled out. The default name of the honeypot field is `website` to attract the attention of the bots. With the `honeypot` option you can change the default name to whatever you like but you should try to make it sound interesting for the bots. Example:

```php+html
uniform('contact-form', [
   'honeypot' => 'fill-me',
   'actions' => [
      // ...
   ]
]);
//...
<input type="text" name="fill-me" id="fill-me" class="uniform__potty" />
```

The honeypot guard is activated in the default setup of Uniform.

## Calc

The calc guard will reject all requests where a simple arithmetic problem is not solved correctly. You can set it up like this:

```php+html
uniform('contact-form', [
   'guard' => 'calc',
   'actions' => [
      // ...
   ]
]);
//...
<label for="_captcha" class="required">Please calculate <?php echo uniform_captcha($form) ?></label>
<input<?php e($form->hasError('_captcha'), ' class="erroneous"')?> type="number" name="_captcha" id="_captcha" required/>
```

The `uniform_captcha()` function will generate a new arithmetic problem for the form and store the correct result in a session variable each time it is called.

## reCAPTCHA

Head over to the [repository of the reCAPTCHA guard](https://github.com/fetzi/kirby-uniform-recaptcha) for the documentation. Thanks @fetzi!
