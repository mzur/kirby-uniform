<?php

namespace Uniform;

use Uniform\Exceptions\Exception;
use Jevets\Kirby\Form as BaseForm;
use Uniform\Actions\ActionInterface;
use Uniform\Exceptions\ActionFailedException;
use Uniform\Exceptions\TokenMismatchException;

class Form extends BaseForm
{
    /**
     * Did the form pass validation?
     *
     * @var boolean
     */
    protected $valid;

    /**
     * Should no more actions be executed because one failed?
     *
     * @var boolean
     */
    protected $blocked;

    /**
     * Create a new instance
     *
     * @param  array  $data
     * @return void
     */
    function __construct($data = [])
    {
        parent::__construct($data);
        $this->valid = false;
        $this->blocked = false;
    }

    /**
     * Validate the form
     *
     * @return Form
     */
    public function validate()
    {
        if (csrf(get('_token')) !== true) {
            throw new TokenMismatchException;
        }

        // TODO run guards

        if (parent::validates()) {
            $this->valid = true;
        } else {
            go(page()->url());
        }

        return $this;
    }

    /**
     * Execute an action
     *
     * @param string $class Action class
     * @param array $options Action options
     * @param boolean $block Don't execute subsequent actions if this one failed
     * @return Form
     */
    public function action($class, $options = [], $block = false)
    {
        if ($this->valid && !$this->blocked) {
            $action = new $class($this->data, $options);

            if (!($action instanceof ActionInterface)) {
                throw new Exception('Actions must implement '.ActionInterface::class);
            }

            try {
                $action->execute();
                $failed = $action->hasFailed();
            } catch (ActionFailedException $e) {
                $failed = true;
            }

            if ($failed) {
                $this->blocked = $block;
                // TODO store action error message
            }
        }

        return $this;
    }
}
