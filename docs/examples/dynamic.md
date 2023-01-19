# Dynamic Form Fields

If you already worked through the [usage section](/usage), you'll know that all form fields need to be specified in the controller. Fields that are present in the form but not specified in the controller are ignored. But there may be cases where the form fields are not static. Sometimes you may want to handle different "sets" of form fields or you want to add form fields dynamically (e.g. via JavaScript). Here is an example on how these dynamic form fields can be handled with Uniform.

## Controller

The controller adds all form fields with the `shopitem-` prefix dynamically to the validation rules array.

```php
<?php

use Uniform\Form;

return function ($kirby)
{
    $rules = [
       'email' => [
            'rules' => ['required', 'email'],
            'message' => 'Please enter a valid email address',
        ],
        'name' => [],
    ];

    $shopItems = array_filter($kirby->request()->body()->toArray(), function ($key) {
       return strpos($key, 'shopitem-') === 0;
    }, ARRAY_FILTER_USE_KEY);

    foreach ($shopItems as $name => $value) {
       $rules[$name] = [
          'rules' => ['required'],
          'message' => 'The shop item must be present.',
       ];
    }

    $form = new Form($rules);

    if ($kirby->request()->is('POST')) {
        $form->emailAction([
            'to' => 'me@example.com',
            'from' => 'info@example.com',
        ])->done();
    }

    return compact('form', 'shopItems');
};
```

## Template

The template reconstructs any dynamic form fields so they are not lost if the form validation failed. Additional `shopitem-` fields may be added via JavaScript.

```html+php
<form action="<?php echo $page->url() ?>" method="POST">
    <label>Email</label>
    <input<?php if ($form->error('email')): ?> class="error"<?php endif; ?> name="email" type="email" value="<?php echo $form->old('email') ?>">

    <label>Name</label>
    <input<?php if ($form->error('name')): ?> class="error"<?php endif; ?> name="name" type="text" value="<?php echo $form->old('name') ?>">

    <label>Shop Items</label>
    <?php foreach ($shopItems as $name => $value): ?>
        <input<?php if ($form->error($name)): ?> class="error"<?php endif; ?> name="<?php echo $name ?>" type="text" value="<?php echo $form->old($name) ?>" readonly>
    <?php endforeach ?>

    <?php echo csrf_field() ?>
    <?php echo honeypot_field() ?>
    <input type="submit" value="Submit">
</form>
<?php if ($form->success()): ?>
    Thank you for your purchase. We will get back to you soon!
<?php else: ?>
    <?php snippet('uniform/errors', ['form' => $form]) ?>
<?php endif; ?>
```
