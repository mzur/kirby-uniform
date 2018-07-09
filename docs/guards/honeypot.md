# Honeypot Guard

The honeypot guard uses a camouflaged form field to determine if the form was submitted by a bot. The form field is visually hidden for humans but looks normal for bots. By default it has the name `website` which should be particularly attractive to bots. Whenever the form field contains any content, the guard will reject the request.

The special form field can be added to a form with the `honeypot_field` helper function like this:

```html+php
<form action="<?php echo $page->url() ?>" method="POST">
   <!-- ... -->
   <?php echo honeypot_field(); ?>
   <!-- ... -->
</form>
```

It uses the `uniform__potty` CSS class to be visually hidden. You already should have added this class to your CSS during [setup](/#setup). You can configure another field name or CSS class like this:

Controller:
```php
$form->honeypotGuard(['field' => 'url']);
```

Template:
```html+php
<?php echo honeypot_field('url', 'my-class'); ?>
```

The honeypot field will not be available in actions even if you explicitly defined it in the constructor array of validation rules of the `Form` class (which you don't have to).

## Options

### field

Name of the form field to use as a honeypot.

Default: `'website'`
