# Honeytime Guard

The honeytime guard uses a hidden form field to determine if the form was submitted by a bot. The form field contains an encrypted timestamp that cannot be forged by bots. When the form is submitted, the timestamp is checked against the current time. If the form was submitted too quickly, it is rejected.

This guard requires an encryption key. You can generate a new encryption key with the following command: `head -c 32 /dev/urandom | base64`. Then set this key with a `base64:` prefix in your `site/config/config.php` file. Example:

```php
return [
    'uniform.honeytime.key' => 'base64:m9pAO+r/7SbyT0lfWTYM4+iV9BwZiT3ouxBurDoNAXs=',
];
```

The special form field can be added to a form with the `honeytime_field` helper function like this:

```html+php
<form action="<?php echo $page->url() ?>" method="POST">
   <!-- ... -->
   <?php echo honeytime_field(c::get('uniform.honeytime.key')); ?>
   <!-- ... -->
</form>
```

When you use the honeytime guard in the controller, you also have to specify the key:

```php
$form->honeytimeGuard([
    'key' => c::get('uniform.honeytime.key'),
]);
```

You can also configure another field name like this:

Controller:
```php
$form->honeytimeGuard([
    'key' => c::get('uniform.honeytime.key'),
    'field' => 'url',
]);
```

Template:
```html+php
<?php echo honeytime_field(c::get('uniform.honeytime.key'). 'url'); ?>
```

The honeytime field value will not be available in actions even if you explicitly defined it in the constructor array of validation rules of the `Form` class (which you don't have to).

## Options

### key (required)

The encryption key. You can generate one with the command `head -c 32 /dev/urandom | base64` and then append a `base64:` prefix.

### seconds

If the form is submitted faster than this time, it will be rejected.

Default: `10`

### field

Name of the form field to use as a honeytime.

Default: `'uniform-honeytime'`
