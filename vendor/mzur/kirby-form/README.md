# Kirby Form

[![Build Status](https://travis-ci.org/mzur/kirby-form.svg?branch=master)](https://travis-ci.org/mzur/kirby-form)

**This is a fork of [jevets\kirby-form](https://github.com/jevets/kirby-form).**

A helper library for working with Kirby forms, using the [Post/Redirect/Get](https://en.wikipedia.org/wiki/Post/Redirect/Get) design pattern.

## Quick Example

```php
$form = new Form([
    'name' => [
        'rules'     => ['required'],
        'message'   => ['Name is required']
    ],
    'phone' => [],
]);

if ($form->validates()) {
    // Validation passed
    // Do something with the data
}
```

## Installation

Install with composer:

```
composer require mzur/kirby-form
```

## Basic Example

This example assumes you're using [page controllers in Kirby](http://getkirby.com/docs/templates/controllers) and that your page's URI is `/my-page`.

```php
// site/templates/my-page.php

<?php snippet('header') ?>

    <?php snippet('form-errors', ['form' => $form]) ?>

    <form method="POST">
        <input name="name" value="<?= $form->old('name') ?>">
        <input name="phone" value="<?= $form->old('phone') ?>">
        <?= csrf_field() ?>
        <input type="submit" value="Submit">
    </form>

<?php snippet('footer') ?>
```

```php
// site/snippets/form-errors.php

<?php if (count($form->errors()) > 0): ?>
    <div class="alert alert-error">
        <?php foreach ($form->errors() as $key => $errors): ?>
            <div><?= implode('<br>', $errors) ?></div>
        <?php endforeach ?>
    </div>
<?php endif ?>
```

```php
// site/controllers/my-page.php

use Jevets\Kirby\Form;

return function ($site, $pages, $page) {

    // Initialize the Form
    $form = new Form([
        'name' => [
            'rules'     => ['required'],
            'message'   => ['Name is required']
        ],
        'phone' => [],
    ]);

    // Process the form on POST requests
    if (r::is('POST')) {
        if ($form->validates()) {
            // Show a thanks page
        } else {
            // Redirect back to the GET form
            go('/my-page');
        }
    }

    return compact('form');
};
```

## Contributing

Feel free to send a pull request!

## Issues/Bugs

Please use the [GitHub issue tracker](https://github.com/mzur/kirby-form/issues).
