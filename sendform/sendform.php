<?php

/**
 * A simple Kirby 2 plugin to handle sending contact forms by e-mail.
 */
if(!class_exists('SendForm')) {
	require_once(__DIR__ . DS . 'lib' . DS . 'SendForm.php');
}

function sendform($id, $recipient, $options = array()) {
	return new SendForm($id, $recipient, $options);
}
