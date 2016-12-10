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
     * Should no more guards/validation/actions be executed because one failed?
     *
     * @var boolean
     */
    protected $shouldFallThrough;

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
     * @param  array  $data
     * @return void
     */
    function __construct($data = [])
    {
        parent::__construct($data);
        $this->shouldFallThrough = false;
        $this->shouldValidate = true;
        $this->shouldCallGuard = true;
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
            $this->shouldFallThrough = true;
            throw new TokenMismatchException;
        }

        if (!parent::validates()) {
            $this->shouldFallThrough = true;
            go(page()->url());
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
        if ($this->shouldFallThrough) return $this;

        if (!class_exists($class)) {
            throw new Exception("Guard {$class} does not exist.");
        }

        $guard = new $class($this, $this->data, $options);

        if (!($guard instanceof GuardInterface)) {
            throw new Exception('Guards must implement the '.GuardInterface::class.'.');
        }

        try {
            $guard->check();
            $rejected = $guard->hasRejected();
        } catch (GuardRejectedException $e) {
            $rejected = true;
        }

        if ($rejected) {
            $this->shouldFallThrough = true;
            // TODO store guard rejection message
        }

        return $this;
    }

    /**
     * Execute an action
     *
     * @param  string  $class   Action class
     * @param  array   $options Action options
     * @param  boolean $block   Don't execute subsequent actions if this one failed
     * @return Form
     */
    public function action($class, $options = [], $block = false)
    {
        if ($this->shouldValidate) $this->validate();
        if ($this->shouldCallGuard) $this->guard();
        if ($this->shouldFallThrough) return $this;

        if (!class_exists($class)) {
            throw new Exception("Action {$class} does not exist.");
        }

        $action = new $class($this->data, $options);

        if (!($action instanceof ActionInterface)) {
            throw new Exception('Actions must implement the '.ActionInterface::class.'.');
        }

        try {
            $action->execute();
            $failed = $action->hasFailed();
        } catch (ActionFailedException $e) {
            $failed = true;
        }

        if ($failed) {
            $this->shouldFallThrough = $block;
            // TODO store action error message
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
}
