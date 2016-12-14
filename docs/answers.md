# Answers

This is a collection of answers to all sorts of questions regarding Uniform that popped up over the time.

## How can I send an HTML email?

How an email is sent within Kirby is defined by email services. For an HTML email you can implement a service like [this one](https://github.com/mzur/kirby-uniform/issues/7#issuecomment-68592134) and then configure the email action to [use it](actions/email#service).

## Can Uniform be used with AJAX?

Yes, [here is a tutorial](https://blog.the-inspired-ones.de/ajax-uniform).

## When I submit a correctly filled out form, all data vanishes and nothing happens. What is going wrong?

Check if the HTTP request for submitting the form is redirected. Some server configurations will redirect a request e.g. from `example.com/form` to `example.com/form/` during which all form data is lost and Uniform will reset itself. Try changing your form's action URL from `<?php echo $page->url() ?>` to `<?php echo $page->url() ?>/`.

If this doesn't help check for an empty `url()` in your CSS ([why?](https://forum.getkirby.com/t/uniform-no-validation/3431/12)).

## Can I use a redirect when the form is submitted successfully?

Yes, just add this to your controller code after the initialization of Uniform by `$form = uniform(...)`:

```php
if ($form->successful()) go('/uri');
```

## Can I work with the submitted form data outside of Uniform snippets?

Sure, since the form data is submitted with an ordinary `POST` request you can access the value of a field with name `myfield` anywhere in your code using the [`get` Kirby helper](https://getkirby.com/docs/cheatsheet/helpers/get) `get('myfield')`.
