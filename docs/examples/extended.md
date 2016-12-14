# Extended Example

This form extends the basic example by radio buttons and `select` fields as well as a custom subject. It validates a non-required field, too. For the email body the `uniform-email-table` snippet provided by this repo is used. For the HTML snippet to work, a `html-mail` email service is used that is *not* provided by this repo.

When the form is sent, a copy of the email will be sent to `me-too@example.com`, as well as to the sender of the form if they checked the `_receive_copy` checkbox (but only once since we set the `receive-copy` property to `false` for the second email action).

## Controller

```php
<?php

return function($site, $pages, $page) {
    $form = uniform('registration-form', [
        'required' => [
            'name'   => '',
            '_from'  => 'email'
        ],
        'validate' => [
            'attendees' => 'num'
        ],
        'actions' => [
            [
                '_action' => 'email',
                'to'      => 'me@example.com',
                'sender'  => 'info@my-domain.tld',
                'subject' => 'Exhibition - New registration',
                'snippet' => 'uniform-email-table'
            ],
            [
                '_action'      => 'email',
                'to'           => 'me-too@example.com',
                'sender'       => 'info@my-domain.tld',
                'subject'      => 'Exhibition - New registration',
                'snippet'      => 'uniform-email-table',
                'receive-copy' => false
            ]
        ]
    ]);

    return compact('form');
};
```

## Template

```php+html
<form action="<?php echo $page->url()?>#form" method="post">

    <label for="name" class="required">Name</label>
    <input<?php e($form->hasError('name'), ' class="erroneous"')?> type="text" name="name" id="name" value="<?php $form->echoValue('name') ?>" required/>

    <label for="email" class="required">E-Mail</label>
    <input<?php e($form->hasError('_from'), ' class="erroneous"')?> type="email" name="_from" id="email" value="<?php $form->echoValue('_from') ?>" required/>

    <label for="expertise">Area of expertise</label>
    <input type="text" name="expertise" id="expertise" value="<?php $form->echoValue('expertise') ?>"/>

    <label for="attendees">Number of attendees</label>
    <input<?php e($form->hasError('attendees'), ' class="erroneous"')?> type="number" name="attendees" id="attendees" value="<?php $form->echoValue('attendees') ?>"/>

    <label for="booth">Booth size</label>
    <select name="booth" id="booth">
        <?php $value = $form->value('booth') ?>
        <option value="6 sqm"<?php e($value=='6 sqm', ' selected')?>>6 m²</option>
        <option value="12 sqm"<?php e($value=='12 sqm', ' selected')?>>12 m²</option>
        <option value="18 sqm"<?php e($value=='18 sqm', ' selected')?>>18 m²</option>
        <option value="special"<?php e($value=='special', ' selected')?>>special size >18 m²</option>
    </select>

    <div class="radio-group">
        <div class="radio-group__label">Do you want to receive the newsletter?</div>
        <?php $value = $form->value('newsletter') ?>
        <label for="newsletter-yes">
            <input type="radio" name="newsletter" id="newsletter-yes" value="yes"<?php e($value=='yes'||$value=='', ' checked')?>/> Yes
        </label>
        <label for="newsletter-no">
            <input type="radio" name="newsletter" id="newsletter-no" value="no"<?php e($value=='no', ' checked')?>/> No
        </label>
    </div>

    <label for="receive-copy">
        <input type="checkbox" name="_receive_copy" id="receive-copy" <?php e($form->value('_receive_copy'), ' checked')?>/> Receive a copy of the sent data
    </label>

    <label for="message">Message</label>
    <textarea name="message" id="message"><?php $form->echoValue('message') ?></textarea>

    <label class="uniform__potty" for="website">Please leave this field blank</label>
    <input type="text" name="website" id="website" class="uniform__potty" />

    <a name="form"></a>
<?php if ($form->hasMessage()): ?>
    <div class="message <?php e($form->successful(), 'success' , 'error')?>">
        <?php $form->echoMessage() ?>
    </div>
<?php endif; ?>

    <button type="submit" name="_submit" value="<?php echo $form->token() ?>"<?php e($form->successful(), ' disabled')?>>Submit</button>

</form>
```

In case "Martin" with email "martin@example.com" has "JavaScript" as area of expertise, brings 3 attendees, wants a 12 m² booth, doesn't want to receive the newsletter and submitted the message "hello", the email would look like this:

From: `info@my-domain.tld`

ReplyTo: `Martin<martin@example.com>`

Subject: `Exhibition - New registration`

Body:
```html
<table>
    <thead>
        <tr>
            <th>Field</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Name</td>
            <td>Martin</td>
        </tr>
        <tr>
            <td>Expertise</td>
            <td>JavaScript</td>
        </tr>
        <tr>
            <td>Attendees</td>
            <td>3</td>
        </tr>
        <tr>
            <td>Booth</td>
            <td>12 sqm</td>
        </tr>
        <tr>
            <td>Newsletter</td>
            <td>no</td>
        </tr>
        <tr>
            <td>Message</td>
            <td>hello</td>
        </tr>
    </tbody>
</table>
```
