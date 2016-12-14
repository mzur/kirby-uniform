# Basic Example

This form only asks for the name and email (both required) as well as a message. It restores values if the submission fails and displays the feedback message in a separate container. Note the `#form` anchor for jumping down to the feedback message when the form was submitted (especially important on mobile). This may be handled differently if the form is on your page root. If the form was submitted successfully, the submit button is disabled.

## Controller

```php
<?php

return function($site, $pages, $page) {
    $form = uniform('contact-form', [
        'required' => [
            'name'  => '',
            '_from' => 'email'
        ],
        'actions' => [
            [
                '_action' => 'email',
                'to'      => 'me@example.com',
                'sender'  => 'info@my-domain.tld',
                'subject' => 'New message from the contact form'
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

In case "Martin" with email "martin@example.com" submitted the message "hello", the email would look like this:

From: `info@my-domain.tld`

ReplyTo: `Martin<martin@example.com>`

Subject: `Message from the web form`

Body:

```txt
Name: Martin

Message: hello
```
