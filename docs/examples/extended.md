# Extended Example

This is an example of a more complex form with different input types, extensive validation and use of multiple actions.

If the form is successfully validated, the content is sent via email to the owner of the site `me@example.com`, stored to a log file and a success email is sent to the person who submitted the form. If there is an error for a form field, the respective field and label get an `error` class and the message is displayed directly below the field. If the submission was successful the user is redirected to the `registration/success` page.

## Controller

```php
<?php

use Uniform\Form;

return function ($kirby)
{
    $form = new Form([
        'name' => [
            'rules' => ['required'],
            'message' => 'Please enter your name',
        ],
        'email' => [
            'rules' => ['required', 'email'],
            'message' => 'Please enter a valid email address',
        ],
        'attendees' => [
            'rules' => ['required', 'num'],
            'message' => 'Please enter a number of attendees',
        ],
        'booth' => [
            'rules' => ['required', 'in' => [['6 sqm', '12 sqm', 'special']]],
            'message' => 'Please choose a booth size',
        ],
        'newsletter' => [
            'rules' => ['in' => [['yes', 'no']]],
            'message' => "Please choose 'yes' or 'no'",
        ],
        'message' => [],
    ]);

    if ($kirby->request()->is('POST')) {
        $form->emailAction([
                'to' => 'me@example.com',
                'from' => 'info@example.com',
                // Dynamically generate the subject with a template.
                'subject' => 'New registration for a {{booth}} booth',
            ])
            ->logAction([
                'file' => $kirby->roots()->site().'/registrations.log',
            ])
            ->emailAction([
                // Send the success email to the email address of the submitter.
                'to' => $form->data('email'),
                'from' => 'info@example.com',
                // Set replyTo manually, else it would be set to the value of 'email'.
                'replyTo' => 'me@example.com',
                'subject' => 'Thank you for your registration!',
                // Use a template for the email body (see below).
                'template' => 'success',
            ]); // No done() here because we do a custom redirect below.

        if ($form->success()) {
            go(page('registration/success')->url());
        }
    }

    return compact('form');
};
```

## Template

```html+php
<h1><?php echo $page->title()->html() ?></h1>

<style type="text/css">
    input:not([type="radio"]), textarea {
        display: block;
    }
    .uniform__potty {
        position: absolute;
        left: -9999px;
    }
    .error {
        border: 1px solid red;
    }
    .error-text {
        color: red;
    }
</style>

<form action="<?php echo $page->url() ?>" method="POST">
    <label>Name</label>
    <input<?php if ($form->error('name')): ?> class="error"<?php endif; ?> name="name" type="text" value="<?php echo $form->old('name') ?>">
    <!-- Use a snippet for the error so we don't repeat the same code for each field -->
    <?php snippet('form/error', ['field' => 'name']) ?>

    <label>Email</label>
    <input<?php if ($form->error('email')): ?> class="error"<?php endif; ?> name="email" type="email" value="<?php echo $form->old('email') ?>">
    <?php snippet('form/error', ['field' => 'email']) ?>

    <label>Attendees</label>
    <input<?php if ($form->error('attendees')): ?> class="error"<?php endif; ?> name="attendees" type="number" value="<?php echo $form->old('attendees') ?>">
    <?php snippet('form/error', ['field' => 'attendees']) ?>

    <label>Booth</label>
    <select name="booth">
    <?php $value = $form->old('booth') ?>
        <!-- Set the first option as default -->
        <option value="6 sqm"<?php e(!$value || $value=='6 sqm', ' selected')?>>6 m²</option>
        <option value="12 sqm"<?php e($value=='12 sqm', ' selected')?>>12 m²</option>
        <option value="special"<?php e($value=='special', ' selected')?>>special size >18 m²</option>
    </select>
    <?php snippet('form/error', ['field' => 'booth']) ?>

    <div>
        Do you want to receive the newsletter?
        <?php $value = $form->old('newsletter') ?>
        <label>
            <!-- Set this as default -->
           <input type="radio" name="newsletter" value="yes"<?php e(!$value || $value=='yes', ' checked')?>/> Yes
        </label>
        <label>
            <input type="radio" name="newsletter" value="no"<?php e($value=='no', ' checked')?>/> No
        </label>
        <?php snippet('form/error', ['field' => 'newsletter']) ?>
    </div>

    <label>Message</label>
    <textarea<?php if ($form->error('message')): ?> class="error"<?php endif; ?> name="message"><?php echo $form->old('message') ?></textarea>
    <?php snippet('form/error', ['field' => 'message']) ?>

    <?php echo csrf_field() ?>
    <?php echo honeypot_field() ?>
    <input type="submit" value="Submit">
    <!-- Show errors of the email actions if there are any -->
    <?php snippet('form/error', ['field' => \Uniform\Actions\EmailAction::class]) ?>
</form>
```

## Snippets

### snippets/form/error.php

```html+php
<?php if ($form->error($field)): ?>
    <p class="error-text"><?php echo implode('<br>', $form->error($field)) ?></p>
<?php endif; ?>
```

### templates/emails/success.php

```html+php
Dear <?php echo $name ?>,

thank you for the registration of a <?php echo $booth ?> booth with <?php echo $attendees ?> attendees<?php if ($newsletter === 'yes'): ?> and your subscription to our newsletter<?php endif; ?>!
```
