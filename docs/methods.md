# Methods

These are only the most important methods of `Uniform\Form`. For all methods check the [source](https://github.com/mzur/kirby-uniform/blob/master/src/Form.php). Be sure to [check out](https://github.com/mzur/kirby-form) `Jevets\Kirby\Form`, too, which is the base class of `Uniform\Form`.

## done()

Redirect back to the previous URL. This method should be called [after all form actions](usage).

## old($key, $default = '')

Get the data that was flashed to the session

This method does not work for some types of form fields (such as `type="file"`).

Return: `mixed`

```html+php
<input type="text" name="name" value="<?php echo $form->old('name', 'Joe User') ?>">
```

## error($key = '')

Get a single error by key

If no key is provided, the first error will be returned. This method returns an array because there may be multiple error messages for a single field.

Return: `array`

```html+php
<input name="name"<?php if ($form->error('name')): ?> class="error" <?php endif ?>>
<?php if ($form->error('name')): ?>
    <p class="error-text"><?php echo implode('<br>', $form->error('name')) ?></p>
<?php endif; ?>
```

## data($key = '', $value = '', $escape = true)

Get or set form data

If no argument is provided, all data will be returned. If one argument is provided, the data for the key will be returned. If two arguments are provided, the data for the key will be set.

Returned data is HTML-escaped by default. Set the third argument to `false` to get unescaped data.

Return: `mixed|array`

## forget($key)

Remove a form field from the form data.

## validate()

Validate the form data. This includes the CSRF token and form field validation.

Return: `Form`

## guard($guard = HoneypotGuard::class, $options = [])

Call a guard. Implicitly calls `validate()` if it wasn't called yet. The first argument can be a guard class name or a guard instance.

Return: `Form`

```php
use Uniform\Form;
use Uniform\Guards\CalcGuard;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $form->guard(CalcGuard::class, [/* guard options */]);
    // alternative:
    // $guard = new CalcGuard($form, [/* guard options */]);
    // $form->guard($guard);

    // call actions
}
```

Guards can be conveniently called through magic methods, too. This is the same than the example above:

```php
use Uniform\Form;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $form->calcGuard([/* guard options */]);
    // call actions
}
```

## action($action, $options = [])

Execute an action. Implicitly calls `validate()` and `guard()` if any of them weren't called yet. The first argument can be an action class name or an action instance.

Return: `Form`

```php
use Uniform\Form;
use Uniform\Actions\EmailAction;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $form->action(EmailAction::class, [/* action options */]);
    // alternative:
    // $action = new EmailAction($form, [/* action options */]);
    // $form->action($action);
}
```

Actions can be called through magic methods, too:

```php
use Uniform\Form;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $form->emailAction([/* action options */]);
}
```

## success()

Check if the form was executed successfully.

Return: `boolean`

```php
use Uniform\Form;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $form->emailAction([/* action options */]);

    if ($form->success()) {
        // redirect to success page
    }
}
```

## withoutGuards()

Don't run the default guard.

Return: `Form`

An action calls the default guard if no guard was called yet. This can be disabled to run without guards:

```php
use Uniform\Form;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $form->withoutGuards()
        ->emailAction([/* action options */]);
}
```

## withoutRedirect()

Don't redirect on error.

Return: `Form`

Typically the form immediately redirects back if an error occurred. In some cases (like an [AJAX form](examples/ajax)) this should not happen. If this method is called and an error occurs the form just skips all subsequent guards or actions and returns.

```php
use Uniform\Form;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $form->withoutRedirect()
        ->emailAction([/* action options */]);

    if (!$form->success()) {
        // prepare an error response
    }
}
```

## withoutFlashing()

Don't flash data or errors to the session.

Return: `Form`

Old form data and errors are usually flashed to the session so they can be displayed if a form wasn't submitted successfully. In some cases (like an [AJAX form](examples/ajax)) this should not happen.

```php
use Uniform\Form;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $form->withoutFlashing()
        ->emailAction([/* action options */]);

    if (!$form->success()) {
        // prepare an error response
    }
}
```
