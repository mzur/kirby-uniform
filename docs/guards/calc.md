# Calc Guard

The calc guard asks the user to solve a simple arithmetic problem like "4 plus 5". The textual representation of the problem is obfuscated to make it harder to read for bots. If the problem is not solved correctly the guard rejects the request.

The required form field and the arithmetic problem can be added to a form with the `uniform_captcha` and `captcha_field` helpers like this:

```html+php
<form action="<?php echo $page->url() ?>" method="POST">
    <!-- ... -->
    <label>Please calculate <?php echo uniform_captcha() ?></label>
    <?php echo captcha_field() ?>
    <!-- ... -->
</form>
```

!!! warning "Note"
    Whenever `uniform_captcha` is called a new arithmetic problem will be generated so you should call it only once for each page!

By default the captcha field will use `captcha` as name and `uniform__captcha` as CSS class. You can configure another field name or CSS class like this:

Controller:
```php
$form->calcGuard(['field' => 'result']);
```

Template:
```php
<?php echo captcha_field('result', 'my-class'); ?>
```

The the captcha field will not be available in actions even if you explicitly defined it in the constructor array of validation rules of the `Form` class (which you don't have to). It's helpful to require the field, though:

```php
$form = new Form([
    // some other rules
    'captcha' => [
        'rules' => ['required', 'num'],
        'message' => 'Please fill in the captcha field',
    ],
]);
```

## Options

### field

Name of the captcha form field.

Default: `'captcha'`
