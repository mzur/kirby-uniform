# AJAX Example

This example shows how to submit a form and handle validation via AJAX. It uses a [route](https://getkirby.com/docs/guide/routing) as endpoint for the form submission. The action of the route is similar to the page controller code you have seen in the other examples. This form is equivalent to the [basic example](basic).

## Route

The route listens for `POST` requests on `/contact`. Note the use of [`withoutFlashing`](/methods/#withoutflashing) and [`withoutRedirect`](/methods/#withoutredirect) because we don't need the default behaviour here.

```php
[
    'pattern' => 'contact',
    'method' => 'POST',
    'action' => function () {
        $form = new \Uniform\Form([
            'email' => [
                'rules' => ['required', 'email'],
                'message' => 'Please enter a valid email address',
            ],
            'name' => [],
            'message' => [
                'rules' => ['required'],
                'message' => 'Please enter a message',
            ],
        ]);

        // Perform validation and execute guards.
        $form->withoutFlashing()
            ->withoutRedirect()
            ->guard();

        if (!$form->success()) {
            // Return validation errors.
            return Response::json($form->errors(), 400);
        }

        // If validation and guards passed, execute the action.
        $form->emailAction([
            'to' => 'me@example.com',
            'from' => 'info@example.com',
        ]);

        if (!$form->success()) {
            // This should not happen and is our fault.
            return Response::json($form->errors(), 500);
        }

        // Return code 200 on success.
        return Response::json([], 200);
    }
]
```

## Template

This is the `/contact` page. If your page has another URL, update the form action URL or the route pattern accordingly.

```html+php
<h1><?php echo $page->title()->html() ?></h1>

<style type="text/css">
    label, input, textarea {
        display: block;
    }
    .uniform__potty {
        position: absolute;
        left: -9999px;
    }
    .error {
        border: 1px solid red;
    }
</style>

<form action="<?php echo $page->url() ?>" method="POST">
    <label>Email</label>
    <input name="email" type="email">

    <label>Name</label>
    <input name="name" type="text">

    <label>Message</label>
    <textarea name="message"></textarea>

    <?php echo csrf_field() ?>
    <?php echo honeypot_field() ?>
    <input type="submit" value="Submit">
    <p id="message"></p>
</form>
```

## JavaScript

This simple example works fine with Vanilla JavaScript in modern browsers. But you probably want to use some JS framework for your production site.

```js
window.addEventListener('load', function () {
    var form = document.querySelector('form');
    var message = document.getElementById('message');
    var fields = {};
    form.querySelectorAll('[name]').forEach(function (field) {
        fields[field.name] = field;
    });

    // Displays all error messages and adds 'error' classes to the form fields with
    // failed validation.
    var handleError = function (response) {
        var errors = [];
        for (var key in response) {
            if (!response.hasOwnProperty(key)) continue;
            if (fields.hasOwnProperty(key)) fields[key].classList.add('error');
            Array.prototype.push.apply(errors, response[key]);
        }
        message.innerHTML = errors.join('<br>');
    }

    var onload = function (e) {
        if (e.target.status === 200) {
            message.innerHTML = 'Success!'
        } else {
            handleError(JSON.parse(e.target.response));
        }
    };

    var submit = function (e) {
        e.preventDefault();
        var request = new XMLHttpRequest();
        request.open('POST', e.target.action);
        request.onload = onload;
        request.send(new FormData(e.target));
        // Remove all 'error' classes of a possible previously failed validation.
        for (var key in fields) {
            if (!fields.hasOwnProperty(key)) continue;
            fields[key].classList.remove('error');
        }
    };
    form.addEventListener('submit', submit);
});
```
