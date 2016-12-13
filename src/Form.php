<?php

namespace Uniform;

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
     */
    public function withoutGuards()
    {
        $this->shouldCallGuard = false;
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

        if (csrf(get('_token')) !== true) {
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
     * @param  string $class   Guard class
     * @param  array  $options Guard options
     */
    public function guard($class = HoneypotGuard::class, $options = [])
    {
        if ($this->shouldValidate) $this->validate();
        $this->shouldCallGuard = false;

        if (!is_subclass_of($class, Guard::class)) {
            throw new Exception('Guards must extend '.Guard::class.'.');
        }

        $guard = new $class($this, $this->data, $options);
        $this->perform($guard);

        return $this;
    }

    /**
     * Execute an action
     *
     * @param  string  $class   Action class
     * @param  array   $options Action options
     * @return Form
     */
    public function action($class, $options = [])
    {
        if ($this->shouldValidate) $this->validate();
        if ($this->shouldCallGuard) $this->guard();

        if (!is_subclass_of($class, Action::class)) {
            throw new Exception('Actions must extend '.Action::class.'.');
        }

        $action = new $class($this->data, $options);
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
     * Redirect back to the page of the form
     */
    protected function redirect()
    {
        go(page()->url());
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
