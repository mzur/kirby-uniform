<?php

/**
 * A simple Kirby 2 plugin to handle form data.
 */
if (!class_exists('UniForm')) {
    require_once __DIR__.DS.'lib'.DS.'UniForm.php';
}

function uniform($id, $options = [])
{
    // loads plugin language files dynamically
    // see https://github.com/getkirby/kirby/issues/168
    $lang = site()->multilang() ? site()->language()->code() : c::get('uniform.language', 'en');
    require_once __DIR__.DS.'languages'.DS.$lang.'.php';

    // load actions
    require_once __DIR__.DS.'actions'.DS.'email.php';
    require_once __DIR__.DS.'actions'.DS.'email-select.php';
    require_once __DIR__.DS.'actions'.DS.'log.php';
    require_once __DIR__.DS.'actions'.DS.'login.php';
    require_once __DIR__.DS.'actions'.DS.'webhook.php';

    require_once __DIR__.DS.'guards'.DS.'honeypot.php';
    require_once __DIR__.DS.'guards'.DS.'calc.php';

    $form = new UniForm($id, $options);
    $form->execute();

    return $form;
}
