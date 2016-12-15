<?php

if (defined('KIRBY')) {
    // Register all Uniform assets

    $kirby = kirby();
    $kirby->set('snippet', 'uniform/errors', __DIR__.DS.'snippets'.DS.'errors.php');
    $kirby->set('snippet', 'uniform/email-default', __DIR__.DS.'snippets'.DS.'email-default.php');
    $kirby->set('snippet', 'uniform/email-table', __DIR__.DS.'snippets'.DS.'email-table.php');

    $site = $kirby->site();
    // loads plugin language files dynamically
    // see https://github.com/getkirby/kirby/issues/168
    $lang = $site->multilang() ? $site->language()->code() : c::get('uniform.language', 'en');
    require __DIR__.DS.'languages'.DS.$lang.'.php';
}
