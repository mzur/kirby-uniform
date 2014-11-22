# kirby-contact-form

A simple, PHP-only [Kirby 2](http://getkirby.com) plugin to handle sending web forms by email.

It does **not** support extensive server-side validation or email templates.

## Installation

1. Put the `sendform` directory to `site/plugins/`.

2. Add the content of `sendform.css` to your CSS.

3. Put the language files of the `language` directory to `site/languages/`. If you already have existing language files, simply append the content to them. You only need to choose those languages that you actually want to support.

4. Make sure you have the language support of Kirby activated (even, if you only want to support one language). Here is an example for activating the support with a single language in `site/config/config.php`:

```php
c::set('languages', array(
	array(
		'code'    => 'en',
		'name'    => 'English',
		'locale'  => 'en_US',
		'default' => true,
		'url'     => '/'
	)
));
```

For more information on the multi language support of Kirby, see [the docs](http://getkirby.com/docs/languages/setup).

For a **quick-start** jump directly to the [basic example](#basic) and paste it into your template.

## Usage

You first have to initialize the form at the top of your contact form template like this:

```php
<?php
	$form = sendform(
		'contact-form-id',
		$page->email(),
		$site->title()->html() . ' - message from the contact form'
	);
?>

```

The **first** argument is a unique ID of the contact form on your site. The **second** one is the recipient's/your email address. In this case the `Email` field of the current page is used but of course you could hard code the address or get it elswhere. The **third** (optional) argument is the subject of the email to be sent. If none is specified, the default subject from the language definition is used.

You then create a form element with the own url of the page as `action` target like this:

```php
<form action="<?php echo $page->url()?>" method="post"></form>
```

The plugin then requires the presence of an `_email` field containing the sender's email address, a `_potty` field acting as a honey pot and a `_submit` button. Note the `_` at the beginning of the field `name` attributes, marking them as "private" fields that are not put into the email body. Here is an example:

```php
<label for="email" class="required">E-Mail</label>
<input type="email" name="_from" id="email" value="<?php $form->echoValue('_from') ?>" required/>

<label class="sendform__potty" for="potty">Please leave this field blank</label>
<input type="text" name="_potty" id="potty" class="sendform__potty" />

<button type="submit" name="_submit" value="<?php echo $form->token() ?>"<?php e($form->successful(), " disabled")?>>Submit</button>
```

There are a few important things happening here. First, the `echoValue()` function is used to set the `value` of the email field. If the submission of the form has failed, this restores already set fields. So in this case you don't have to enter the email address again when the page is reloaded. For more on the available functions, see [the functions section](#functions).

Secondly the honey pot field uses the `sendform__potty` class. If you check the `sendform.css` you'll see that it makes the field disappear visually but not in the souce code of the page, the spam-bots are accessing.

Lastly the submit button uses the `token()` function to set its value. The token is submitted along with all the other data of the form and ensures that the form can only be submitted directly from the website and not e.g. with an automated script.

The presence of these three elements with the exact `name` attributes and the token as a value of the submit button is critical for the plugin to work correctly!

Now you can add as many additional form fields as you like. Make sure not to use `_` as a prefix of the `name` attributes, else they won't appear in the email being sent. If you add a `name="name"` field, the content will be used for the name of the sender of the email in addition to the email address.

## Functions

### value($key)

Returns the value of a form field in case the submission of the form has failed. The value is empty if the form was sent successful. This will not work if the page was simply refreshed without submitting the form.

`$key`: The `name` attribute of the form field.

### echoValue($key)

Echos [`value()`](#valuekey) directly as a HTML-safe string.

`$key`: The `name` attribute of the form field.

### isValue($key, $value)

Checks if a form field has a certain value.

`$key`: The `name` attribute of the form field.

`$value`: The value tested against the actual content of the form field.

Returns `true` if the value equals the content of the form field, `false` otherwise

### successful()

Returns `true` if the form was sent successfully, `false` otherwise.

### message()

Returns the success/error feedback message.

### echoMessage()

Echos [`message()`](#message) directly as a HTML-safe string.

### hasMessage()

Returns `true` if there is a success/error feedback message, `false` otherwise.

### token()

Returns the current session token of this form.

## Examples

Here are a few full examples that you could directly put into your templates. They make use of the [`e()`](http://getkirby.com/docs/cheatsheet/helpers/e) helper function of Kirby which is not a part of this plugin.

### basic

This form only asks for the name and email as well as a message. It restores values if the submission fails and displays the feedback message in a separate container. Note the `#form` anchor for jumping down to the feedback message when the form was submitted (especially important on mobile). If the form was submitted successfully, the submit button is disabled.

```php
<?php
	$form = sendform(
		'contact-form',
		'me@example.com'
	);
?>

<form action="<?php echo $page->url()?>#form" method="post">

	<label for="name" class="required">Name</label>
	<input type="text" name="name" id="name" value="<?php $form->echoValue('name') ?>" required/>

	<label for="email" class="required">E-Mail</label>
	<input type="email" name="_from" id="email" value="<?php $form->echoValue('_from') ?>" required/>

	<label for="message" class="required">Message</label>
	<textarea name="message" id="message" required><?php $form->echoValue('message') ?></textarea>

	<label class="sendform__potty" for="potty">Please leave this field blank</label>
	<input type="text" name="_potty" id="potty" class="sendform__potty" />

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

From: `Martin<martin@example.com>`

Subject: `Message from the web form.`

```txt
Name: Martin

Message: hello
```

### extended

This form extends the basic example by radio buttons and `select` fields as well as a custom subject.

```php
<?php
	$form = sendform(
		'registration-form',
		'me@example.com',
		'Exhibition - New registration'
	);
?>

<form action="<?php echo $page->url()?>#form" method="post">

	<label for="name" class="required">Name</label>
	<input type="text" name="name" id="name" value="<?php $form->echoValue('name') ?>" required/>

	<label for="email" class="required">E-Mail</label>
	<input type="email" name="_from" id="email" value="<?php $form->echoValue('_from') ?>" required/>

	<label for="expertise">Area of expertise</label>
	<input type="text" name="expertise" id="expertise" value="<?php $form->echoValue('expertise') ?>"/>

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

	<label for="message" class="required">Message</label>
	<textarea name="message" id="message" required><?php $form->echoValue('message') ?></textarea>

	<label class="sendform__potty" for="potty">Please leave this field blank</label>
	<input type="text" name="_potty" id="potty" class="sendform__potty" />

	<a name="form"></a>
<?php if ($form->hasMessage()): ?>
	<div class="message <?php e($form->successful(), 'success' , 'error')?>">
		<?php $form->echoMessage() ?>
	</div>
<?php endif; ?>

	<button type="submit" name="_submit" value="<?php echo $form->token() ?>"<?php e($form->successful(), ' disabled')?>>Submit</button>

</form>
```

In case "Martin" with email "martin@example.com" has "JavaScript" as area of expertise, wants a 12 m² booth, doesn't want to receive the newsletter and submitted the message "hello", the email would look like this:

From: `Martin<martin@example.com>`

Subject: `Exhibition - New registration`

```txt
Name: Martin

Expertise: JavaScript

Booth: 12 sqm

Newsletter: no

Message: hello
```