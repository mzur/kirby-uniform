# Actions

When you have a web form you most probably want to do something with the submitted data, too. With Uniform this is done with actions. There are several built-in actions you can choose from but you can [easily write your own](#custom-actions), too. Actions are only executed when the form data passed validation and the guards. Multiple actions can be chained like this:

```php
use Uniform\Form;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $form->emailAction([/* options */])
        ->logAction([/* options */])
        ->webhookAction([/* options */]);
}
```

If no guard is explicitly called, the first action will execute the default [HoneypotGuard](/guards/honeypot). If an action fails, the subsequent actions will not be executed.

## Magic Methods

Just like [guards](/guards), actions can be conveniently called through magic methods so you don't have to write this:

```php
use Uniform\Form;
use Uniform\Actions\EmailAction;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $action = new EmailAction($form, [/* options */]);
    $form->action($action);
}
```

or the equivalent:

```php
use Uniform\Form;
use Uniform\Actions\EmailAction;

$form = new Form;
if (kirby()->request()->is('POST')) {
    $form->action(EmailAction::class, [/* options */]);
}
```

## Custom Actions

Custom actions are very similar to [custom guards](/guards/guards#custom-guards), too. Each action is a class in the `Uniform\Actions` namespace. To write your own actions you can either provide them through a PHP package and Composer or implement them in the traditional way as a Kirby plugin.

Let's take a look at the implementation of an exemplary MyCustomAction, defined in the `site/plugins/uniform-custom-actions/MyCustomAction.php` file:

```php
<?php

namespace Uniform\Actions;

class MyCustomAction extends Action
{
    public function perform()
    {
        try {
            var_dump($this->form->data());
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
```

And the complementary `site/plugins/uniform-custom-actions/index.php` to be loaded automatically by Kirby (not required if the action is autoloaded by Composer):

```php
<?php

load([
    'Uniform\\Actions\\MyCustomAction' => 'MyCustomAction.php'
], __DIR__);
```

As you can see we also place the class in the `Uniform\Actions` namespace and give it a name with the suffix `Action`. You don't have to do this but it is a requirement if you want to call the action through a magic method (`$form->myCustomAction()`). Also, it makes extending the `Uniform\Actions\Action` base class easier, which you have to do for all actions.

Each action must implement a `perform` method. In the method you do something with the form data. In case anything fails you can call the `fail` method. If an action fails the form will immediately redirect the request and display the form with an error message. The `fail` method takes two optional arguments:

- `$message`: An error message to display to the user. Default is "{action class} failed.".
- `$key`: A key to store the error message to. This can be a form field name if the error message should be displayed for a specific field only. Default is the class name of the action.

An action class has access to the following properties:

- `$form`: The Uniform instance.
- `$options`: The options array that may be passed to the action.

There are two methods to conveniently retrieve options:

- `option($key, $default = null)`: Returns an option from the options array or an optional default value. Example: `$this->option('field', self::FIELD_NAME)`
- `requireOption($key)`: Returns an option from the options array or throws an exception of the option is not present. This can be used for mandatory options. Example: `$this->requireOption('file')`

Take a look at the [built-in actions](https://github.com/mzur/kirby-uniform/tree/master/src/Actions) for some examples.
