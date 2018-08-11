# Email Select Action

This action can choose the `to` email address for the email action based on the form data. This can be used with forms where the user can choose to send the form to different departments for example. For this action the form needs a `recipient` field (e.g. select or radio) that has a key of the [allowed-recipients](#allowed-recipients-required) array as value.

## Example

### Controller

```php
<?php

use Uniform\Form;

return function ($kirby)
{
    $form = new Form([
        'email' => [
            'rules' => ['required', 'email'],
            'message' => 'Email is required',
        ],
        'recipient' => [
            'rules' => ['required'],
            'message' => 'Please choose a recipient',
        ],
        'message' => [
            'rules' => ['required'],
            'message' => 'Message is required',
        ],
    ]);

    if ($kirby->request()->is('POST')) {
        $form->emailSelectAction([
            'from' => 'info@example.com',
            'allowed-recipients' => [
                'sales'     => 'sales@example.com',
                'marketing' => 'marketing@example.com',
                'feedback'  => 'feedback@example.com'
            ],
        ]);
    }

    return compact('form');
};
```

### Template
```html+php
<form method="POST">
    <input name="email" type="email" value="<?php echo $form->old('email') ?>">
    <textarea name="message"><?php echo $form->old('message') ?></textarea>
    <select name="recipient">
        <?php $value = $form->old('recipient') ?>
        <option value="sales"<?php e($value=='sales', ' selected')?>>Contact sales</option>
        <option value="marketing"<?php e($value=='marketing', ' selected')?>>Contact marketing</option>
        <option value="feedback"<?php e($value=='feedback', ' selected')?>>Give feedback</option>
    </select>
    <?php echo csrf_field() ?>
    <?php echo honeypot_field() ?>
    <input type="submit" value="Submit">
</form>
```

## Options

This action supports [all options of the EmailAction](email#options) with the exception of `to` because the receiver of the email will be chosen from the allowed-recipients array.

### from (required)

This is the same option than [from of the EmailAction](email#from-required).

### allowed-recipients (required)

An associative array of possible recipients. The array maps the value of the `recipient` form field to an email address that should be the receiver of the email. These values are used so the actual email addresses are not leaked to the HTML document.
