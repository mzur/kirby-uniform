<?php

use Uniform\Form;

// This file is called by Kirby if Uniform wasn't installed with Composer. In this case,
// use it's own vendor directory and autoload script.

if (!class_exists(Form::class)) {
    require __DIR__.DS.'vendor'.DS.'autoload.php';
}
