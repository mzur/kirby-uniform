# Guards

Guards are the spam protection mechanism of Uniform. When no guard is explicitly specified, the [HoneypotGuard](honeypot) is used. Guards can be combined to provide even more reliable ways to filter out spam:

```php
use Uniform\Form;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $form->honeypotGuard([/* options */])
        ->calcGuard([/* options */])
        // more guards or call actions
}
```

In cases where you don't want to execute even the default guard, you can disable guards before any actions are called:

```php
use Uniform\Form;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $form->withoutGuards()
        // call actions
}
```

## Magic Methods

In the examples above guards are always called like this: `{name}Guard()`. These are magic methods which spare you to have to write this:

```php
use Uniform\Form;
use Uniform\Guards\CalcGuard;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $guard = new CalcGuard($form, [/* options */]);
    $form->guard($guard);
}
```

or the equivalent:

```php
use Uniform\Form;
use Uniform\Guards\CalcGuard;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $form->guard(CalcGuard::class, [/* options */]);
}
```
Although you probably want to use magic methods in most cases, it might be useful to know what actually happens under the hood.

## Custom Guards

As you have seen in the previous section, each guard is a class in the `Uniform\Guards` namespace. To write your own guards you can either provide them through a PHP package and Composer or implement them in the traditional way as a Kirby plugin.

Let's take a look at the implementation of a bare bones custom guard, defined in the `site/plugins/uniform-custom-guards/MyCustomGuard.php` file:

```php
<?php

namespace Uniform\Guards;

class MyCustomGuard extends Guard
{
    public function perform()
    {
        $accepted = /* do some check if the guard accepts the request */;
        if (!$accepted) {
            $this->reject();
        }
    }
}
```

And the complementary `site/plugins/uniform-custom-guards/index.php` to be loaded automatically by Kirby (not required if the guard is autoloaded by Composer):

```php
<?php

load([
    'Uniform\\Actions\\MyCustomGuard' => 'MyCustomGuard.php'
], __DIR__);
```

As you can see we also place the class in the `Uniform\Guards` namespace and give it a name with the suffix `Guard`. You don't have to do this but it is a requirement if you want to call the guard through a magic method (`$form->myCustomGuard()`). Also, it makes extending the `Uniform\Guards\Guard` base class easier, which you have to do for all guards.

Each guard must implement a `perform` method. In the method you can do all the checks you want to determine if the request is no spam. If the check succeeds, the function just returns. If it fails, however, you call the `reject` method. Rejecting a request will cause the form to immediately redirect the request and display the form with an error message. The `reject` method takes two optional arguments:

- `$message`: An error message to display to the user. Default is "{guard class} rejected the request".
- `$key`: A key to store the error message to. This can be a form field name if the error message should be displayed for a specific field only. Default is the class name of the guard.

A guard class has access to the following properties:

- `$form`: The Uniform instance.
- `$options`: The options array that may be passed to the guard.

There are two methods to conveniently retrieve options:

- `option($key, $default = null)`: Returns an option from the options array or an optional default value. Example: `$this->option('field', self::FIELD_NAME)`
- `requireOption($key)`: Returns an option from the options array or throws an exception of the option is not present. This can be used for mandatory options. Example: `$this->requireOption('file')`

Take a look at the [built-in guards](https://github.com/mzur/kirby-uniform/tree/master/src/Guards) for some examples.
