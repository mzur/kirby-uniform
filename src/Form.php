<?php

namespace Uniform;

use Uniform\Guards\HoneypotGuard;
use Uniform\Exceptions\Exception;
use Uniform\Guards\GuardInterface;
use Jevets\Kirby\Form as BaseForm;
use Uniform\Actions\ActionInterface;
use Uniform\Exceptions\ActionFailedException;
use Uniform\Exceptions\TokenMismatchException;
use Uniform\Exceptions\GuardRejectedException;

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
     * Validate the form data
     *
     * @return Form
     */
    public function validate()
    {
        $this->shouldValidate = false;

        if (csrf(get('_token')) !== true) {
            // TODO show a normal error message or simply ignore the request?
            throw new TokenMismatchException;
        }

        if (!parent::validates()) {
            $this->redirectBack();
        }

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

        $guard = new $class($this, $this->data, $options);

        if (!($guard instanceof GuardInterface)) {
            throw new Exception('Guards must implement the '.GuardInterface::class.'.');
        }

        try {
            $guard->check();
            $rejected = $guard->hasRejected();
            $message = $guard->getMessage();
        } catch (GuardRejectedException $e) {
            $rejected = true;
            $message = $e->getMessage();
        }

        if ($rejected) {
            $this->addError($guard->getKey(), $message);
            $this->saveData();
            $this->redirectBack();
        }

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

        $action = new $class($this->data, $options);

        if (!($action instanceof ActionInterface)) {
            throw new Exception('Actions must implement the '.ActionInterface::class.'.');
        }

        try {
            $action->execute();
            $failed = $action->hasFailed();
            $message = $action->getMessage();
        } catch (ActionFailedException $e) {
            $failed = true;
            $message = $e->getMessage();
        }

        if ($failed) {
            $this->addError($class, $message);
            $this->saveData();
            $this->redirectBack();
        }

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
    protected function redirectBack()
    {
        go(page()->url());
    }
}
