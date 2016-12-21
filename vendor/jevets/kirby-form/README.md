# Kirby Form

A helper library for working with Kirby forms, using the [Post/Redirect/Get](https://en.wikipedia.org/wiki/Post/Redirect/Get) design pattern.

**Requires:** [Jevets\Kirby\Flash](https://github.com/jevets/kirby-flash) for storing error messages and submitted form data.

## Quick Example

```php
$form = new Form([
    'name' => [
        'rules'     => ['required'],
        'message'   => ['Name is required']
    ],
    'phone' => [],
]);

if ($form->validates())
{
    // Validation passed
    // Do something with the data
}
```

## Installation

Require the package in your `composer.json` file:

```shell
{
    "require": {
        "jevets\kirby-form": "dev-master"
    },
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/jevets/kirby-form.git"
        }
    ]
}
```

Run `composer install` or `composer update`.

## Basic Example

This example assumes you're using [page controllers in Kirby](http://getkirby.com/docs/templates/controllers) and that your page's URI is `/my-page`.

```php
// site/templates/my-page.php

<?php snippet('header') ?>

    <?php snippet('form-errors', ['form' => $form]) ?>

    <form method="POST">
        <input name="name" value="<?= $form->old('name') ?>">
        <input name="phone" value="<?= $form->old('phone') ?>">
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
    if (r::is('post'))
    {
        if ($form->validates())
        {
            // Show a thanks page
        }
        else
        {
            // Redirect back to the GET form
            go('/my-page');
        }
    }

    return compact('form');

};
```
