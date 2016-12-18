# Methods

These are only the most important methods of `Uniform\Form`. For all methods check the source. Be sure to check out `Jevets\Kirby\Form`, too, which is the base class of `Uniform\Form`.

## old($key)

Get the data that was flashed to the session

Return: `mixed`

```html+php
<input name="name" value="<?php echo $form->old('name') ?>">
```

## error($key = '')

Get a single error by key

If no key is provided, the first error will be returned.

Return: `array`

```html+php
<input name="name"<?php if ($form->error('name')): ?> class="error" <?php endif ?>>
<?php if ($form->error('name')): ?>
    <p class="error-text"><?php echo implode('<br>', $form->error('name')) ?></p>
<?php endif; ?>
```

## data($key = '', $value = '')

Get or set form data

If a second argument is provided, the data for the key will be returned. Otherwise, all data will be returned.

Return: `mixed|array`

## forget($name)

Forget a from field.

## validate()

Validate the form data.

Return: `Form`

## guard($guard = HoneypotGuard::class, $options = [])

Call a guard. Implicitly calls `validate()` if it wasn't called yet.

Return: `Form`

```php
use Uniform\Form;
use Uniform\Guards\CalcGuard;

$form = new Form;
if (r::is('POST')) {
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
if (r::is('POST')) {
    $form->calcGuard([/* guard options */]);
    // call actions
}
```

## action($action, $options = [])

Execute an action. Implicitly calls `guard()` if it wasn't called yet.

Return: `Form`

```php
use Uniform\Form;
use Uniform\Actions\EmailAction;

$form = new Form;
if (r::is('POST')) {
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
if (r::is('POST')) {
    $form->emailAction([/* action options */]);
}
```

## withoutGuards()

Don't run the default guard.

Return: `Form`

An action calls the default guard if no guard was called yet. This can be disabled to run without guards:

```php
use Uniform\Form;

$form = new Form;
if (r::is('POST')) {
    $form->withoutGuards()
        ->emailAction([/* action options */]);
}
```

## success()

Check if the form was executed successfully.

Return: `boolean`

```php
use Uniform\Form;

$form = new Form;
if (r::is('POST')) {
    $form->emailAction([/* action options */]);

    if ($form->success()) {
        // redirect to success page
    }
}
```
