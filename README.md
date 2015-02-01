# kirby-uniform

A versatile and powerful [Kirby 2](http://getkirby.com) plugin to handle web form actions.

Builtin actions:

- `email`: Send the form data by email.

## Installation

1. Copy or link the `uniform` directory to `site/plugins/`.

2. Add the content of `uniform.css` to your CSS.

3. Put the language files of the `languages` directory to `site/languages/`. If you already have existing language files, simply append the content to them (or use `include_once`). You only need to choose those languages that you actually want to support.

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

For a **quick-start** jump directly to the [basic example](#basic).

## Usage

You first have to initialize the form in your form page controller like this:

```php
$form = uniform(
	'contact-form',
	array(
		'required' => array(
			'_from' => 'email'
		),
		'actions' => array(
			array(
				'_action' => 'email',
				'to'      => (string) $page->email(),
				'sender'  => 'info@my-domain.tld',
				'subject' => $site->title()->html() . ' - message from the contact form'
			)
		)
	)
);
```

The **first** argument is a unique ID of the form on your entire website.

The **second** argument is the array of [options](#options). In this case the `_from` form field is required and validated as an email address. If the form data is correct, the `email` [action](#actions-1) is performed, sending the data to an email address specified in `$page->email()`.

You then create a form element with the own url of the page as `action` target like this:

```php
<form action="<?php echo $page->url()?>" method="post"></form>
```

The plugin then by default requires the presence of a `website` field acting as a honey pot and a `_submit` button. Note the `_` at the beginning of the field `name` attribute, marking it as special field that shouldn't be altered.

Here is an example with an additional `_from` field required by the `email` action:

```php
<label for="email" class="required">Email</label>
<input<?php e($form->hasError('_from'), ' class="erroneous"')?> type="email" name="_from" id="email" value="<?php $form->echoValue('_from') ?>" required/>

<label class="uniform__potty" for="website">Please leave this field blank</label>
<input type="text" name="website" id="website" class="uniform__potty" />

<button type="submit" name="_submit" value="<?php echo $form->token() ?>"<?php e($form->successful(), " disabled")?>>Submit</button>
```

There are some important things happening here.

First, the `echoValue()` function is used to set the `value` of the email field. If the submission of the form has failed, this restores already set fields. So in this case you don't have to enter the email address again when the page is reloaded after submitting the form. Also, the `hasError()` function is used to mark the email field with a special class if the server-side validation failed. For more on the available functions, see [the functions section](#functions).

Second, the honey pot field uses the `uniform__potty` class. If you check the `uniform.css` you'll see that it makes the field disappear visually but not in the souce code of the page, the spam-bots are accessing.

Last, the submit button uses the `token()` function to set its value. The token is submitted along with all the other data of the form and ensures that the form can only be submitted directly from the website and not e.g. with an automated script that doesn't know the token. If the honeypot check fails, a new token is generated to make it harder to guess.

The presence of the last two elements with the exact `name` attributes and the token as a value of the submit button is critical for the plugin to work correctly! Actions may require their own fields like `_from`, too.

Now you can add as many additional form fields as you like but you shouldn't use the `_` prefix for your own field names.

## Options

These are the options of the options array. You have to specify at least one action. Everything else is, well, optional.

### honeypot

The default name of the honeypot field is `website` to attract the attention of the bots. With the `honeypot` option you can change the default name to whatever you like but you should try to make it sound interesting for the bots. Example:

```php
'honeypot' => 'fill-me'
//...
<input type="text" name="fill-me" id="fill-me" class="uniform__potty" />
```

### required

Associative array of required form fields. The keys of the array are the `name` attributes of the required fields. The values of the entries are optional [validator function](http://getkirby.com/docs/cheatsheet#validators) names. Example:

```php
array('_from' => 'email')
```

So the `_from` field is required and validated by the [`v::email`](http://getkirby.com/docs/cheatsheet/validators/email) function. Note, that this works only with validator functions that validate single strings. If a field is required but should not be validated, leave the validator function name empty.

If a required field is missing, the form won't execute any actions.

### validate

Like [`required`](#required) but execution of the actions will *not* fail if one of these fields is missing. Only if one of these fields contains invalid data the actions will not be performed.

### actions

An array of [action](#actions-1) arrays. Each of these action arrays needs to contain at least an `_action` key with the name of the action that should be performed as value. It can contain arbitrary additional data for the action function. Example:

```php
array(
	'_action' => 'email',
	'to'      => (string) $page->email(),
	'subject' => $site->title()->html() . ' - message from the contact form'
)
```

This way even the same actions can be performed multiple times when a form is submitted (like sending the form data to multiple email addresses).

## Actions

Once all required fields are present and validated, the actions are performed. These can be completely arbitrary functions that receive the form data and action options as arguments. An example is the builtin `email` action. You can create your own action, too, of course!

To add custom actions, create a `site/plugins/uniform-actions/uniform-actions.php` file and implement all your custom actions there. Take a look at the `email` action in `UniForm.php` to see how to implement one.

[See the wiki](https://github.com/mzur/kirby-contact-form/wiki#actions) for all the available actions.

## Functions

These are the functions provided by the plugin to create a dynamic web form.

### value($key)

Returns the value of a form field in case the submission of the form has failed. The value is empty if the form was submitted successfully. This will not work if the page was simply refreshed without submitting the form!

`$key`: The `name` attribute of the form field.

### echoValue($key)

Echos [`value($key)`](#valuekey) directly as a HTML-safe string.

`$key`: The `name` attribute of the form field.

### isValue($key, $value)

Checks if a form field has a certain value.

`$key`: The `name` attribute of the form field.

`$value`: The value tested against the actual content of the form field.

Returns `true` if the value equals the content of the form field, `false` otherwise

### hasError($key)

`$key`: (optional) The `name` attribute of the form field.

Retruns `true` if there are erroneous fields. If a key is given, returns `true` if this field is erroneous. Returns `false` otherwise.

### token()

Returns the current session token of this form.

### successful($action = false)

`$action`: (optional) the index of the action in the actions array or `'_uniform'`.

Returns `true` if the action was performed successfully, `false` otherwise. If `'_uniform'` is used, `true` if the form data was successfully validated, `false` otherwise. If no action was specified, `true` if the form data was valid and all actions performed successfully, `false` otherwise.

### message($action = false)

`$action`: (optional) the index of the action in the actions array or `'_uniform'`.

Returns the success/error feedback message of a specific action or Uniform. If no action was specified, all messages will be returned.

### echoMessage($action = false)

Echos [`message($action)`](#messageaction-false) directly as a HTML-safe string.

### hasMessage($action = false)

`$action`: (optional) the index of the action in the actions array or `'_uniform'`.

Returns `true` if there is a success/error feedback message for the specified action or Uniform, `false` otherwise. If no action was specified, returns `true` if there is *any* message, `false` otherwise.

## Examples

Here are a few full examples that you could directly put into your controllers/templates. They make use of the [`e()`](http://getkirby.com/docs/cheatsheet/helpers/e) helper function of Kirby which is not a part of this plugin.

### basic

This form only asks for the name and email (both required) as well as a message. It restores values if the submission fails and displays the feedback message in a separate container. Note the `#form` anchor for jumping down to the feedback message when the form was submitted (especially important on mobile). This may be handled differently if the form is on your page root. If the form was submitted successfully, the submit button is disabled.

Controller:

```php
$form = uniform(
	'contact-form',
	array(
		'required' => array(
			'name'  => '',
			'_from' => 'email'
		),
		'actions' => array(
			array(
				'_action' => 'email',
				'to'      => 'me@example.com',
				'sender'  => 'info@my-domain.tld'
			)
		)
	)
);
```

Template:

```php
<form action="<?php echo $page->url()?>#form" method="post">

	<label for="name" class="required">Name</label>
	<input<?php e($form->hasError('name'), ' class="erroneous"')?> type="text" name="name" id="name" value="<?php $form->echoValue('name') ?>" required/>

	<label for="email" class="required">E-Mail</label>
	<input<?php e($form->hasError('_from'), ' class="erroneous"')?> type="email" name="_from" id="email" value="<?php $form->echoValue('_from') ?>" required/>

	<label for="message">Message</label>
	<textarea name="message" id="message"><?php $form->echoValue('message') ?></textarea>

	<label class="sendform__potty" for="website">Please leave this field blank</label>
	<input type="text" name="website" id="website" class="sendform__potty" />

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

### extended

This form extends the basic example by radio buttons and `select` fields as well as a custom subject. It validates a non-required field, too. For the email body the `sendform-table` snippet provided by this repo is used. For the HTML snippet to work, a `html-mail` email service is used that is *not* provided by this repo.

When the form is sent, a copy of the email will be sent to `me-too@example.com`, as well as to the sender of the form if they checked the `_receive_copy` checkbox.

Controller:

```php
$form = uniform(
	'registration-form',
	array(
		'required' => array(
			'name'   => '',
			'_from'  => 'email'
		),
		'validate' => array(
			'attendees'	=> 'num'
		),
		'actions' => array(
			array(
				'_action' => 'email',
				'to'      => 'me@example.com',
				'sender'  => 'info@my-domain.tld',
				'subject' => 'Exhibition - New registration'
			),
			array(
				'_action' => 'email',
				'to'      => 'me-too@example.com',
				'sender'  => 'info@my-domain.tld',
				'subject' => 'Exhibition - New registration'
			)
		)
	)
);
```

Template:

```php
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

	<label class="sendform__potty" for="website">Please leave this field blank</label>
	<input type="text" name="website" id="website" class="sendform__potty" />

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