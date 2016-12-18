<?php

namespace Uniform;

use R;
use Str;
use Redirect;
use Uniform\Guards\Guard;
use Uniform\Actions\Action;
use Uniform\Guards\HoneypotGuard;
use Uniform\Exceptions\Exception;
use Jevets\Kirby\Form as BaseForm;
use Uniform\Exceptions\PerformerException;
use Uniform\Exceptions\TokenMismatchException;

class Form extends BaseForm
{
    /**
     * Name of the form field containing the CSRF token.
     *
     * @var string
     */
    const CSRF_FIELD = 'csrf_token';

    /**
     * Should the validation still be done?
     *
     * @var boolean
     */
    protected $shouldValidate;

    /**
     * Should any guards still be executed?
     *
     * @var boolean
     */
    protected $shouldCallGuard;

    /**
     * Was the form executed successfully?
     *
     * @var boolean
     */
    protected $success;

    /**
     * Create a new instance
     *
     * @param  array  $rules  Form fields and their validation rules
     * @return void
     */
    function __construct($rules = [])
    {
        parent::__construct($rules);
        $this->shouldValidate = true;
        $this->shouldCallGuard = true;
        $this->success = false;
    }

    /**
     * {@inheritDoc}
     *
     * Other than addErrors of Jevets\Kirby\Form this will add an array with error
     * messages for each field because guards or actions can produce multiple error
     * messages with the same key.
     *
     * @param  array  $data
     */
    public function addErrors($data)
    {
        $errors = $this->errors();

        foreach ($data as $key => $value) {
            $errors[$key][] = $value;
        }

        $this->flash->set(BaseForm::FLASH_KEY_ERRORS, $errors);
    }

    /**
     * Don't run the guards
     *
     * @return  Form
     */
    public function withoutGuards()
    {
        $this->shouldCallGuard = false;

        return $this;
    }

    /**
     * Check if the form was executed successfully.
     *
     * @return boolean
     */
    public function success()
    {
        return $this->success;
    }

    /**
     * Validate the form data
     *
     * @return Form
     */
    public function validate()
    {
        $this->shouldValidate = false;

        if (csrf(R::postData(self::CSRF_FIELD)) !== true) {
            throw new TokenMismatchException('The CSRF token was invalid.');
        }

        if (!parent::validates()) {
            $this->redirect();
        }

        $this->success = true;

        return $this;
    }

    /**
     * Call a guard
     *
     * @param  string|Guard $guard   Guard classname or object
     * @param  array  $options Guard options
     */
    public function guard($guard = HoneypotGuard::class, $options = [])
    {
        if ($this->shouldValidate) $this->validate();
        $this->shouldCallGuard = false;

        if (is_string($guard) && !class_exists($guard)) {
            throw new Exception("{$guard} does not exist.");
        }

        if (!is_subclass_of($guard, Guard::class)) {
            throw new Exception('Guards must extend '.Guard::class.'.');
        }

        if (is_string($guard)) {
            $guard = new $guard($this, $options);
        }

        $this->perform($guard);

        return $this;
    }

    /**
     * Execute an action
     *
     * @param  string|Action  $action   Action classname or object
     * @param  array   $options Action options
     * @return Form
     */
    public function action($action, $options = [])
    {
        if ($this->shouldValidate) $this->validate();
        if ($this->shouldCallGuard) $this->guard();

        if (is_string($action) && !class_exists($action)) {
            throw new Exception("{$action} does not exist.");
        }

        if (!is_subclass_of($action, Action::class)) {
            throw new Exception('Actions must extend '.Action::class.'.');
        }

        if (is_string($action)) {
            $action = new $action($this, $options);
        }

        $this->perform($action);

        return $this;
    }

    /**
     * Forget a form field
     *
     * @param  string $name Form field name
     */
    public function forget($name)
    {
        unset($this->data[$name]);
    }

    /**
     * Call actions and gards as magic method.
     *
     * Usage:
     * $form->calcGuard(...);
     * instead of
     * $form->guard(\Uniform\Guards\CalcGuard::class, ...);
     *
     * $form->emailAction(...);
     * instead of
     * $form->action(\Uniform\Actions\EmailAction::class, ...);
     *
     * @param  string $method
     * @param  array  $parameters
     * @return Form|null
     */
    public function __call($method, $parameters = [])
    {
        if (Str::endsWith($method, 'Guard')) {
            $class = '\Uniform\Guards\\'.ucfirst($method);
            $options = array_key_exists(0, $parameters) ? $parameters[0] : [];

            return $this->guard($class, $options);
        } else if (Str::endsWith($method, 'Action')) {
            $class = '\Uniform\Actions\\'.ucfirst($method);
            $options = array_key_exists(0, $parameters) ? $parameters[0] : [];

            return $this->action($class, $options);
        }
    }

    /**
     * Redirect back to the page of the form
     */
    protected function redirect()
    {
        Redirect::back();
    }

    /**
     * Perform a performer and handle a possible exception
     *
     * @param  Performer $performer
     */
    protected function perform(Performer $performer)
    {
        try {
            $performer->perform();
        } catch (PerformerException $e) {
            $this->addError($e->getKey(), $e->getMessage());
            $this->saveData();
            $this->redirect();
        }
    }
}
