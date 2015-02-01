<?php

/**
 * A simple Kirby 2 plugin to handle form data.
 */
if(!class_exists('UniForm')) {
	require_once(__DIR__ . DS . 'lib' . DS . 'UniForm.php');
}

function uniform($id, $options = array()) {
	$form = new UniForm($id, $options);
	$form->execute();
	return $form;
}
