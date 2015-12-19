<?php

/*
 * Action to log the form data to a file
 */
uniform::$actions['log'] = function($form, $actionOptions)
{
	$file = a::get($actionOptions, 'file', false);
	if ($file === false)
	{
		throw new Exception('Uniform log action: No logfile specified!');
	}

	$data = '[' . date('c') . '] ' . visitor::ip() . ' ' . visitor::userAgent();

	foreach ($form as $key => $value) {
		$data .= "\n" . $key . ": " . $value;
	}
	$data .= "\n\n";

	$success = file_put_contents($file, $data, FILE_APPEND | LOCK_EX);

	if ($success === false)
	{
		return array(
			'success' => false,
			'message' => l::get('uniform-log-error')
		);
	}
	else
	{
		return array(
			'success' => true,
			'message' => l::get('uniform-log-success')
		);
	}
};
