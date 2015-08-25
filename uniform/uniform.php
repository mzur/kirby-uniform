<?php

/**
 * A simple Kirby 2 plugin to handle form data.
 */

if(!class_exists('UniForm')) {
	require_once __DIR__ . DS . 'lib' . DS . 'UniForm.php';
}

function uniform($id, $options = array()) {
	// loads plugin language files dynamically
	// see https://github.com/getkirby/kirby/issues/168
	$lang = site()->multilang() ? site()->language()->code() : c::get('uniform.language', 'en');
	require_once __DIR__ . DS . 'languages' . DS . $lang . '.php';

	$form = new UniForm($id, $options);
	$form->execute();
	return $form;
}
