<?php

/**
 * A simple Kirby 2 plugin to handle sending contact forms by e-mail.
 */
if(!class_exists('SendForm')) require_once('lib/SendForm.php');

function sendform($id, $recipient, $options = array()) {
	return new SendForm($id, $recipient, $options);
}